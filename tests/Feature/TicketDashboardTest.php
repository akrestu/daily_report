<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketMetrics;
use App\Services\TicketWorkflow;
use App\Support\WorkingCalendar;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketDashboardTest extends TestCase
{
    use RefreshDatabase;

    private TicketWorkflow $workflow;

    private User $lead;

    private User $tech;

    private User $requester;

    protected function setUp(): void
    {
        parent::setUp();
        WorkingCalendar::flush();

        foreach (['level4' => 4, 'level2' => 2, 'level1' => 1] as $slug => $level) {
            Role::create(['name' => ucfirst($slug), 'slug' => $slug, 'level' => $level]);
        }
        $it = Department::create(['name' => 'IT Department', 'code' => 'IT']);
        $hr = Department::create(['name' => 'HR Department', 'code' => 'HR']);
        TicketCategory::create(['name' => 'IT Support', 'is_active' => true]);

        $this->workflow = app(TicketWorkflow::class);
        $this->lead = User::factory()->create(['role_id' => Role::where('slug', 'level4')->value('id'), 'department_id' => $it->id]);
        $this->tech = User::factory()->create(['role_id' => Role::where('slug', 'level2')->value('id'), 'department_id' => $it->id]);
        $this->requester = User::factory()->create(['role_id' => Role::where('slug', 'level1')->value('id'), 'department_id' => $hr->id]);
    }

    private function ticket(string $priority, array $overrides = []): Ticket
    {
        return Ticket::createWithNumber($overrides + [
            'requester_user_id' => $this->requester->id,
            'requester_name' => $this->requester->name,
            'requester_department' => 'HR Department',
            'category_id' => TicketCategory::where('name', 'IT Support')->value('id'),
            'title' => 'Masalah '.$priority,
            'description' => '-',
            'priority' => $priority,
        ])->refresh();
    }

    /**
     * Senin 2026-10-12: tiket A (Mendesak) dibuat 08:00, di-assign 08:20, selesai 10:00, dikonfirmasi.
     * Tiket B (Sedang) dibuat 09:00, belum direspons.
     */
    private function seedScenario(): Ticket
    {
        $this->travelTo(Carbon::parse('2026-10-12 08:00'));
        $a = $this->ticket('urgent');

        $this->travelTo(Carbon::parse('2026-10-12 08:20'));
        $this->workflow->assign($a, $this->tech, '2026-10-12', $this->lead);

        $this->travelTo(Carbon::parse('2026-10-12 09:00'));
        $this->ticket('medium');

        $this->travelTo(Carbon::parse('2026-10-12 10:00'));
        $this->workflow->changeStatus($a->refresh(), Ticket::STATUS_RESOLVED, $this->tech, 'Selesai');
        $this->workflow->confirmResolved($a->refresh(), $this->requester, $this->requester->name);

        return $a->refresh();
    }

    public function test_requester_can_rate_closed_ticket_once(): void
    {
        $ticket = $this->seedScenario();

        $this->actingAs($this->requester)->get(route('tickets.show', $ticket))->assertSee('Bagaimana layanan tim IT');
        $this->actingAs($this->requester)->post(route('tickets.csat', $ticket), ['rating' => 6])->assertSessionHasErrors('rating');
        $this->actingAs($this->requester)->post(route('tickets.csat', $ticket), ['rating' => 5, 'comment' => 'Cepat sekali'])
            ->assertSessionHas('success');
        $this->actingAs($this->requester)->post(route('tickets.csat', $ticket), ['rating' => 1])->assertSessionHas('error');

        $ticket->refresh();
        $this->assertSame(5, $ticket->csat_rating);
        $this->assertSame('Cepat sekali', $ticket->csat_comment);
        $this->actingAs($this->tech)->post(route('tickets.csat', $ticket), ['rating' => 1])->assertForbidden();
    }

    public function test_low_public_rating_notifies_it_team(): void
    {
        $this->travelTo(Carbon::parse('2026-10-12 08:00'));
        $ticket = $this->ticket('low', ['requester_user_id' => null, 'requester_name' => 'Budi']);
        $this->workflow->assign($ticket, $this->tech, '2026-10-13', $this->lead);
        $this->workflow->changeStatus($ticket->refresh(), Ticket::STATUS_RESOLVED, $this->tech, 'Selesai');
        $this->workflow->confirmResolved($ticket->refresh(), null, 'Budi');

        $this->post(route('tickets.public.track.lookup'), ['ticket_number' => $ticket->ticket_number, 'token' => $ticket->tracking_token]);
        $this->post(route('tickets.public.csat', $ticket), ['rating' => 2, 'comment' => 'Lama sekali'])->assertSessionHas('success');

        $this->assertSame(2, $ticket->fresh()->csat_rating);
        foreach ([$this->tech, $this->lead] as $user) {
            $this->assertTrue(Notification::where('user_id', $user->id)->where('message', 'like', '%Penilaian rendah (2/5)%')->exists());
        }
    }

    public function test_metrics_summary_is_computed_in_working_minutes(): void
    {
        $ticket = $this->seedScenario();
        $this->workflow->submitCsat($ticket, 4, null, $this->requester, $this->requester->name);

        $this->travelTo(Carbon::parse('2026-10-12 16:00'));
        $metrics = TicketMetrics::for(Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));
        $summary = $metrics->summary();

        $this->assertSame(2, $summary['created']);
        $this->assertSame(1, $summary['resolved']);
        $this->assertSame(1, $summary['backlog']);
        $this->assertSame(1, $summary['unassigned']);
        $this->assertSame(120, $summary['mttr_minutes'], '08:00 → 10:00');
        $this->assertSame(20, $summary['response_minutes'], '08:00 → 08:20');
        $this->assertEquals(100.0, $summary['response_sla_pct']);
        $this->assertEquals(100.0, $summary['resolution_sla_pct']);
        $this->assertEquals(4.0, $summary['csat_avg']);
        $this->assertEquals(0.0, $summary['reopen_pct']);

        $tech = $metrics->technicians()->firstWhere('name', $this->tech->name);
        $this->assertSame(1, $tech['resolved']);
        $this->assertSame(120, $tech['mttr_minutes']);

        $this->assertSame(['Mendesak' => 1, 'Tinggi' => 0, 'Sedang' => 1, 'Rendah' => 0], $metrics->breakdowns()['priority']);
        $trend = $metrics->dailyTrend();
        $this->assertSame(31, count($trend['labels']));
        $this->assertSame(2, $trend['created'][11]);
    }

    public function test_dashboard_is_for_it_team_only(): void
    {
        $this->seedScenario();

        $this->actingAs($this->lead)
            ->get(route('tickets.dashboard', ['period' => 'custom', 'from' => '2026-10-01', 'to' => '2026-10-31']))
            ->assertOk()->assertSee('Kinerja Teknisi')->assertSee($this->tech->name);

        $this->actingAs($this->lead)->get(route('tickets.dashboard'))->assertOk();
        $this->actingAs($this->lead)->get(route('tickets.dashboard', ['period' => 'custom']))->assertSessionHasErrors('from');
        $this->actingAs($this->requester)->get(route('tickets.dashboard'))->assertForbidden();
    }
}

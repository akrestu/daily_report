<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Notification;
use App\Models\Role;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketWorkflow;
use App\Support\WorkingCalendar;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kalender uji: Sabtu 2026-10-10, Minggu 2026-10-11, Senin 2026-10-12, Selasa 2026-10-13.
 * Jam kerja 06:00–17:00, Senin–Sabtu. Default SLA Mendesak: respons 30 menit, selesai 4 jam.
 */
class TicketSlaTest extends TestCase
{
    use RefreshDatabase;

    private TicketWorkflow $workflow;

    private User $lead;

    private User $tech;

    protected function setUp(): void
    {
        parent::setUp();
        WorkingCalendar::flush();

        foreach (['admin' => 0, 'level4' => 4, 'level2' => 2] as $slug => $level) {
            Role::create(['name' => ucfirst($slug), 'slug' => $slug, 'level' => $level]);
        }
        Department::create(['name' => 'IT Department', 'code' => 'IT']);
        TicketCategory::create(['name' => 'IT Support', 'is_active' => true]);

        $this->workflow = app(TicketWorkflow::class);
        $this->lead = $this->user('level4');
        $this->tech = $this->user('level2');
    }

    private function user(string $role): User
    {
        return User::factory()->create([
            'role_id' => Role::where('slug', $role)->value('id'),
            'department_id' => Department::value('id'),
        ]);
    }

    private function ticket(string $priority = 'urgent'): Ticket
    {
        return Ticket::createWithNumber([
            'requester_name' => 'Budi',
            'requester_department' => 'HR',
            'category_id' => TicketCategory::value('id'),
            'title' => 'Server mati',
            'description' => '-',
            'priority' => $priority,
        ])->refresh();
    }

    public function test_working_minutes_skip_off_hours_sunday_and_holidays(): void
    {
        $this->assertEquals('2026-10-13 06:30', WorkingCalendar::addWorkingMinutes(Carbon::parse('2026-10-12 16:30'), 60)->format('Y-m-d H:i'));
        $this->assertEquals('2026-10-12 06:30', WorkingCalendar::addWorkingMinutes(Carbon::parse('2026-10-12 03:00'), 30)->format('Y-m-d H:i'));
        // Sabtu 16:00 + 2 jam: 1 jam Sabtu, Minggu libur, 1 jam Senin
        $this->assertEquals('2026-10-12 07:00', WorkingCalendar::addWorkingMinutes(Carbon::parse('2026-10-10 16:00'), 120)->format('Y-m-d H:i'));
        $this->assertSame(120, WorkingCalendar::workingMinutesBetween(Carbon::parse('2026-10-10 16:00'), Carbon::parse('2026-10-12 07:00')));

        Holiday::create(['date' => '2026-10-12', 'name' => 'Libur uji']);
        $this->assertEquals('2026-10-13 07:00', WorkingCalendar::addWorkingMinutes(Carbon::parse('2026-10-10 16:00'), 120)->format('Y-m-d H:i'));
    }

    public function test_due_dates_are_set_on_creation_from_priority_policy(): void
    {
        $this->travelTo(Carbon::parse('2026-10-12 10:00'));
        $ticket = $this->ticket('urgent');

        $this->assertEquals('2026-10-12 10:30', $ticket->response_due_at->format('Y-m-d H:i'));
        $this->assertEquals('2026-10-12 14:00', $ticket->resolution_due_at->format('Y-m-d H:i'));
    }

    public function test_public_form_priority_comes_from_impact_urgency_matrix(): void
    {
        Employee::create(['nik' => '001', 'name' => 'Budi', 'department_id' => Department::value('id')]);

        $this->post('/request', [
            'nik' => '001', 'type' => 'incident', 'category_id' => TicketCategory::value('id'),
            'title' => 'Jaringan site down', 'description' => '-',
            'impact' => 'high', 'urgency' => 'high', 'priority' => 'low',
        ])->assertRedirect(route('tickets.public.success'));

        $ticket = Ticket::first();
        $this->assertSame('urgent', $ticket->priority, 'Prioritas dihitung server, input "priority" diabaikan');
        $this->assertSame('medium', Ticket::priorityFor('low', 'high'));
        $this->assertSame('low', Ticket::priorityFor('medium', 'low'));
    }

    public function test_pending_pauses_resolution_clock_and_resolving_late_marks_breach(): void
    {
        $this->travelTo(Carbon::parse('2026-10-12 10:00'));
        $ticket = $this->ticket('urgent');

        $this->travelTo(Carbon::parse('2026-10-12 10:10'));
        $this->workflow->assign($ticket, $this->tech, '2026-10-12', $this->lead);
        $ticket->refresh();
        $this->assertEquals('2026-10-12 10:10', $ticket->first_responded_at->format('Y-m-d H:i'));
        $this->assertFalse($ticket->response_breached);

        $this->travelTo(Carbon::parse('2026-10-12 11:00'));
        $this->workflow->changeStatus($ticket, Ticket::STATUS_PENDING, $this->tech, 'Mohon kirim foto error');

        $this->travelTo(Carbon::parse('2026-10-12 13:00'));
        $this->workflow->changeStatus($ticket, Ticket::STATUS_IN_PROGRESS, $this->tech);
        $ticket->refresh();
        $this->assertSame(120, $ticket->sla_paused_minutes);
        $this->assertEquals('2026-10-12 16:00', $ticket->resolution_due_at->format('Y-m-d H:i'), 'Tenggat bergeser 2 jam');

        $this->travelTo(Carbon::parse('2026-10-12 16:30'));
        $this->workflow->changeStatus($ticket, Ticket::STATUS_RESOLVED, $this->tech, 'Server dinyalakan ulang');
        $this->assertTrue($ticket->fresh()->resolution_breached);
    }

    public function test_sla_check_flags_breach_and_notifies_once(): void
    {
        $this->travelTo(Carbon::parse('2026-10-12 10:00'));
        $ticket = $this->ticket('urgent');

        $this->travelTo(Carbon::parse('2026-10-12 10:31'));
        $this->artisan('tickets:sla-check')->assertSuccessful();
        $this->artisan('tickets:sla-check')->assertSuccessful();

        $this->assertTrue($ticket->fresh()->response_breached);
        $this->assertSame(1, Notification::where('user_id', $this->lead->id)->where('message', 'like', '%SLA respons%')->count());
        $this->assertSame(0, Notification::where('user_id', $this->tech->id)->count(), 'Teknisi non-triager belum terkait tiket');

        $this->actingAs($this->lead)->get(route('tickets.index', ['sla' => 'breached']))->assertSee($ticket->ticket_number);
    }

    public function test_triager_can_correct_priority_and_due_dates_are_recalculated(): void
    {
        $this->travelTo(Carbon::parse('2026-10-12 10:00'));
        $ticket = $this->ticket('low');

        $this->actingAs($this->lead)->post(route('tickets.priority', $ticket), ['priority' => 'urgent'])
            ->assertSessionHas('error');

        $this->actingAs($this->lead)->post(route('tickets.priority', $ticket), [
            'priority' => 'urgent', 'reason' => 'Server produksi, semua user terdampak',
        ])->assertSessionHas('success');

        $ticket->refresh();
        $this->assertSame('urgent', $ticket->priority);
        $this->assertEquals('2026-10-12 14:00', $ticket->resolution_due_at->format('Y-m-d H:i'));
        $this->assertStringContainsString('Rendah menjadi Mendesak', $ticket->histories()->get()->last()->note);

        $this->actingAs($this->tech)->post(route('tickets.priority', $ticket), ['priority' => 'low', 'reason' => 'x'])
            ->assertForbidden();
    }

    public function test_admin_can_update_sla_targets_in_hours(): void
    {
        $admin = $this->user('admin');
        $policies = [];
        foreach (['urgent' => [0.5, 2], 'high' => [1, 6], 'medium' => [4, 22], 'low' => [8, 44]] as $priority => [$response, $resolution]) {
            $policies[$priority] = ['response_hours' => $response, 'resolution_hours' => $resolution];
        }

        $this->actingAs($admin)->get(route('admin.sla.index'))->assertOk()->assertSee('Matriks Prioritas');
        $this->actingAs($admin)->put(route('admin.sla.update'), ['policies' => $policies])->assertSessionHas('success');

        $this->assertSame(120, SlaPolicy::forPriority('urgent')->resolution_minutes);
        $this->assertSame(30, SlaPolicy::forPriority('urgent')->response_minutes);

        $policies['low']['resolution_hours'] = 1;
        $this->actingAs($admin)->put(route('admin.sla.update'), ['policies' => $policies])->assertSessionHasErrors();

        $this->actingAs($this->lead)->get(route('admin.sla.index'))->assertRedirect();
    }
}

<?php

namespace Tests\Feature;

use App\Exceptions\TicketWorkflowException;
use App\Models\Department;
use App\Models\JobPlan;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketWorkflow;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private TicketWorkflow $workflow;

    private User $lead;

    private User $tech;

    private User $requester;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin' => 0, 'level4' => 4, 'level2' => 2, 'level1' => 1] as $slug => $level) {
            Role::create(['name' => ucfirst($slug), 'slug' => $slug, 'level' => $level]);
        }
        Department::create(['name' => 'IT Department', 'code' => 'IT']);
        Department::create(['name' => 'HR Department', 'code' => 'HR']);
        TicketCategory::create(['name' => 'IT Support', 'is_active' => true]);

        $this->workflow = app(TicketWorkflow::class);
        $this->lead = $this->user('level4', 'IT');
        $this->tech = $this->user('level2', 'IT');
        $this->requester = $this->user('level1', 'HR');
    }

    private function user(string $role, string $dept): User
    {
        return User::factory()->create([
            'role_id' => Role::where('slug', $role)->value('id'),
            'department_id' => Department::where('code', $dept)->value('id'),
        ]);
    }

    private function ticket(array $overrides = []): Ticket
    {
        return Ticket::createWithNumber($overrides + [
            'requester_user_id' => $this->requester->id,
            'requester_name' => $this->requester->name,
            'requester_department' => 'HR Department',
            'category_id' => TicketCategory::where('name', 'IT Support')->value('id'),
            'title' => 'Printer rusak',
            'description' => 'Tidak bisa print',
            'priority' => 'medium',
        ])->refresh();
    }

    private function assignedTicket(): Ticket
    {
        $ticket = $this->ticket();
        $this->workflow->assign($ticket, $this->tech, today()->addDays(2)->toDateString(), $this->lead);

        return $ticket->refresh();
    }

    public function test_creation_is_recorded_in_history(): void
    {
        $ticket = $this->ticket();

        $this->assertSame(1, $ticket->histories()->count());
        $this->assertNull($ticket->histories->first()->from_status);
        $this->assertSame('open', $ticket->histories->first()->to_status);
    }

    public function test_assign_creates_job_plan_and_reassign_moves_it(): void
    {
        $ticket = $this->ticket();
        $this->actingAs($this->lead)->post(route('tickets.assign', $ticket), [
            'assigned_to' => $this->tech->id,
            'due_date' => today()->addDays(2)->toDateString(),
        ])->assertSessionHasNoErrors();

        $ticket->refresh();
        $this->assertSame(Ticket::STATUS_ASSIGNED, $ticket->status);
        $this->assertSame($this->tech->id, $ticket->jobPlan->assignee_id);

        $otherTech = $this->user('level2', 'IT');
        $this->workflow->changeStatus($ticket, Ticket::STATUS_IN_PROGRESS, $this->tech);
        $this->workflow->assign($ticket, $otherTech, today()->addDays(3)->toDateString(), $this->lead);

        $ticket->refresh();
        $this->assertSame(Ticket::STATUS_IN_PROGRESS, $ticket->status, 'Reassign tidak mengubah status');
        $this->assertSame($otherTech->id, $ticket->jobPlan->fresh()->assignee_id);
        $this->assertSame(1, JobPlan::count(), 'Job plan aktif dipindahkan, bukan dibuat ulang');
        $this->assertSame('Dialihkan ke '.$otherTech->name, $ticket->histories()->get()->last()->note);
    }

    public function test_assign_rejects_non_it_handler(): void
    {
        $this->actingAs($this->lead)->post(route('tickets.assign', $this->ticket()), [
            'assigned_to' => $this->requester->id,
            'due_date' => today()->toDateString(),
        ])->assertSessionHasErrors('assigned_to');
    }

    public function test_invalid_transitions_and_missing_notes_are_rejected(): void
    {
        $ticket = $this->assignedTicket();

        $this->actingAs($this->tech)->post(route('tickets.update-status', $ticket), ['status' => 'closed'])
            ->assertSessionHas('error');
        $this->actingAs($this->tech)->post(route('tickets.update-status', $ticket), ['status' => 'resolved'])
            ->assertSessionHas('error');
        $this->assertSame(Ticket::STATUS_ASSIGNED, $ticket->fresh()->status);

        $this->actingAs($this->tech)->post(route('tickets.update-status', $ticket), [
            'status' => 'resolved', 'note' => 'Driver printer diinstal ulang',
        ])->assertSessionHas('success');

        $ticket->refresh();
        $this->assertSame(Ticket::STATUS_RESOLVED, $ticket->status);
        $this->assertSame('Driver printer diinstal ulang', $ticket->resolution_note);
        $this->assertNotNull($ticket->resolved_at);
        $this->assertTrue(Notification::where('user_id', $this->requester->id)->where('ticket_id', $ticket->id)->exists());
    }

    public function test_requester_confirms_or_reopens_resolved_ticket(): void
    {
        $ticket = $this->assignedTicket();
        $this->workflow->changeStatus($ticket, Ticket::STATUS_RESOLVED, $this->tech, 'Selesai');

        $this->actingAs($this->requester)->post(route('tickets.respond', $ticket), ['action' => 'reopen'])
            ->assertSessionHas('error');

        $this->actingAs($this->requester)->post(route('tickets.respond', $ticket), [
            'action' => 'reopen', 'reason' => 'Masih error saat print dua sisi',
        ])->assertSessionHas('success');

        $ticket->refresh();
        $this->assertSame(Ticket::STATUS_IN_PROGRESS, $ticket->status);
        $this->assertSame(1, $ticket->reopen_count);
        $this->assertNull($ticket->resolved_at);

        $this->workflow->changeStatus($ticket, Ticket::STATUS_RESOLVED, $this->tech, 'Setting duplex diperbaiki');
        $this->actingAs($this->requester)->post(route('tickets.respond', $ticket), ['action' => 'confirm']);

        $ticket->refresh();
        $this->assertSame(Ticket::STATUS_CLOSED, $ticket->status);
        $this->assertSame('requester', $ticket->close_method);
    }

    public function test_other_users_cannot_respond_and_staff_cannot_close_directly(): void
    {
        $ticket = $this->assignedTicket();
        $this->workflow->changeStatus($ticket, Ticket::STATUS_RESOLVED, $this->tech, 'Selesai');

        $this->actingAs($this->tech)->post(route('tickets.respond', $ticket), ['action' => 'confirm'])->assertForbidden();
        $this->assertSame([], TicketWorkflow::staffTransitions($ticket->fresh()));
    }

    public function test_requester_can_cancel_only_before_work_starts(): void
    {
        $ticket = $this->assignedTicket();
        $this->workflow->changeStatus($ticket, Ticket::STATUS_IN_PROGRESS, $this->tech);

        $this->expectException(TicketWorkflowException::class);
        $this->workflow->cancelByRequester($ticket, $this->requester, $this->requester->name, 'Tidak jadi');
    }

    public function test_internal_comments_are_hidden_from_requester(): void
    {
        $ticket = $this->assignedTicket();
        $this->actingAs($this->tech)->post(route('tickets.comment', $ticket), ['comment' => 'Rahasia tim: cek vendor', 'is_internal' => 1]);
        $this->actingAs($this->tech)->post(route('tickets.comment', $ticket), ['comment' => 'Mohon kirim foto error']);

        $this->actingAs($this->requester)->get(route('tickets.show', $ticket))
            ->assertOk()->assertSee('Mohon kirim foto error')->assertDontSee('Rahasia tim');

        // Pemohon tidak bisa membuat catatan internal walau mengirim flag
        $this->actingAs($this->requester)->post(route('tickets.comment', $ticket), ['comment' => 'Ini fotonya', 'is_internal' => 1]);
        $this->assertFalse($ticket->comments()->where('comment', 'Ini fotonya')->first()->is_internal);
        $this->assertTrue(Notification::where('user_id', $this->tech->id)->where('message', 'like', '%Ini fotonya%')->exists());
    }

    public function test_public_requester_can_comment_and_confirm_via_tracking_session(): void
    {
        $ticket = $this->ticket(['requester_user_id' => null, 'requester_name' => 'Budi']);
        $this->workflow->assign($ticket, $this->tech, today()->toDateString(), $this->lead);
        $this->workflow->addComment($ticket, 'Catatan internal X', $this->tech, $this->tech->name, true);
        $this->workflow->changeStatus($ticket->refresh(), Ticket::STATUS_RESOLVED, $this->tech, 'Toner diganti');

        $this->post(route('tickets.public.respond', $ticket), ['action' => 'confirm'])
            ->assertRedirect(route('tickets.public.track'));

        $this->post(route('tickets.public.track.lookup'), ['ticket_number' => $ticket->ticket_number, 'token' => $ticket->tracking_token]);

        $this->get(route('tickets.public.track.show', $ticket))
            ->assertOk()->assertSee('Toner diganti')->assertDontSee('Catatan internal X');

        $this->post(route('tickets.public.comment', $ticket), ['comment' => 'Terima kasih'])->assertSessionHas('success');
        $this->assertSame('Budi', $ticket->comments()->get()->last()->author_display_name);

        $this->post(route('tickets.public.respond', $ticket), ['action' => 'confirm'])->assertSessionHas('success');
        $this->assertSame(Ticket::STATUS_CLOSED, $ticket->fresh()->status);
    }

    private function reportFromPlan(Ticket $ticket, array $overrides = []): \App\Models\DailyReport
    {
        $report = \App\Models\DailyReport::create($overrides + [
            'user_id' => $this->tech->id, 'department_id' => $this->tech->department_id, 'job_plan_id' => $ticket->job_plan_id,
            'job_name' => $ticket->jobPlan->job_name, 'report_date' => today(), 'due_date' => today(),
            'description' => 'Kabel LAN diganti, koneksi normal.', 'status' => 'completed',
            'approval_status' => 'pending', 'job_pic' => $this->lead->id,
        ]);
        $ticket->jobPlan->update(['status' => 'converted', 'converted_at' => now()]);

        return $report;
    }

    public function test_converting_job_plan_marks_ticket_in_progress_until_report_is_approved(): void
    {
        $ticket = $this->assignedTicket();
        $report = $this->reportFromPlan($ticket);

        $this->assertSame(Ticket::STATUS_IN_PROGRESS, $ticket->fresh()->status);

        $report->update(['approval_status' => 'approved', 'status' => 'completed', 'approved_by' => $this->lead->id]);

        $ticket->refresh();
        $this->assertSame(Ticket::STATUS_RESOLVED, $ticket->status);
        $this->assertStringContainsString('Kabel LAN diganti', $ticket->resolution_note);
    }

    public function test_rejected_report_keeps_ticket_open(): void
    {
        $ticket = $this->assignedTicket();
        $report = $this->reportFromPlan($ticket);

        $report->update(['approval_status' => 'rejected', 'approved_by' => $this->lead->id, 'rejection_reason' => 'Lengkapi foto']);

        $this->assertSame(Ticket::STATUS_IN_PROGRESS, $ticket->fresh()->status);
    }

    public function test_manual_resolve_or_cancel_closes_active_job_plan(): void
    {
        $ticket = $this->assignedTicket();
        $this->workflow->changeStatus($ticket, Ticket::STATUS_RESOLVED, $this->tech, 'Sudah diperbaiki di lokasi');

        $this->assertSame('closed', $ticket->jobPlan->fresh()->status);
        $this->assertSame('Ditutup', $ticket->jobPlan->fresh()->badge()['label']);
    }

    public function test_reopen_creates_follow_up_job_plan_for_handler(): void
    {
        $ticket = $this->assignedTicket();
        $oldPlanId = $ticket->job_plan_id;
        $this->workflow->changeStatus($ticket, Ticket::STATUS_RESOLVED, $this->tech, 'Selesai');

        $this->workflow->reopen($ticket->fresh(), $this->requester, $this->requester->name, 'Masih putus-putus');

        $ticket->refresh();
        $this->assertNotSame($oldPlanId, $ticket->job_plan_id);
        $this->assertSame('assigned', $ticket->jobPlan->status);
        $this->assertSame($this->tech->id, $ticket->jobPlan->assignee_id);
        $this->assertStringContainsString('Masih putus-putus', $ticket->jobPlan->remark);
    }

    public function test_auto_close_after_three_working_days_skipping_sunday(): void
    {
        // Jumat 10:00 → +3 hari kerja (Sabtu, Senin, Selasa) = Selasa 10:00
        $this->travelTo(Carbon::parse('2026-10-09 10:00'));
        $ticket = $this->assignedTicket();
        $this->workflow->changeStatus($ticket, Ticket::STATUS_RESOLVED, $this->tech, 'Selesai');

        $this->travelTo(Carbon::parse('2026-10-13 09:59'));
        $this->artisan('tickets:auto-close')->assertSuccessful();
        $this->assertSame(Ticket::STATUS_RESOLVED, $ticket->fresh()->status);

        $this->travelTo(Carbon::parse('2026-10-13 10:00'));
        $this->artisan('tickets:auto-close')->assertSuccessful();

        $ticket->refresh();
        $this->assertSame(Ticket::STATUS_CLOSED, $ticket->status);
        $this->assertSame('auto', $ticket->close_method);
        $this->assertSame('Sistem', $ticket->histories()->get()->last()->actor_name);
    }
    public function test_web_scheduler_auto_closes_without_cron_and_respects_interval(): void
    {
        $this->travelTo(Carbon::parse('2026-10-09 10:00'));
        $ticket = $this->assignedTicket();
        $this->workflow->changeStatus($ticket, Ticket::STATUS_RESOLVED, $this->tech, 'Selesai');
        $second = $this->assignedTicket();

        $this->travelTo(Carbon::parse('2026-10-13 10:00'));
        app(\App\Support\WebScheduler::class)->runDue();
        $this->assertSame(Ticket::STATUS_CLOSED, $ticket->fresh()->status);

        // Dalam interval yang sama tidak dijalankan ulang
        $this->workflow->changeStatus($second, Ticket::STATUS_RESOLVED, $this->tech, 'Selesai');
        $this->travelTo(Carbon::parse('2026-10-16 10:30'));
        \Illuminate\Support\Facades\Cache::put('web-scheduler:tickets:auto-close', 1, 3600);
        app(\App\Support\WebScheduler::class)->runDue();
        $this->assertSame(Ticket::STATUS_RESOLVED, $second->fresh()->status);
    }
}

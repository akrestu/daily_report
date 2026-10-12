<?php

namespace Tests\Feature;

use App\Exceptions\TicketWorkflowException;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketWorkflow;
use App\Support\WorkingCalendar;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketApprovalTest extends TestCase
{
    use RefreshDatabase;

    private TicketCategory $approvalCategory;

    private Department $hr;

    private Employee $requester;

    private Employee $boss;

    private User $lead;

    private User $tech;

    protected function setUp(): void
    {
        parent::setUp();
        WorkingCalendar::flush();
        Storage::fake('attachments');

        foreach (['admin' => 0, 'level4' => 4, 'level2' => 2, 'level1' => 1] as $slug => $level) {
            Role::create(['name' => ucfirst($slug), 'slug' => $slug, 'level' => $level]);
        }
        $it = Department::create(['name' => 'IT Department', 'code' => 'IT']);
        $this->hr = Department::create(['name' => 'HR Department', 'code' => 'HR']);

        // Dibuat oleh migration fase 5
        $this->approvalCategory = TicketCategory::where('name', 'Perangkat Baru')->firstOrFail();

        $this->requester = Employee::create(['nik' => '100', 'name' => 'Budi', 'department_id' => $this->hr->id]);
        $this->boss = Employee::create(['nik' => '200', 'name' => 'Ibu Manajer', 'position' => 'HR Manager', 'department_id' => $this->hr->id]);
        Employee::create(['nik' => '300', 'name' => 'Orang IT', 'department_id' => $it->id]);

        $this->lead = User::factory()->create(['role_id' => Role::where('slug', 'level4')->value('id'), 'department_id' => $it->id]);
        $this->tech = User::factory()->create(['role_id' => Role::where('slug', 'level2')->value('id'), 'department_id' => $it->id]);
    }

    private function submitPublic(array $overrides = [])
    {
        return $this->post('/request', $overrides + [
            'nik' => '100', 'type' => 'service_request', 'category_id' => $this->approvalCategory->id,
            'title' => 'Laptop baru untuk staf', 'description' => 'Laptop lama rusak total',
            'impact' => 'low', 'urgency' => 'medium',
        ]);
    }

    private function trackSession(Ticket $ticket): void
    {
        $this->post(route('tickets.public.track.lookup'), ['ticket_number' => $ticket->ticket_number, 'token' => $ticket->tracking_token]);
    }

    public function test_nik_lookup_offers_same_department_supervisors_except_self(): void
    {
        $this->getJson(route('tickets.public.employee', ['nik' => '100']))
            ->assertOk()
            ->assertJsonPath('supervisors', [['id' => $this->boss->id, 'name' => 'Ibu Manajer', 'position' => 'HR Manager']]);
    }

    public function test_approval_category_requires_supervisor_and_pauses_sla(): void
    {
        $this->submitPublic()->assertSessionHasErrors('supervisor_id');
        $this->submitPublic(['supervisor_id' => $this->requester->id])->assertSessionHasErrors('supervisor_id');

        $this->travelTo(Carbon::parse('2026-10-12 08:00'));
        $this->submitPublic(['supervisor_id' => $this->boss->id])->assertRedirect(route('tickets.public.success'));

        $ticket = Ticket::first();
        $this->assertSame(Ticket::APPROVAL_WAITING_UPLOAD, $ticket->approval_status);
        $this->assertSame($this->boss->id, $ticket->supervisor_employee_id);
        $this->assertNotNull($ticket->sla_paused_at);
        $this->assertTrue($ticket->awaitingApproval());

        $this->expectException(TicketWorkflowException::class);
        app(TicketWorkflow::class)->assign($ticket, $this->tech, '2026-10-13', $this->lead);
    }

    public function test_full_public_approval_flow_with_rejection_and_resubmission(): void
    {
        $this->travelTo(Carbon::parse('2026-10-12 08:00'));
        $this->submitPublic(['supervisor_id' => $this->boss->id]);
        $ticket = Ticket::first();
        $this->trackSession($ticket);

        $this->get(route('tickets.public.approval.form', $ticket))
            ->assertOk()->assertSee('Ibu Manajer')->assertSee('HR Manager')->assertSee($ticket->ticket_number);

        // Unggah bukti
        $this->post(route('tickets.public.approval.upload', $ticket), [
            'approval_proof' => UploadedFile::fake()->image('form.jpg'),
        ])->assertSessionHas('success');

        $ticket->refresh();
        $this->assertSame(Ticket::APPROVAL_WAITING_VERIFICATION, $ticket->approval_status);
        Storage::disk('attachments')->assertExists($ticket->approval_proof_path);
        $this->assertTrue(Notification::where('user_id', $this->lead->id)->where('message', 'like', '%perlu diverifikasi%')->exists());

        // Teknisi biasa tidak boleh memverifikasi; triager menolak tanpa alasan → gagal
        $this->actingAs($this->tech)->post(route('tickets.approval.verify', $ticket), ['decision' => 'approve'])->assertForbidden();
        $this->actingAs($this->lead)->post(route('tickets.approval.verify', $ticket), ['decision' => 'reject'])->assertSessionHas('error');
        $this->actingAs($this->lead)->post(route('tickets.approval.verify', $ticket), [
            'decision' => 'reject', 'note' => 'Tanda tangan atasan tidak ada',
        ])->assertSessionHas('success');
        $this->assertSame(Ticket::APPROVAL_REJECTED, $ticket->fresh()->approval_status);
        auth()->logout();

        // Pemohon unggah ulang, bukti lama dihapus
        $oldPath = $ticket->fresh()->approval_proof_path;
        $this->post(route('tickets.public.approval.upload', $ticket), [
            'approval_proof' => UploadedFile::fake()->create('form.pdf', 200, 'application/pdf'),
        ])->assertSessionHas('success');
        Storage::disk('attachments')->assertMissing($oldPath);

        // Disetujui 2 jam kerja kemudian → SLA mulai berjalan, jeda tercatat
        $this->travelTo(Carbon::parse('2026-10-12 10:00'));
        $this->actingAs($this->lead)->post(route('tickets.approval.verify', $ticket), ['decision' => 'approve'])->assertSessionHas('success');

        $ticket->refresh();
        $this->assertSame(Ticket::APPROVAL_APPROVED, $ticket->approval_status);
        $this->assertNull($ticket->sla_paused_at);
        $this->assertSame(120, $ticket->sla_paused_minutes);
        $this->assertSame($this->lead->id, $ticket->approval_verified_by);
        // Dampak rendah × urgensi sedang = Rendah (respons 11 jam kerja):
        // Senin 08:00 + 2 jam jeda + 11 jam = 13 jam kerja → Selasa 10:00
        $this->assertSame('low', $ticket->priority);
        $this->assertEquals('2026-10-13 10:00', $ticket->response_due_at->format('Y-m-d H:i'));

        app(TicketWorkflow::class)->assign($ticket, $this->tech, '2026-10-13', $this->lead);
        $this->assertSame(Ticket::STATUS_ASSIGNED, $ticket->fresh()->status);
    }

    public function test_approval_pages_require_tracking_session_or_ticket_access(): void
    {
        $this->submitPublic(['supervisor_id' => $this->boss->id]);
        $ticket = Ticket::first();

        $this->get(route('tickets.public.approval.form', $ticket))->assertRedirect(route('tickets.public.track'));
        $this->post(route('tickets.public.approval.upload', $ticket), ['approval_proof' => UploadedFile::fake()->image('x.jpg')])
            ->assertRedirect(route('tickets.public.track'));

        $outsider = User::factory()->create(['role_id' => Role::where('slug', 'level1')->value('id'), 'department_id' => $this->hr->id]);
        $this->actingAs($outsider)->get(route('tickets.approval.form', $ticket))->assertForbidden();
        $this->actingAs($this->lead)->get(route('tickets.approval.form', $ticket))->assertOk();
    }

    public function test_logged_in_requester_uploads_from_ticket_page(): void
    {
        $user = User::factory()->create(['role_id' => Role::where('slug', 'level1')->value('id'), 'department_id' => $this->hr->id]);

        $this->actingAs($user)->get(route('tickets.create'))->assertOk()->assertSee('Ibu Manajer');
        $this->actingAs($user)->post(route('tickets.store'), [
            'type' => 'service_request', 'category_id' => $this->approvalCategory->id,
            'title' => 'Akses printer', 'description' => '-', 'impact' => 'low', 'urgency' => 'low',
            'supervisor_id' => $this->boss->id,
        ]);
        $ticket = Ticket::first();

        $this->actingAs($user)->get(route('tickets.show', $ticket))->assertOk()->assertSee('Cetak Formulir Persetujuan');
        $this->actingAs($user)->post(route('tickets.approval.upload', $ticket), [
            'approval_proof' => UploadedFile::fake()->create('bad.exe', 10),
        ])->assertSessionHasErrors('approval_proof');
        $this->actingAs($user)->post(route('tickets.approval.upload', $ticket), [
            'approval_proof' => UploadedFile::fake()->image('ok.png'),
        ])->assertSessionHas('success');

        $this->actingAs($user)->get(route('tickets.approval.proof', $ticket))->assertOk();
    }

    public function test_admin_can_toggle_category_approval_requirement(): void
    {
        $admin = User::factory()->create(['role_id' => Role::where('slug', 'admin')->value('id'), 'department_id' => $this->hr->id]);
        $category = TicketCategory::create(['name' => 'Software', 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.ticket-categories.index'))->assertOk()->assertSee('Perangkat Baru');
        $this->actingAs($admin)->put(route('admin.ticket-categories.update', $category), [
            'name' => 'Software', 'requires_approval' => 1, 'is_active' => 1,
        ])->assertSessionHas('success');

        $this->assertTrue($category->fresh()->requires_approval);
        $this->actingAs($this->lead)->get(route('admin.ticket-categories.index'))->assertRedirect();
    }
}

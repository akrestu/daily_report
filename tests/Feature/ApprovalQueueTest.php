<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalQueueTest extends TestCase
{
    use RefreshDatabase;

    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->department = Department::create(['name' => 'Maintenance', 'code' => 'MTC']);
    }

    private function user(string $slug): User
    {
        $role = Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);

        return User::factory()->create(['role_id' => $role->id, 'department_id' => $this->department->id]);
    }

    private function pending(User $owner, User $pic, string $name): DailyReport
    {
        return DailyReport::create([
            'user_id' => $owner->id,
            'department_id' => $this->department->id,
            'job_name' => $name,
            'report_date' => today(),
            'due_date' => today()->addDay(),
            'description' => 'Deskripsi',
            'status' => 'completed',
            'approval_status' => 'pending',
            'job_pic' => $pic->id,
        ]);
    }

    public function test_queue_lists_reports_assigned_to_pic(): void
    {
        $pic = $this->user('level2');
        $this->pending($this->user('level1'), $pic, 'Inspeksi conveyor CV-03');

        $this->actingAs($pic)->get(route('daily-reports.pending'))
            ->assertOk()
            ->assertSee('sgQueue', false)
            ->assertSee('Inspeksi conveyor CV-03');
    }

    public function test_swipe_approval_returns_json(): void
    {
        $pic = $this->user('level2');
        $report = $this->pending($this->user('level1'), $pic, 'Ganti filter oli');

        $this->actingAs($pic)
            ->postJson(route('daily-reports.approval', $report), ['status' => 'approved'])
            ->assertOk()
            ->assertJson(['ok' => true, 'status' => 'approved']);

        $this->assertSame('approved', $report->fresh()->approval_status);
    }

    public function test_empty_queue_shows_empty_state(): void
    {
        $this->actingAs($this->user('level2'))->get(route('daily-reports.pending'))
            ->assertOk()
            ->assertSee('Antrian kosong');
    }

    public function test_batch_approve_and_batch_reject_requires_reason(): void
    {
        $pic = $this->user('level2');
        $owner = $this->user('level1');
        $a = $this->pending($owner, $pic, 'A');
        $b = $this->pending($owner, $pic, 'B');

        // batchReject menangkap ValidationException di try/catch → flash 'error' (tampil sebagai toast), laporan tidak berubah
        $this->actingAs($pic)
            ->post(route('daily-reports.batch-reject'), ['selected_reports' => [$b->id]])
            ->assertSessionHas('error');
        $this->assertSame('pending', $b->fresh()->approval_status);

        $this->actingAs($pic)
            ->post(route('daily-reports.batch-approve'), ['selected_reports' => [$a->id, $b->id]]);

        $this->assertSame(['approved', 'approved'], [$a->fresh()->approval_status, $b->fresh()->approval_status]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportDetailTest extends TestCase
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

    private function report(User $owner, User $pic): DailyReport
    {
        return DailyReport::create([
            'user_id' => $owner->id,
            'department_id' => $this->department->id,
            'job_name' => 'Inspeksi conveyor CV-03',
            'report_date' => today(),
            'due_date' => today()->addDay(),
            'description' => 'Deskripsi <script>alert(1)</script>',
            'status' => 'pending',
            'approval_status' => 'pending',
            'job_pic' => $pic->id,
        ]);
    }

    public function test_pic_sees_approval_actions_and_owner_sees_edit(): void
    {
        $owner = $this->user('level1');
        $pic = $this->user('level2');
        $report = $this->report($owner, $pic);

        $this->actingAs($pic)->get(route('daily-reports.show', $report))
            ->assertOk()
            ->assertSee('Tolak Laporan')
            ->assertSee('approve-report', false)
            ->assertDontSee('<script>alert(1)</script>', false);

        $this->actingAs($owner)->get(route('daily-reports.show', $report))
            ->assertOk()
            ->assertSee(route('daily-reports.edit', $report), false)
            ->assertDontSee('Tolak Laporan');
    }

    public function test_reject_requires_reason_and_approve_updates_status(): void
    {
        $owner = $this->user('level1');
        $pic = $this->user('level2');
        $report = $this->report($owner, $pic);

        $this->actingAs($pic)
            ->post(route('daily-reports.approval', $report), ['status' => 'rejected', 'redirect_back' => 1])
            ->assertSessionHasErrors('rejection_reason');

        $this->actingAs($pic)
            ->post(route('daily-reports.approval', $report), ['status' => 'approved', 'redirect_back' => 1])
            ->assertSessionHasNoErrors();

        $this->assertSame('approved', $report->fresh()->approval_status);
    }
}

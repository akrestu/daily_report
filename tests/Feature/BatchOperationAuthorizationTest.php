<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\DailyReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchOperationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $level2User;
    protected $level3User;
    protected $level4User;
    protected $level5User;
    protected $level1User;
    protected $department1;
    protected $department2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create departments
        $this->department1 = Department::factory()->create(['name' => 'Department 1']);
        $this->department2 = Department::factory()->create(['name' => 'Department 2']);

        // Create roles
        $adminRole = Role::factory()->create(['name' => 'Administrator', 'slug' => 'admin']);
        $level5Role = Role::factory()->create(['name' => 'Department Head', 'slug' => 'level5']);
        $level4Role = Role::factory()->create(['name' => 'Manager', 'slug' => 'level4']);
        $level3Role = Role::factory()->create(['name' => 'Supervisor', 'slug' => 'level3']);
        $level2Role = Role::factory()->create(['name' => 'Leader', 'slug' => 'level2']);
        $level1Role = Role::factory()->create(['name' => 'Staff', 'slug' => 'level1']);

        // Create users
        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'department_id' => $this->department1->id,
        ]);

        $this->level5User = User::factory()->create([
            'role_id' => $level5Role->id,
            'department_id' => $this->department1->id,
        ]);

        $this->level4User = User::factory()->create([
            'role_id' => $level4Role->id,
            'department_id' => $this->department1->id,
        ]);

        $this->level3User = User::factory()->create([
            'role_id' => $level3Role->id,
            'department_id' => $this->department1->id,
        ]);

        $this->level2User = User::factory()->create([
            'role_id' => $level2Role->id,
            'department_id' => $this->department1->id,
        ]);

        $this->level1User = User::factory()->create([
            'role_id' => $level1Role->id,
            'department_id' => $this->department1->id,
        ]);
    }

    /** @test */
    public function level2_can_batch_approve_only_pending_reports_from_their_department()
    {
        // Create pending report in same department
        $report1 = DailyReport::factory()->create([
            'user_id' => $this->level1User->id,
            'department_id' => $this->department1->id,
            'approval_status' => 'pending',
        ]);

        // Create pending report in different department
        $report2 = DailyReport::factory()->create([
            'user_id' => $this->level1User->id,
            'department_id' => $this->department2->id,
            'approval_status' => 'pending',
        ]);

        // Create already approved report
        $report3 = DailyReport::factory()->create([
            'user_id' => $this->level1User->id,
            'department_id' => $this->department1->id,
            'approval_status' => 'approved_by_leader',
        ]);

        $response = $this->actingAs($this->level2User)->post(route('daily-reports.batch-approve'), [
            'selected_reports' => [$report1->id, $report2->id, $report3->id],
        ]);

        $response->assertSessionHas('success');

        // Only report1 should be approved (same dept, correct status)
        $report1->refresh();
        $this->assertEquals('approved_by_leader', $report1->approval_status);
        $this->assertEquals($this->level2User->id, $report1->approved_by);

        // Report2 should not be approved (different department)
        $report2->refresh();
        $this->assertEquals('pending', $report2->approval_status);

        // Report3 should not be approved (already approved)
        $report3->refresh();
        $this->assertEquals('approved_by_leader', $report3->approval_status);
    }

    /** @test */
    public function level3_can_only_approve_reports_at_approved_by_leader_status()
    {
        // Create report with correct status
        $report1 = DailyReport::factory()->create([
            'user_id' => $this->level1User->id,
            'department_id' => $this->department1->id,
            'approval_status' => 'approved_by_leader',
        ]);

        // Create report with wrong status
        $report2 = DailyReport::factory()->create([
            'user_id' => $this->level1User->id,
            'department_id' => $this->department1->id,
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($this->level3User)->post(route('daily-reports.batch-approve'), [
            'selected_reports' => [$report1->id, $report2->id],
        ]);

        $response->assertSessionHas('success');

        // Report1 should be approved
        $report1->refresh();
        $this->assertEquals('approved_by_supervisor', $report1->approval_status);

        // Report2 should not be approved (wrong status)
        $report2->refresh();
        $this->assertEquals('pending', $report2->approval_status);
    }

    /** @test */
    public function batch_size_limit_prevents_dos_attack()
    {
        // Create 101 reports
        $reportIds = [];
        for ($i = 0; $i < 101; $i++) {
            $report = DailyReport::factory()->create([
                'user_id' => $this->level1User->id,
                'department_id' => $this->department1->id,
                'approval_status' => 'pending',
            ]);
            $reportIds[] = $report->id;
        }

        $response = $this->actingAs($this->level2User)->post(route('daily-reports.batch-approve'), [
            'selected_reports' => $reportIds,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Cannot approve more than 100', session('error'));
    }

    /** @test */
    public function admin_can_approve_reports_from_any_department()
    {
        // Create reports in different departments
        $report1 = DailyReport::factory()->create([
            'user_id' => $this->level1User->id,
            'department_id' => $this->department1->id,
            'approval_status' => 'pending',
        ]);

        $report2 = DailyReport::factory()->create([
            'user_id' => $this->level1User->id,
            'department_id' => $this->department2->id,
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('daily-reports.batch-approve'), [
            'selected_reports' => [$report1->id, $report2->id],
        ]);

        $response->assertSessionHas('success');

        // Both reports should be approved
        $report1->refresh();
        $report2->refresh();

        $this->assertNotEquals('pending', $report1->approval_status);
        $this->assertNotEquals('pending', $report2->approval_status);
    }

    /** @test */
    public function batch_reject_validates_department_and_status()
    {
        // Create report in same department with correct status
        $report1 = DailyReport::factory()->create([
            'user_id' => $this->level1User->id,
            'department_id' => $this->department1->id,
            'approval_status' => 'pending',
        ]);

        // Create report in different department
        $report2 = DailyReport::factory()->create([
            'user_id' => $this->level1User->id,
            'department_id' => $this->department2->id,
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($this->level2User)->post(route('daily-reports.batch-reject'), [
            'selected_reports' => [$report1->id, $report2->id],
            'rejection_reason' => 'Test rejection reason',
        ]);

        $response->assertSessionHas('success');

        // Only report1 should be rejected
        $report1->refresh();
        $this->assertEquals('rejected', $report1->approval_status);
        $this->assertEquals('Test rejection reason', $report1->rejection_reason);

        // Report2 should not be rejected (different department)
        $report2->refresh();
        $this->assertEquals('pending', $report2->approval_status);
    }

    /** @test */
    public function audit_logs_are_created_for_batch_operations()
    {
        $report = DailyReport::factory()->create([
            'user_id' => $this->level1User->id,
            'department_id' => $this->department1->id,
            'approval_status' => 'pending',
        ]);

        // Clear existing logs
        \Illuminate\Support\Facades\Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) use ($report) {
                return $message === 'Batch approval: Report approved'
                    && $context['report_id'] === $report->id
                    && $context['user_id'] === $this->level2User->id
                    && $context['old_status'] === 'pending'
                    && $context['new_status'] === 'approved_by_leader';
            });

        $response = $this->actingAs($this->level2User)->post(route('daily-reports.batch-approve'), [
            'selected_reports' => [$report->id],
        ]);

        $response->assertSessionHas('success');
    }

    /** @test */
    public function level1_staff_cannot_perform_batch_operations()
    {
        $report = DailyReport::factory()->create([
            'user_id' => $this->level1User->id,
            'department_id' => $this->department1->id,
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($this->level1User)->post(route('daily-reports.batch-approve'), [
            'selected_reports' => [$report->id],
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Unauthorized', session('error'));

        // Report should not be modified
        $report->refresh();
        $this->assertEquals('pending', $report->approval_status);
    }

    /** @test */
    public function level5_can_approve_multiple_status_levels()
    {
        // Create reports at different approval stages
        $report1 = DailyReport::factory()->create([
            'user_id' => $this->level1User->id,
            'department_id' => $this->department1->id,
            'approval_status' => 'pending',
        ]);

        $report2 = DailyReport::factory()->create([
            'user_id' => $this->level2User->id,
            'department_id' => $this->department1->id,
            'approval_status' => 'approved_by_leader',
        ]);

        $report3 = DailyReport::factory()->create([
            'user_id' => $this->level3User->id,
            'department_id' => $this->department1->id,
            'approval_status' => 'approved_by_supervisor',
        ]);

        $response = $this->actingAs($this->level5User)->post(route('daily-reports.batch-approve'), [
            'selected_reports' => [$report1->id, $report2->id, $report3->id],
        ]);

        $response->assertSessionHas('success');

        // All reports should be approved to department head level
        $report1->refresh();
        $report2->refresh();
        $report3->refresh();

        $this->assertEquals('approved_by_department_head', $report1->approval_status);
        $this->assertEquals('approved_by_department_head', $report2->approval_status);
        $this->assertEquals('approved_by_department_head', $report3->approval_status);
    }
}

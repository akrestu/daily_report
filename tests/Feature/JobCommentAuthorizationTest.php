<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\Department;
use App\Models\JobComment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobCommentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create basic roles and departments needed for tests
        $this->createRolesAndDepartments();
    }

    protected function createRolesAndDepartments(): void
    {
        // Create roles
        Role::create(['name' => 'Admin', 'slug' => 'admin', 'level' => 5]);
        Role::create(['name' => 'Level 4', 'slug' => 'level4', 'level' => 4]);
        Role::create(['name' => 'Level 3', 'slug' => 'level3', 'level' => 3]);
        Role::create(['name' => 'Level 2', 'slug' => 'level2', 'level' => 2]);
        Role::create(['name' => 'Level 1', 'slug' => 'level1', 'level' => 1]);

        // Create departments
        Department::create(['name' => 'IT Department', 'code' => 'IT']);
        Department::create(['name' => 'HR Department', 'code' => 'HR']);
    }

    protected function createReport($user, $department): DailyReport
    {
        return DailyReport::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'job_name' => 'Test Job',
            'report_date' => now(),
            'due_date' => now()->addDays(7),
            'description' => 'Test activities description',
            'job_pic' => $user->id,
            'status' => 'pending',
            'approval_status' => 'pending',
        ]);
    }

    public function test_report_owner_can_add_comment_to_own_report(): void
    {
        $role = Role::where('slug', 'level1')->first();
        $department = Department::where('code', 'IT')->first();

        $user = User::factory()->create([
            'role_id' => $role->id,
            'department_id' => $department->id,
        ]);

        $report = $this->createReport($user, $department);

        $response = $this
            ->actingAs($user)
            ->postJson("/daily-reports/{$report->id}/comments", [
                'comment' => 'This is my comment on my own report',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('job_comments', [
            'daily_report_id' => $report->id,
            'user_id' => $user->id,
            'comment' => 'This is my comment on my own report',
        ]);
    }

    public function test_user_in_same_department_can_add_comment(): void
    {
        $role = Role::where('slug', 'level2')->first();
        $department = Department::where('code', 'IT')->first();

        $reportOwner = User::factory()->create([
            'role_id' => Role::where('slug', 'level1')->first()->id,
            'department_id' => $department->id,
        ]);

        $departmentColleague = User::factory()->create([
            'role_id' => $role->id,
            'department_id' => $department->id,
        ]);

        $report = $this->createReport($reportOwner, $department);

        $response = $this
            ->actingAs($departmentColleague)
            ->postJson("/daily-reports/{$report->id}/comments", [
                'comment' => 'Comment from department colleague',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('job_comments', [
            'daily_report_id' => $report->id,
            'user_id' => $departmentColleague->id,
        ]);
    }

    public function test_admin_can_add_comment_to_any_report(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $itDepartment = Department::where('code', 'IT')->first();
        $hrDepartment = Department::where('code', 'HR')->first();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'department_id' => $itDepartment->id,
        ]);

        $reportOwner = User::factory()->create([
            'role_id' => Role::where('slug', 'level1')->first()->id,
            'department_id' => $hrDepartment->id,
        ]);

        $report = $this->createReport($reportOwner, $hrDepartment);

        $response = $this
            ->actingAs($admin)
            ->postJson("/daily-reports/{$report->id}/comments", [
                'comment' => 'Admin comment',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('job_comments', [
            'daily_report_id' => $report->id,
            'user_id' => $admin->id,
            'comment' => 'Admin comment',
        ]);
    }

    public function test_unauthorized_user_cannot_add_comment_to_other_department_report(): void
    {
        $role = Role::where('slug', 'level1')->first();
        $itDepartment = Department::where('code', 'IT')->first();
        $hrDepartment = Department::where('code', 'HR')->first();

        $unauthorizedUser = User::factory()->create([
            'role_id' => $role->id,
            'department_id' => $hrDepartment->id,
        ]);

        $reportOwner = User::factory()->create([
            'role_id' => $role->id,
            'department_id' => $itDepartment->id,
        ]);

        $report = $this->createReport($reportOwner, $itDepartment);

        $response = $this
            ->actingAs($unauthorizedUser)
            ->postJson("/daily-reports/{$report->id}/comments", [
                'comment' => 'Trying to comment on unauthorized report',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('job_comments', [
            'daily_report_id' => $report->id,
            'user_id' => $unauthorizedUser->id,
        ]);
    }

    public function test_report_owner_can_view_comments_on_own_report(): void
    {
        $role = Role::where('slug', 'level1')->first();
        $department = Department::where('code', 'IT')->first();

        $user = User::factory()->create([
            'role_id' => $role->id,
            'department_id' => $department->id,
        ]);

        $report = $this->createReport($user, $department);

        JobComment::create([
            'daily_report_id' => $report->id,
            'user_id' => $user->id,
            'comment' => 'Test comment',
            'visibility' => 'public',
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/daily-reports/{$report->id}/comments");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonCount(1, 'comments');
    }

    public function test_unauthorized_user_cannot_view_comments_from_other_department(): void
    {
        $role = Role::where('slug', 'level1')->first();
        $itDepartment = Department::where('code', 'IT')->first();
        $hrDepartment = Department::where('code', 'HR')->first();

        $reportOwner = User::factory()->create([
            'role_id' => $role->id,
            'department_id' => $itDepartment->id,
        ]);

        $unauthorizedUser = User::factory()->create([
            'role_id' => $role->id,
            'department_id' => $hrDepartment->id,
        ]);

        $report = $this->createReport($reportOwner, $itDepartment);

        JobComment::create([
            'daily_report_id' => $report->id,
            'user_id' => $reportOwner->id,
            'comment' => 'Confidential comment',
            'visibility' => 'public',
        ]);

        $response = $this
            ->actingAs($unauthorizedUser)
            ->getJson("/daily-reports/{$report->id}/comments");

        $response->assertStatus(403);
    }

    public function test_admin_can_view_comments_on_any_report(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $itDepartment = Department::where('code', 'IT')->first();
        $hrDepartment = Department::where('code', 'HR')->first();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'department_id' => $itDepartment->id,
        ]);

        $reportOwner = User::factory()->create([
            'role_id' => Role::where('slug', 'level1')->first()->id,
            'department_id' => $hrDepartment->id,
        ]);

        $report = $this->createReport($reportOwner, $hrDepartment);

        JobComment::create([
            'daily_report_id' => $report->id,
            'user_id' => $reportOwner->id,
            'comment' => 'Comment from report owner',
            'visibility' => 'public',
        ]);

        $response = $this
            ->actingAs($admin)
            ->getJson("/daily-reports/{$report->id}/comments");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_comment_owner_can_delete_own_comment(): void
    {
        $role = Role::where('slug', 'level1')->first();
        $department = Department::where('code', 'IT')->first();

        $user = User::factory()->create([
            'role_id' => $role->id,
            'department_id' => $department->id,
        ]);

        $report = $this->createReport($user, $department);

        $comment = JobComment::create([
            'daily_report_id' => $report->id,
            'user_id' => $user->id,
            'comment' => 'My comment to delete',
            'visibility' => 'public',
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson("/comments/{$comment->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('job_comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_admin_can_delete_any_comment(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $userRole = Role::where('slug', 'level1')->first();
        $department = Department::where('code', 'IT')->first();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'department_id' => $department->id,
        ]);

        $regularUser = User::factory()->create([
            'role_id' => $userRole->id,
            'department_id' => $department->id,
        ]);

        $report = $this->createReport($regularUser, $department);

        $comment = JobComment::create([
            'daily_report_id' => $report->id,
            'user_id' => $regularUser->id,
            'comment' => 'Comment to be deleted by admin',
            'visibility' => 'public',
        ]);

        $response = $this
            ->actingAs($admin)
            ->deleteJson("/comments/{$comment->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('job_comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_comment(): void
    {
        $role = Role::where('slug', 'level1')->first();
        $department = Department::where('code', 'IT')->first();

        $user1 = User::factory()->create([
            'role_id' => $role->id,
            'department_id' => $department->id,
        ]);

        $user2 = User::factory()->create([
            'role_id' => $role->id,
            'department_id' => $department->id,
        ]);

        $report = $this->createReport($user1, $department);

        $comment = JobComment::create([
            'daily_report_id' => $report->id,
            'user_id' => $user1->id,
            'comment' => 'User1 comment',
            'visibility' => 'public',
        ]);

        $response = $this
            ->actingAs($user2)
            ->deleteJson("/comments/{$comment->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('job_comments', [
            'id' => $comment->id,
        ]);
    }
}

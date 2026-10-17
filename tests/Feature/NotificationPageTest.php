<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_are_grouped_linked_and_can_be_marked_read(): void
    {
        $department = Department::create(['name' => 'Maintenance', 'code' => 'MTC']);
        $role = Role::create(['name' => 'Level 1', 'slug' => 'level1']);
        $user = User::factory()->create(['role_id' => $role->id, 'department_id' => $department->id]);
        $report = DailyReport::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'job_name' => 'Inspeksi conveyor CV-03',
            'report_date' => today(),
            'due_date' => today()->addDay(),
            'description' => 'Deskripsi',
            'status' => 'completed',
            'approval_status' => 'approved',
            'job_pic' => $user->id,
        ]);
        Notification::create([
            'user_id' => $user->id,
            'daily_report_id' => $report->id,
            'type' => 'job_approved',
            'message' => 'Laporan "Inspeksi conveyor CV-03" disetujui',
            'is_read' => false,
        ]);

        $this->actingAs($user)->get(route('notifications.view-all'))
            ->assertOk()
            ->assertSee('Hari ini')
            ->assertSee('1 belum dibaca')
            ->assertSee(route('daily-reports.show', $report->id), false);

        $this->actingAs($user)->get(route('notifications.view-all', ['mark_read' => 1]))->assertOk();
        $this->assertSame(0, Notification::where('user_id', $user->id)->where('is_read', false)->count());
    }

    public function test_empty_state(): void
    {
        $role = Role::create(['name' => 'Level 1', 'slug' => 'level1']);

        $this->actingAs(User::factory()->create(['role_id' => $role->id]))
            ->get(route('notifications.view-all'))
            ->assertOk()
            ->assertSee('Belum ada notifikasi');
    }
}

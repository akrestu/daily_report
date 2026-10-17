<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportListTest extends TestCase
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

    private function report(User $owner, User $pic, string $name, string $approval): DailyReport
    {
        return DailyReport::create([
            'user_id' => $owner->id,
            'department_id' => $this->department->id,
            'job_name' => $name,
            'report_date' => today(),
            'due_date' => today()->addDay(),
            'description' => 'Deskripsi',
            'status' => 'completed',
            'approval_status' => $approval,
            'job_pic' => $pic->id,
            'rejection_reason' => $approval === 'rejected' ? 'Lampiran kurang' : null,
            'approved_by' => $approval === 'pending' ? null : $pic->id, // index hanya menampilkan laporan yang sudah diproses
        ]);
    }

    public function test_my_reports_and_assigned_reports_render_with_filters(): void
    {
        $owner = $this->user('level1');
        $pic = $this->user('level3');
        $this->report($owner, $pic, 'Servis genset 500 kVA', 'pending');

        $this->actingAs($owner)->get(route('daily-reports.user-jobs', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('Servis genset 500 kVA')
            ->assertSee('report-filter', false);

        $this->actingAs($pic)->get(route('daily-reports.assigned-jobs'))
            ->assertOk()
            ->assertSee('Servis genset 500 kVA');
    }

    public function test_all_reports_switches_between_approved_and_rejected(): void
    {
        $owner = $this->user('level1');
        $viewer = $this->user('level3');
        $this->report($owner, $viewer, 'Laporan disetujui', 'approved');
        $this->report($owner, $viewer, 'Laporan ditolak', 'rejected');

        $this->actingAs($viewer)->get(route('daily-reports.index', ['type' => 'rejected']))
            ->assertOk()
            ->assertSee('Ringkasan WA')
            ->assertSee('Laporan ditolak')
            ->assertDontSee('Laporan disetujui</a>', false);
    }
}

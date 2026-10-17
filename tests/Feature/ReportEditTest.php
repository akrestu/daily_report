<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportEditTest extends TestCase
{
    use RefreshDatabase;

    private Department $department;

    private User $owner;

    private User $pic;

    protected function setUp(): void
    {
        parent::setUp();
        $this->department = Department::create(['name' => 'Maintenance', 'code' => 'MTC']);
        $this->owner = $this->user('level1');
        $this->pic = $this->user('level2');
    }

    private function user(string $slug, ?Department $department = null): User
    {
        $role = Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);

        return User::factory()->create(['role_id' => $role->id, 'department_id' => ($department ?? $this->department)->id]);
    }

    private function report(): DailyReport
    {
        return DailyReport::create([
            'user_id' => $this->owner->id,
            'department_id' => $this->department->id,
            'job_name' => 'Servis genset',
            'report_date' => today(),
            'due_date' => today()->addDay(),
            'description' => 'Ganti oli genset',
            'status' => 'in_progress',
            'approval_status' => 'pending',
            'job_pic' => $this->pic->id,
            'attachment_path' => 'lama.pdf',
            'attachment_original_name' => 'lama.pdf',
        ]);
    }

    private function payload(array $override = []): array
    {
        return $override + [
            'job_name' => 'Servis genset unit 2',
            'report_date' => today()->toDateString(),
            'due_date' => today()->addDay()->toDateString(),
            'job_pic' => $this->pic->id,
            'status' => 'completed',
            'description' => 'Oli dan filter diganti',
        ];
    }

    public function test_edit_page_uses_new_form(): void
    {
        $report = $this->report();

        $this->actingAs($this->owner)->get(route('daily-reports.edit', $report))
            ->assertOk()
            ->assertSee('Progres Pekerjaan')
            ->assertSee('sgSlot', false)
            ->assertSee('lama.pdf')
            ->assertDontSee('name="department_id"', false);
    }

    public function test_update_keeps_department_and_existing_attachment(): void
    {
        $report = $this->report();
        $other = Department::create(['name' => 'HRD', 'code' => 'HRD']);

        $this->actingAs($this->owner)
            ->patch(route('daily-reports.update', $report), $this->payload(['department_id' => $other->id]))
            ->assertSessionHasNoErrors();

        $report->refresh();
        $this->assertSame('Servis genset unit 2', $report->job_name);
        $this->assertSame($this->department->id, $report->department_id);
        $this->assertSame('lama.pdf', $report->attachment_path);
    }

    public function test_validation_messages_are_indonesian(): void
    {
        $report = $this->report();

        $this->actingAs($this->owner)
            ->patch(route('daily-reports.update', $report), $this->payload(['job_name' => '']))
            ->assertSessionHasErrors(['job_name' => 'Nama pekerjaan wajib diisi.']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\JobPlan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPlanFormTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $slug, Department $department): User
    {
        $role = Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);

        return User::factory()->create(['role_id' => $role->id, 'department_id' => $department->id]);
    }

    public function test_create_and_edit_use_shared_form_and_store_plan(): void
    {
        $department = Department::create(['name' => 'Maintenance', 'code' => 'MTC']);
        $leader = $this->user('level3', $department);
        $member = $this->user('level1', $department);

        $this->actingAs($leader)->get(route('job-plans.create'))
            ->assertOk()
            ->assertSee('id="assignee-sheet"', false)
            ->assertSee($member->name)
            ->assertSee('Tugaskan');

        $this->actingAs($leader)->post(route('job-plans.store'), [
            'assignee_id' => $member->id,
            'job_name' => 'Inspeksi panel',
            'description' => 'Cek suhu panel B',
            'planned_date' => today()->toDateString(),
            'due_date' => today()->addDay()->toDateString(),
        ])->assertSessionHasNoErrors();

        $plan = JobPlan::firstOrFail();
        $this->assertSame($member->id, $plan->assignee_id);

        $this->actingAs($leader)->get(route('job-plans.edit', $plan))
            ->assertOk()
            ->assertSee('tidak bisa diganti')
            ->assertDontSee('id="assignee-sheet"', false);
    }

    public function test_due_date_before_planned_date_is_rejected_in_indonesian(): void
    {
        $department = Department::create(['name' => 'Maintenance', 'code' => 'MTC']);
        $leader = $this->user('level3', $department);
        $member = $this->user('level1', $department);

        $this->actingAs($leader)->post(route('job-plans.store'), [
            'assignee_id' => $member->id,
            'job_name' => 'Inspeksi panel',
            'description' => 'Cek suhu panel B',
            'planned_date' => today()->toDateString(),
            'due_date' => today()->subDay()->toDateString(),
        ])->assertSessionHasErrors(['due_date' => 'Due date harus tanggal yang sama atau setelah tanggal rencana.']);
    }
}

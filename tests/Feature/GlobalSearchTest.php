<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $slug, Department $department): User
    {
        $role = Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);

        return User::factory()->create(['role_id' => $role->id, 'department_id' => $department->id]);
    }

    private function report(User $owner, string $name): DailyReport
    {
        return DailyReport::create([
            'user_id' => $owner->id,
            'department_id' => $owner->department_id,
            'job_name' => $name,
            'report_date' => today(),
            'due_date' => today()->addDay(),
            'description' => 'Deskripsi',
            'status' => 'pending',
            'approval_status' => 'pending',
            'job_pic' => $this->user('level2', Department::find($owner->department_id))->id,
        ]);
    }

    public function test_short_query_returns_no_groups(): void
    {
        $user = $this->user('level1', Department::create(['name' => 'Maintenance', 'code' => 'MTC']));

        $this->actingAs($user)->getJson(route('search', ['q' => 'a']))
            ->assertOk()
            ->assertJson(['groups' => []]);
    }

    public function test_results_respect_report_policy(): void
    {
        $mtc = Department::create(['name' => 'Maintenance', 'code' => 'MTC']);
        $hr = Department::create(['name' => 'HRD', 'code' => 'HRD']);
        $me = $this->user('level1', $mtc);
        $this->report($me, 'Pompa sump milik saya');
        $this->report($this->user('level1', $hr), 'Pompa sump departemen lain');

        $response = $this->actingAs($me)->getJson(route('search', ['q' => 'pompa']))->assertOk();

        $titles = collect($response->json('groups'))->flatMap(fn ($g) => collect($g['items'])->pluck('title'));
        $this->assertSame(['Pompa sump milik saya'], $titles->all());
    }

    public function test_only_admin_can_search_users(): void
    {
        $mtc = Department::create(['name' => 'Maintenance', 'code' => 'MTC']);
        $this->user('level1', $mtc)->update(['name' => 'Budi Santoso']);
        $staff = $this->user('level2', $mtc);
        $admin = $this->user('admin', $mtc);

        $this->actingAs($staff)->getJson(route('search', ['q' => 'Budi']))
            ->assertOk()->assertJsonMissing(['label' => 'Pengguna']);

        $this->actingAs($admin)->getJson(route('search', ['q' => 'Budi']))
            ->assertOk()->assertJsonFragment(['label' => 'Pengguna'])->assertJsonFragment(['title' => 'Budi Santoso']);
    }
}

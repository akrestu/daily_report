<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\JobSite;
use App\Models\Role;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->department = Department::create(['name' => 'Maintenance', 'code' => 'MTC']);
        $role = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $this->admin = User::factory()->create(['role_id' => $role->id, 'name' => 'Admin Utama']);
    }

    public function test_admin_pages_render_with_design_system(): void
    {
        JobSite::create(['name' => 'Head Office', 'code' => 'HO', 'is_active' => true]);
        Section::create(['name' => 'Jaringan', 'code' => 'IT-NET', 'department_id' => $this->department->id, 'is_active' => true]);

        foreach ([
            'admin.users.index', 'admin.users.create', 'admin.departments.index', 'admin.job-sites.index',
            'admin.sections.index', 'admin.settings', 'admin.reports.cleanup.index',
        ] as $name) {
            $this->actingAs($this->admin)->get(route($name))
                ->assertOk()
                ->assertSee('sg-page-header', false)
                ->assertDontSee('Delete Selected');
        }

        $this->actingAs($this->admin)->get(route('admin.users.edit', $this->admin))->assertOk()->assertSee('Edit Pengguna');
    }

    public function test_user_filters_are_applied(): void
    {
        $level1 = Role::firstOrCreate(['slug' => 'level1'], ['name' => 'Level 1']);
        User::factory()->create(['name' => 'Budi Teknisi', 'role_id' => $level1->id, 'department_id' => $this->department->id]);
        User::factory()->create(['name' => 'Sari Gudang', 'role_id' => $level1->id]);

        $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'Budi']))
            ->assertOk()->assertSee('Budi Teknisi')->assertDontSee('Sari Gudang');

        $this->actingAs($this->admin)->get(route('admin.users.index', ['department' => $this->department->id]))
            ->assertOk()->assertSee('Budi Teknisi')->assertDontSee('Sari Gudang');
    }

    public function test_department_validation_error_reopens_sheet(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.departments.index'))
            ->post(route('admin.departments.store'), ['name' => '', 'code' => 'MTC', '_sheet' => ''])
            ->assertSessionHasErrors(['name', 'code']);

        $this->actingAs($this->admin)->get(route('admin.departments.index'))->assertOk();
    }
}

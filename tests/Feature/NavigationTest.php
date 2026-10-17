<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\Navigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $slug): User
    {
        $role = Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function keys(User $user): array
    {
        return array_column((new Navigation($user))->items(), 'key');
    }

    public function test_level1_creates_reports_but_has_no_approval_menu(): void
    {
        $keys = $this->keys($this->userWithRole('level1'));

        $this->assertContains('create', $keys);
        $this->assertContains('tickets-mine', $keys);
        $this->assertNotContains('approvals', $keys);
        $this->assertNotContains('plans', $keys);
        $this->assertNotContains('admin-users', $keys);
    }

    public function test_approver_sees_approval_and_job_plan_menus(): void
    {
        $keys = $this->keys($this->userWithRole('level3'));

        $this->assertContains('approvals', $keys);
        $this->assertContains('plans', $keys);
    }

    public function test_level8_cannot_create_reports_and_fab_goes_to_approvals(): void
    {
        $user = $this->userWithRole('level8');

        $this->assertNotContains('create', $this->keys($user));
        $this->assertNotContains('org', $this->keys($user));
        $this->assertSame('approvals', (new Navigation($user))->bottomNav()['fab']['key']);
    }

    public function test_admin_sees_admin_menu_and_ticket_triage(): void
    {
        $keys = $this->keys($this->userWithRole('admin'));

        $this->assertContains('admin-users', $keys);
        $this->assertContains('tickets', $keys);
        $this->assertNotContains('create', $keys);
        $this->assertNotContains('reports', $keys);
    }

    public function test_shell_renders_bottom_nav_and_sidebar(): void
    {
        $this->actingAs($this->userWithRole('level1'))
            ->get(route('daily-reports.create'))
            ->assertOk()
            ->assertSee('sg-bottom-nav', false)
            ->assertSee('sg-sidebar', false)
            ->assertSee('Lainnya')
            ->assertDontSee('cdn.jsdelivr.net', false);
    }
}

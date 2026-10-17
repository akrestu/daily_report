<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiKitTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $slug): User
    {
        $role = Role::create(['name' => ucfirst($slug), 'slug' => $slug]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_admin_can_view_component_catalog(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get('/_ui')
            ->assertOk()
            ->assertSee('Komponen SiGAP')
            ->assertSee('sg-badge--pending', false)
            ->assertSee('Lewat 1j 20m');
    }

    public function test_non_admin_gets_404_outside_local(): void
    {
        $this->actingAs($this->userWithRole('level1'))
            ->get('/_ui')
            ->assertNotFound();
    }
}

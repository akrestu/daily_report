<?php

namespace Tests\Feature\Auth;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_public_registration_cannot_create_users(): void
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $department = Department::factory()->create();

        $this->post('/register', [
            'name' => 'Attacker',
            'user_id' => 'attacker',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $adminRole->id,
            'department_id' => $department->id,
        ])->assertNotFound();

        $this->assertGuest();
        $this->assertFalse(User::where('user_id', 'attacker')->exists());
    }
}

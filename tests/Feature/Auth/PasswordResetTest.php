<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    // Passwords are reset by admins; the email reset flow is disabled
    public function test_email_password_reset_routes_are_not_available(): void
    {
        $this->get('/forgot-password')->assertNotFound();
        $this->post('/forgot-password', ['email' => 'user@example.com'])->assertNotFound();
        $this->get('/reset-password/some-token')->assertNotFound();
        $this->post('/reset-password', ['token' => 'some-token'])->assertNotFound();
    }
}

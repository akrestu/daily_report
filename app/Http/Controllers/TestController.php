<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function testCreateUser()
    {
        try {
            // Get first role and department
            $role = Role::first();
            $department = Department::first();
            
            if (!$role) {
                return "No roles found in the database. Please create a role first.";
            }
            
            // Create test user data
            $userData = [
                'name' => 'Test User ' . time(),
                'email' => 'test' . time() . '@example.com',
                'password' => Hash::make('password123'),
                'role_id' => $role->id,
                'department_id' => $department ? $department->id : null,
                'user_id' => 'testuser' . time(),
                'email_verified_at' => now(),
            ];
            
            // Log attempt
            Log::info('Attempting to create user with data', $userData);
            
            // Create user
            $user = User::create($userData);
            
            // Log success
            Log::info('User created successfully', ['user_id' => $user->id]);
            
            return "User created successfully with ID: " . $user->id;
        } catch (\Exception $e) {
            // Log error
            Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return "Error creating user: " . $e->getMessage();
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('slug', 'admin')->first();
        $departmentHeadRole = Role::where('slug', 'department_head')->first();
        $leaderRole = Role::where('slug', 'leader')->first();
        $staffRole = Role::where('slug', 'staff')->first();
        
        // Get departments
        $itDepartment = Department::where('name', 'IT')->first() ?? Department::create(['name' => 'IT']);
        $hrDepartment = Department::where('name', 'HR')->first() ?? Department::create(['name' => 'HR']);
        
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'department_id' => $itDepartment->id,
            ]
        );
        
        // Create department head
        User::updateOrCreate(
            ['email' => 'head@example.com'],
            [
                'name' => 'Department Head',
                'password' => Hash::make('password'),
                'role_id' => $departmentHeadRole->id,
                'department_id' => $itDepartment->id,
            ]
        );
        
        // Create leader
        User::updateOrCreate(
            ['email' => 'leader@example.com'],
            [
                'name' => 'Team Leader',
                'password' => Hash::make('password'),
                'role_id' => $leaderRole->id,
                'department_id' => $itDepartment->id,
            ]
        );
        
        // Create staff
        User::updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff Member',
                'password' => Hash::make('password'),
                'role_id' => $staffRole->id,
                'department_id' => $itDepartment->id,
            ]
        );
    }
} 
<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\JobSite;
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
        $level1Role = Role::where('slug', 'level1')->first();
        $level2Role = Role::where('slug', 'level2')->first();
        $level3Role = Role::where('slug', 'level3')->first();
        $level4Role = Role::where('slug', 'level4')->first();
        $level5Role = Role::where('slug', 'level5')->first();

        // Get departments (assume they already exist from DepartmentsSeeder)
        $itDepartment = Department::where('code', 'IT')->first();
        $hrDepartment = Department::where('code', 'HR')->first();

        // If departments don't exist, skip seeding (run DepartmentsSeeder first)
        if (!$itDepartment || !$hrDepartment) {
            $this->command->warn('Departments not found. Please run DepartmentsSeeder first.');
            return;
        }

        // Get job sites
        $headOffice = JobSite::where('code', 'HO')->first();
        $surabayaOffice = JobSite::where('code', 'SBY')->first();
        $bandungOffice = JobSite::where('code', 'BDG')->first();

        // Create admin user (no jobsite - can access all)
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'department_id' => $itDepartment->id,
                'job_site_id' => null,
            ]
        );

        // Create Level 5 user (Head Office) - highest operational level
        User::updateOrCreate(
            ['email' => 'level5@example.com'],
            [
                'name' => 'Level 5 User',
                'password' => Hash::make('password'),
                'role_id' => $level5Role->id,
                'department_id' => $itDepartment->id,
                'job_site_id' => $headOffice?->id,
            ]
        );

        // Create Level 2 user (Head Office)
        User::updateOrCreate(
            ['email' => 'level2@example.com'],
            [
                'name' => 'Level 2 User',
                'password' => Hash::make('password'),
                'role_id' => $level2Role->id,
                'department_id' => $itDepartment->id,
                'job_site_id' => $headOffice?->id,
            ]
        );

        // Create Level 1 user (Head Office)
        User::updateOrCreate(
            ['email' => 'level1@example.com'],
            [
                'name' => 'Level 1 User',
                'password' => Hash::make('password'),
                'role_id' => $level1Role->id,
                'department_id' => $itDepartment->id,
                'job_site_id' => $headOffice?->id,
            ]
        );

        // Create additional users for testing different jobsites
        // Surabaya Branch - Level 1
        User::updateOrCreate(
            ['email' => 'level1.surabaya@example.com'],
            [
                'name' => 'Level 1 Surabaya',
                'password' => Hash::make('password'),
                'role_id' => $level1Role->id,
                'department_id' => $itDepartment->id,
                'job_site_id' => $surabayaOffice?->id,
            ]
        );

        // Surabaya Branch - Level 2
        User::updateOrCreate(
            ['email' => 'level2.surabaya@example.com'],
            [
                'name' => 'Level 2 Surabaya',
                'password' => Hash::make('password'),
                'role_id' => $level2Role->id,
                'department_id' => $itDepartment->id,
                'job_site_id' => $surabayaOffice?->id,
            ]
        );

        // Bandung Branch - Level 1
        User::updateOrCreate(
            ['email' => 'level1.bandung@example.com'],
            [
                'name' => 'Level 1 Bandung',
                'password' => Hash::make('password'),
                'role_id' => $level1Role->id,
                'department_id' => $itDepartment->id,
                'job_site_id' => $bandungOffice?->id,
            ]
        );
    }
} 
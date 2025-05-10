<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            DepartmentsSeeder::class,
            UserIdSeeder::class,
        ]);

        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role_id' => Role::where('slug', 'admin')->first()->id,
            ]
        );

        // Create a user for each role in each department
        $departments = Department::all();
        $roles = Role::whereIn('slug', ['department_head', 'leader', 'staff'])->get();

        foreach ($departments as $department) {
            foreach ($roles as $role) {
                $email = strtolower($role->slug) . '.' . strtolower($department->code) . '@example.com';
                
                User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => ucfirst($role->slug) . ' ' . $department->name,
                        'password' => Hash::make('password'),
                        'role_id' => $role->id,
                        'department_id' => $department->id,
                    ]
                );
            }
        }
    }
}

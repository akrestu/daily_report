<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Administrator has full access to all functionalities',
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Manager can approve or reject reports',
            ],
            [
                'name' => 'Employee',
                'slug' => 'employee',
                'description' => 'Regular employee who can submit daily reports',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
} 
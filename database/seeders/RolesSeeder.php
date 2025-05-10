<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Highest level role with full access to the system',
            ],
            [
                'name' => 'Department Head',
                'slug' => 'department_head',
                'description' => 'Can approve work for all roles below them within their department',
            ],
            [
                'name' => 'Leader',
                'slug' => 'leader',
                'description' => 'Can approve work for staff within their department',
            ],
            [
                'name' => 'Staff',
                'slug' => 'staff',
                'description' => 'Regular staff member that reports to leader and department head',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                [
                    'name' => $role['name'],
                    'description' => $role['description'],
                ]
            );
        }
    }
}

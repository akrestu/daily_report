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
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Administrator has full access to all functionalities',
            ],
            [
                'name' => 'Level 1',
                'slug' => 'level1',
                'description' => 'Level 1 - Lowest approval level, cannot be PIC, can only assign to Level 2',
            ],
            [
                'name' => 'Level 2',
                'slug' => 'level2',
                'description' => 'Level 2 - Can approve Level 1 reports, can only assign to Level 3',
            ],
            [
                'name' => 'Level 3',
                'slug' => 'level3',
                'description' => 'Level 3 - Can approve Level 2 reports, can only assign to Level 4',
            ],
            [
                'name' => 'Level 4',
                'slug' => 'level4',
                'description' => 'Level 4 - Can approve Level 3 reports, can only assign to Level 5',
            ],
            [
                'name' => 'Level 5',
                'slug' => 'level5',
                'description' => 'Level 5 - Highest approval level below Admin, can approve Level 1-4 reports, cannot assign to Admin',
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

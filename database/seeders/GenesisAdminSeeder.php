<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GenesisAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the admin role or create it if it doesn't exist
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Highest level role with full access to the system'
            ]
        );
        
        // Create the Genesis admin user with specific user_id and explicitly set role_id to 1
        User::updateOrCreate(
            ['user_id' => 'genesis25'],
            [
                'name' => 'Genesis Admin',
                'email' => 'genesis25@example.com',
                'password' => Hash::make('Kristanto1'),
                'role_id' => 1, // Explicitly set to role_id 1 (admin)
            ]
        );
        
        $this->command->info('Genesis Admin user created successfully!');
    }
}

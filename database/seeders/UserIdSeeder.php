<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereNull('user_id')->get();
        
        foreach ($users as $user) {
            // Generate a user ID based on initials and a random string
            $initials = $this->getInitials($user->name);
            $randomStr = Str::upper(Str::random(4));
            $userId = $initials . $randomStr;
            
            // Make sure it's unique
            while (User::where('user_id', $userId)->exists()) {
                $randomStr = Str::upper(Str::random(4));
                $userId = $initials . $randomStr;
            }
            
            $user->user_id = $userId;
            $user->save();
        }
    }
    
    /**
     * Get initials from a name.
     */
    private function getInitials(string $name): string
    {
        $words = explode(' ', $name);
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        
        // Ensure at least 2 characters
        if (strlen($initials) < 2) {
            $initials = str_pad($initials, 2, 'U');
        }
        
        return $initials;
    }
} 
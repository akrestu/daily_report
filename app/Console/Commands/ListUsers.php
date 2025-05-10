<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all users in the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::with(['role', 'department'])->get();
        
        $this->table(
            ['User ID', 'Email', 'Name', 'Role', 'Department'],
            $users->map(function ($user) {
                return [
                    'User ID' => $user->user_id,
                    'Email' => $user->email,
                    'Name' => $user->name,
                    'Role' => $user->role ? $user->role->name : 'None',
                    'Department' => $user->department ? $user->department->name : 'None',
                ];
            })
        );
        
        return Command::SUCCESS;
    }
}

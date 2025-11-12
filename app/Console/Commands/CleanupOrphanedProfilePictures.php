<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOrphanedProfilePictures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:orphaned-profile-pictures {--dry-run : Show what would be cleaned without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned profile picture references in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Scanning for orphaned profile picture references...');
        
        $users = User::whereNotNull('profile_picture')->get();
        $orphanedCount = 0;
        $cleanedCount = 0;
        
        foreach ($users as $user) {
            if (!Storage::disk('public')->exists($user->profile_picture)) {
                $orphanedCount++;
                
                if ($dryRun) {
                    $this->warn("Would clean: User {$user->id} ({$user->name}) - {$user->profile_picture}");
                } else {
                    $this->warn("Cleaning: User {$user->id} ({$user->name}) - {$user->profile_picture}");
                    $user->update(['profile_picture' => null]);
                    $cleanedCount++;
                }
            }
        }
        
        if ($orphanedCount === 0) {
            $this->info('No orphaned profile picture references found.');
        } else {
            if ($dryRun) {
                $this->info("Found {$orphanedCount} orphaned profile picture references.");
                $this->info('Run without --dry-run to clean them up.');
            } else {
                $this->info("Cleaned up {$cleanedCount} orphaned profile picture references.");
            }
        }
        
        return 0;
    }
}

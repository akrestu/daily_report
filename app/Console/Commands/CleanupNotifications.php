<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup {--days=30 : Number of days to keep notifications} {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old notifications to keep database performant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Looking for notifications older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')})...");
        
        // Get count of notifications to be deleted
        $query = Notification::where('created_at', '<', $cutoffDate);
        $count = $query->count();
        
        if ($count === 0) {
            $this->info('No old notifications found to clean up.');
            return Command::SUCCESS;
        }
        
        $this->warn("Found {$count} notifications to clean up.");
        
        if ($dryRun) {
            $this->info('DRY RUN: No notifications were actually deleted.');
            
            // Show breakdown by type
            $breakdown = Notification::where('created_at', '<', $cutoffDate)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get();
                
            $this->table(['Type', 'Count'], $breakdown->map(function($item) {
                return [$item->type, $item->count];
            })->toArray());
            
            return Command::SUCCESS;
        }
        
        // Confirm deletion in production
        if (app()->environment('production')) {
            if (!$this->confirm("Are you sure you want to delete {$count} notifications?")) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }
        
        try {
            // Delete in chunks to avoid memory issues
            $deleted = 0;
            $chunkSize = 1000;
            
            $this->output->progressStart($count);
            
            do {
                $chunk = Notification::where('created_at', '<', $cutoffDate)
                    ->limit($chunkSize)
                    ->get();
                    
                if ($chunk->isEmpty()) {
                    break;
                }
                
                $chunkIds = $chunk->pluck('id');
                $deletedInChunk = Notification::whereIn('id', $chunkIds)->delete();
                $deleted += $deletedInChunk;
                
                $this->output->progressAdvance($deletedInChunk);
                
                // Small delay to prevent overwhelming the database
                usleep(100000); // 0.1 second
                
            } while ($chunk->count() === $chunkSize);
            
            $this->output->progressFinish();
            
            $this->info("Successfully cleaned up {$deleted} old notifications.");
            
        } catch (\Exception $e) {
            $this->error("Error during cleanup: " . $e->getMessage());
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
} 
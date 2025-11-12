<?php

namespace App\Console\Commands;

use App\Models\DailyReport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupDailyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:cleanup 
                            {--days=365 : Number of days to keep reports} 
                            {--status=* : Only cleanup reports with specific status (completed, approved, rejected)}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--keep-attachments : Keep attachment files when deleting reports}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old daily reports to maintain database performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $statuses = $this->option('status');
        $dryRun = $this->option('dry-run');
        $keepAttachments = $this->option('keep-attachments');
        
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Looking for daily reports older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')})...");
        
        // Build query
        $query = DailyReport::where('created_at', '<', $cutoffDate);
        
        // Filter by status if specified
        if (!empty($statuses)) {
            $validStatuses = ['pending', 'in_progress', 'completed', 'approved', 'rejected'];
            $statuses = array_intersect($statuses, $validStatuses);
            
            if (!empty($statuses)) {
                $query->where(function($q) use ($statuses) {
                    $q->whereIn('status', $statuses)
                      ->orWhereIn('approval_status', $statuses);
                });
                $this->info("Filtering by status: " . implode(', ', $statuses));
            }
        }
        
        $count = $query->count();
        
        if ($count === 0) {
            $this->info('No old reports found to clean up.');
            return Command::SUCCESS;
        }
        
        $this->warn("Found {$count} reports to clean up.");
        
        if ($dryRun) {
            $this->info('DRY RUN: No reports were actually deleted.');
            
            // Show breakdown
            $this->showBreakdown($query->get(), $cutoffDate);
            
            return Command::SUCCESS;
        }
        
        // Safety confirmation in production
        if (app()->environment('production')) {
            if (!$this->confirm("Are you sure you want to delete {$count} daily reports? This action cannot be undone!")) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
            
            // Double confirmation for production
            if (!$this->confirm("This will permanently delete reports and their related data. Are you absolutely sure?")) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }
        
        try {
            $deleted = $this->performCleanup($query, $keepAttachments);
            $this->info("Successfully cleaned up {$deleted} old daily reports.");
            
        } catch (\Exception $e) {
            $this->error("Error during cleanup: " . $e->getMessage());
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Show breakdown of reports to be deleted
     */
    private function showBreakdown($reports, $cutoffDate)
    {
        // Breakdown by status
        $statusBreakdown = $reports->groupBy('status')->map->count();
        $approvalBreakdown = $reports->groupBy('approval_status')->map->count();
        
        $this->table(['Status', 'Count'], $statusBreakdown->map(function($count, $status) {
            return [$status ?: 'null', $count];
        })->values()->toArray());
        
        $this->table(['Approval Status', 'Count'], $approvalBreakdown->map(function($count, $status) {
            return [$status ?: 'null', $count];
        })->values()->toArray());
        
        // Breakdown by department
        $departmentBreakdown = $reports->groupBy('department_id')->map->count();
        $this->table(['Department ID', 'Count'], $departmentBreakdown->map(function($count, $deptId) {
            return [$deptId ?: 'null', $count];
        })->values()->toArray());
        
        // Show reports with attachments
        $withAttachments = $reports->filter(function($report) {
            return !empty($report->attachment_path);
        });
        
        if ($withAttachments->count() > 0) {
            $this->warn("Reports with attachments: {$withAttachments->count()}");
        }
    }
    
    /**
     * Perform the actual cleanup
     */
    private function performCleanup($query, $keepAttachments)
    {
        $deleted = 0;
        $chunkSize = 100; // Smaller chunks for reports due to related data
        
        $totalReports = $query->count();
        $this->output->progressStart($totalReports);
        
        do {
            // Get chunk of reports with their relationships
            $chunk = $query->with(['comments', 'notifications'])
                          ->limit($chunkSize)
                          ->get();
                          
            if ($chunk->isEmpty()) {
                break;
            }
            
            foreach ($chunk as $report) {
                // Delete attachment file if not keeping attachments
                if (!$keepAttachments && $report->attachment_path) {
                    $this->deleteAttachmentFile($report->attachment_path);
                }
                
                // Delete related data first (cascade should handle this, but being explicit)
                $report->comments()->delete();
                $report->notifications()->delete();
                
                // Delete the report
                $report->delete();
                $deleted++;
                
                $this->output->progressAdvance();
            }
            
            // Small delay to prevent overwhelming the database
            usleep(50000); // 0.05 second
            
        } while ($chunk->count() === $chunkSize);
        
        $this->output->progressFinish();
        
        return $deleted;
    }
    
    /**
     * Delete attachment file from storage
     */
    private function deleteAttachmentFile($attachmentPath)
    {
        try {
            if (Storage::disk('public')->exists($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
                $this->line("Deleted attachment: {$attachmentPath}", null, 'vv');
            }
        } catch (\Exception $e) {
            $this->warn("Failed to delete attachment {$attachmentPath}: " . $e->getMessage());
        }
    }
} 
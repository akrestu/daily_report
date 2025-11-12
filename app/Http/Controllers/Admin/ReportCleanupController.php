<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReportCleanupController extends Controller
{
    /**
     * Show the cleanup management page
     */
    public function index(): View
    {
        // Get statistics for different time periods
        $stats = $this->getCleanupStats();
        
        return view('admin.reports.cleanup', compact('stats'));
    }
    
    /**
     * Preview what would be cleaned up
     */
    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:3650',
            'statuses' => 'array',
            'statuses.*' => 'in:pending,in_progress,completed,approved,rejected'
        ]);
        
        $days = $request->input('days');
        $statuses = $request->input('statuses', []);
        
        $cutoffDate = Carbon::now()->subDays($days);
        
        // Build query
        $query = DailyReport::where('created_at', '<', $cutoffDate);
        
        if (!empty($statuses)) {
            $query->where(function($q) use ($statuses) {
                $q->whereIn('status', $statuses)
                  ->orWhereIn('approval_status', $statuses);
            });
        }
        
        $reports = $query->get();
        $count = $reports->count();
        
        // Get breakdown
        $breakdown = [
            'total' => $count,
            'by_status' => $reports->groupBy('status')->map->count()->toArray(),
            'by_approval_status' => $reports->groupBy('approval_status')->map->count()->toArray(),
            'by_department' => $reports->groupBy('department_id')->map->count()->toArray(),
            'with_attachments' => $reports->filter(fn($r) => !empty($r->attachment_path))->count(),
            'total_size' => $this->calculateTotalSize($reports),
        ];
        
        return response()->json([
            'success' => true,
            'breakdown' => $breakdown,
            'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Execute the cleanup
     */
    public function execute(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:3650',
            'statuses' => 'array',
            'statuses.*' => 'in:pending,in_progress,completed,approved,rejected',
            'keep_attachments' => 'boolean'
        ]);
        
        $days = $request->input('days');
        $statuses = $request->input('statuses', []);
        $keepAttachments = $request->input('keep_attachments', false);
        
        try {
            // Build the artisan command
            $command = "reports:cleanup --days={$days}";
            
            if (!empty($statuses)) {
                foreach ($statuses as $status) {
                    $command .= " --status={$status}";
                }
            }
            
            if ($keepAttachments) {
                $command .= " --keep-attachments";
            }
            
            // Execute the command
            $exitCode = Artisan::call($command);
            $output = Artisan::output();
            
            if ($exitCode === 0) {
                // Parse the output to get the number of deleted reports
                preg_match('/Successfully cleaned up (\d+) old daily reports/', $output, $matches);
                $deletedCount = $matches[1] ?? 0;
                
                return response()->json([
                    'success' => true,
                    'message' => "Successfully cleaned up {$deletedCount} old daily reports.",
                    'deleted_count' => (int) $deletedCount,
                    'output' => $output
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Cleanup command failed.',
                    'output' => $output
                ], 500);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during cleanup: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get cleanup statistics
     */
    private function getCleanupStats(): array
    {
        $now = Carbon::now();
        
        return [
            'total_reports' => DailyReport::count(),
            'older_than_30_days' => DailyReport::where('created_at', '<', $now->copy()->subDays(30))->count(),
            'older_than_90_days' => DailyReport::where('created_at', '<', $now->copy()->subDays(90))->count(),
            'older_than_365_days' => DailyReport::where('created_at', '<', $now->copy()->subDays(365))->count(),
            'older_than_730_days' => DailyReport::where('created_at', '<', $now->copy()->subDays(730))->count(),
            'by_status' => DailyReport::selectRaw('status, COUNT(*) as count')
                                   ->groupBy('status')
                                   ->pluck('count', 'status')
                                   ->toArray(),
            'by_approval_status' => DailyReport::selectRaw('approval_status, COUNT(*) as count')
                                             ->groupBy('approval_status')
                                             ->pluck('count', 'approval_status')
                                             ->toArray(),
            'with_attachments' => DailyReport::whereNotNull('attachment_path')
                                           ->where('attachment_path', '!=', '')
                                           ->count(),
            'storage_size' => $this->getStorageSize(),
        ];
    }
    
    /**
     * Calculate total size of reports
     */
    private function calculateTotalSize($reports): string
    {
        $totalSize = 0;
        
        foreach ($reports as $report) {
            if ($report->attachment_path && Storage::disk('public')->exists($report->attachment_path)) {
                $totalSize += Storage::disk('public')->size($report->attachment_path);
            }
        }
        
        return $this->formatBytes($totalSize);
    }
    
    /**
     * Get total storage size used by attachments
     */
    private function getStorageSize(): string
    {
        $totalSize = 0;
        $attachmentPath = 'attachments';
        
        try {
            if (Storage::disk('public')->exists($attachmentPath)) {
                $files = Storage::disk('public')->allFiles($attachmentPath);
                foreach ($files as $file) {
                    $totalSize += Storage::disk('public')->size($file);
                }
            }
        } catch (\Exception $e) {
            // If we can't calculate size, return unknown
            return 'Unknown';
        }
        
        return $this->formatBytes($totalSize);
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
} 
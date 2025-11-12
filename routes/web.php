<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TestController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

// Redirect root to dashboard or login page
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::post('/dashboard/toggle-sidebar', [DashboardController::class, 'toggleSidebar'])->middleware(['auth'])->name('dashboard.toggle-sidebar');

// Test Chart Route
Route::get('/test-chart', function() {
    return view('test-chart');
})->middleware(['auth'])->name('test-chart');

Route::middleware('auth')->group(function () {
    // Test Route for User Creation
    Route::get('/test-create-user', [TestController::class, 'testCreateUser'])->name('test.create-user');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/picture', [ProfileController::class, 'updateProfilePicture'])->name('profile.update-picture');
    
    // Notification Routes
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'getNotifications'])->name('notifications.get');
    Route::post('/notifications/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/clear-all', [\App\Http\Controllers\NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::get('/notifications/all', [\App\Http\Controllers\NotificationController::class, 'viewAll'])->name('notifications.view-all');
    Route::get('/notifications/preferences', [\App\Http\Controllers\NotificationController::class, 'getPreferences'])->name('notifications.preferences.get');
    Route::post('/notifications/preferences', [\App\Http\Controllers\NotificationController::class, 'updatePreferences'])->name('notifications.preferences.update');
    
    // Organization Chart Route
    Route::get('/organization-chart', [\App\Http\Controllers\OrganizationChartController::class, 'index'])->name('organization.chart');

    // PWA Install Page
    Route::get('/install-app', function () {
        return view('pwa-install');
    })->name('pwa.install');
    
    // Daily Reports Routes
    Route::get('/daily-reports/pending', [DailyReportController::class, 'pending'])->name('daily-reports.pending');
    Route::get('/daily-reports/my-jobs', [DailyReportController::class, 'userJobs'])->name('daily-reports.user-jobs');
    Route::get('/daily-reports/assigned-jobs', [DailyReportController::class, 'assignedJobs'])->name('daily-reports.assigned-jobs');
    Route::post('/daily-reports/{dailyReport}/approval', [DailyReportController::class, 'approval'])->name('daily-reports.approval');
    Route::post('/daily-reports/batch-approve', [DailyReportController::class, 'batchApprove'])->name('daily-reports.batch-approve');
    Route::post('/daily-reports/batch-reject', [DailyReportController::class, 'batchReject'])->name('daily-reports.batch-reject');
    Route::delete('/daily-reports/batch-delete', [DailyReportController::class, 'batchDelete'])->name('daily-reports.batch-delete');
    
    // Daily Reports Import/Export Routes
    Route::get('/daily-reports/export-all', [DailyReportController::class, 'exportAll'])->name('daily-reports.export-all');
    Route::get('/daily-reports/export', [DailyReportController::class, 'export'])->name('daily-reports.export');
    Route::get('/daily-reports/import', [DailyReportController::class, 'showImport'])->name('daily-reports.show-import');
    Route::post('/daily-reports/import', [DailyReportController::class, 'import'])->name('daily-reports.import');
    Route::get('/daily-reports/export-template', [DailyReportController::class, 'exportTemplate'])->name('daily-reports.export-template');
    
    Route::resource('daily-reports', DailyReportController::class);
    Route::post('daily-reports/store-multiple', [DailyReportController::class, 'storeMultiple'])
        ->middleware('web')
        ->name('daily-reports.store-multiple');
    
    // Job Comments Routes
    Route::get('/daily-reports/{reportId}/comments', [\App\Http\Controllers\JobCommentController::class, 'getComments'])->name('job-comments.index');
    Route::post('/daily-reports/{reportId}/comments', [\App\Http\Controllers\JobCommentController::class, 'store'])->name('job-comments.store');
    Route::delete('/comments/{commentId}', [\App\Http\Controllers\JobCommentController::class, 'destroy'])->name('job-comments.destroy');
    
    // Debug route for comments
    Route::get('/debug/comments/{reportId}', function($reportId) {
        if (Auth::check()) {
            $report = \App\Models\DailyReport::findOrFail($reportId);
            $comments = $report->comments()->with('user')->orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'report' => $report->only(['id', 'job_name']),
                'comments_count' => $comments->count(),
                'comments' => $comments->map(function($comment) {
                    return [
                        'id' => $comment->id,
                        'user' => $comment->user->name,
                        'comment' => $comment->comment,
                        'visibility' => $comment->visibility,
                        'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                    ];
                })
            ]);
        }
        
        return response()->json(['error' => 'Unauthorized'], 401);
    })->middleware('auth');
    
    // Admin Routes (only accessible by admin)
    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin.only'], function () {
        // Admin dashboard route
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Reports routes for admin
        Route::get('/reports', [DailyReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{dailyReport}', [DailyReportController::class, 'show'])->name('reports.show');
        
        // First define the batch delete route before the resource
        Route::delete('/users/batch-delete', [UserController::class, 'batchDelete'])->name('users.batch-delete');
        
        // User import/export routes
        Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
        Route::get('/users/import', [UserController::class, 'showImport'])->name('users.show-import');
        Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
        Route::get('/users/export-template', [UserController::class, 'exportTemplate'])->name('users.export-template');
        
        // Then define the resource route
        Route::resource('users', UserController::class);
        
        // Department management
        Route::delete('/departments/batch-delete', [\App\Http\Controllers\Admin\DepartmentController::class, 'batchDelete'])->name('departments.batch-delete');
        Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class);
        
        // Admin settings
        Route::get('/settings', function() {
            return view('admin.settings');
        })->name('settings');
        
        // Report cleanup management
        Route::get('/reports/cleanup', [\App\Http\Controllers\Admin\ReportCleanupController::class, 'index'])->name('reports.cleanup.index');
        Route::post('/reports/cleanup/preview', [\App\Http\Controllers\Admin\ReportCleanupController::class, 'preview'])->name('reports.cleanup.preview');
        Route::post('/reports/cleanup/execute', [\App\Http\Controllers\Admin\ReportCleanupController::class, 'execute'])->name('reports.cleanup.execute');
    });
    
    // Debug Routes
    Route::get('/debug/roles', [DebugController::class, 'debugRoles'])->name('debug.roles');
});

// Add file attachment route
Route::get('/storage/attachments/{filename}', function ($filename) {
    /** @var User|null $user */
    $user = Auth::user();
    if (!$user) {
        abort(403);
    }

    // Sanitize filename to prevent path traversal
    $filename = basename($filename);
    $path = 'attachments/' . $filename;
    
    // Check if file exists
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    
    // Check if user has access to this attachment
    $report = \App\Models\DailyReport::where('attachment_path', $path)
        ->orWhere('attachment_path', 'LIKE', '%' . $filename)
        ->first();
        
    if (!$report) {
        abort(404);
    }
    
    // Check access permissions
    if (!($user->isAdmin() || 
          $user->isDepartmentHead() || 
          $user->id === $report->user_id || 
          ($user->department_id === $report->department_id && ($user->isLeader() || $user->isStaff())))) {
        abort(403);
    }
    
    return response()->file(storage_path('app/public/' . $path));
})->name('attachments.show')->middleware('auth');

require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\JobPlanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicTicketController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ===== STATIC ASSET ROUTES (FALLBACK FOR SERVERS WHERE .htaccess DOESN'T WORK) =====
// Serve PWA icons directly via Laravel routes
// This ensures icons work even if .htaccess is disabled or server is Nginx
Route::get('/icons/{filename}', function ($filename) {
    $path = public_path('icons/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    $mimeType = mime_content_type($path);

    return response()->file($path, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000', // Cache for 1 year
    ]);
})->where('filename', '.*\.(png|jpg|jpeg|svg|ico)$');

// Serve screenshots
Route::get('/screenshots/{filename}', function ($filename) {
    $path = public_path('screenshots/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    $mimeType = mime_content_type($path);

    return response()->file($path, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('filename', '.*\.(png|jpg|jpeg)$');

// ===== PUBLIC TICKET ROUTES (tanpa auth) =====
Route::get('/request', [PublicTicketController::class, 'showForm'])->name('tickets.public.form');
Route::post('/request', [PublicTicketController::class, 'store'])
    ->middleware('throttle:5,10')
    ->name('tickets.public.store');
Route::get('/request/success', [PublicTicketController::class, 'success'])->name('tickets.public.success');
Route::get('/request/employee', [PublicTicketController::class, 'lookupEmployee'])
    ->middleware('throttle:20,1')
    ->name('tickets.public.employee');
Route::get('/request/status', [PublicTicketController::class, 'trackForm'])->name('tickets.public.track');
Route::post('/request/status', [PublicTicketController::class, 'track'])
    ->middleware('throttle:10,1')
    ->name('tickets.public.track.lookup');
Route::get('/request/status/{ticket:ticket_number}', [PublicTicketController::class, 'showTracked'])->name('tickets.public.track.show');
Route::post('/request/status/{ticket:ticket_number}/comment', [PublicTicketController::class, 'comment'])
    ->middleware('throttle:10,1')
    ->name('tickets.public.comment');
Route::post('/request/status/{ticket:ticket_number}/respond', [PublicTicketController::class, 'respond'])
    ->middleware('throttle:10,1')
    ->name('tickets.public.respond');

// Redirect root to dashboard or login page
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');
Route::post('/dashboard/toggle-sidebar', [DashboardController::class, 'toggleSidebar'])->middleware(['auth'])->name('dashboard.toggle-sidebar');

Route::middleware('auth')->group(function () {
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
    Route::get('/daily-reports/whatsapp-share', [DailyReportController::class, 'whatsappShare'])->name('daily-reports.whatsapp-share');
    Route::get('/daily-reports/export', [DailyReportController::class, 'export'])->name('daily-reports.export');
    Route::get('/daily-reports/import', [DailyReportController::class, 'showImport'])->name('daily-reports.show-import');
    Route::post('/daily-reports/import', [DailyReportController::class, 'import'])->name('daily-reports.import');
    Route::get('/daily-reports/export-template', [DailyReportController::class, 'exportTemplate'])->name('daily-reports.export-template');
    
    Route::resource('daily-reports', DailyReportController::class);
    Route::post('daily-reports/store-multiple', [DailyReportController::class, 'storeMultiple'])
        ->middleware('web')
        ->name('daily-reports.store-multiple');
    
    // Job Plans Routes (custom routes BEFORE resource to avoid wildcard collision)
    Route::get('/job-plans/whatsapp-share', [JobPlanController::class, 'whatsappShare'])->name('job-plans.whatsapp-share');
    Route::get('/job-plans/assigned-to-me', [JobPlanController::class, 'assignedToMe'])->name('job-plans.assigned-to-me');
    Route::get('/job-plans/{jobPlan}/convert', [JobPlanController::class, 'convert'])->name('job-plans.convert');
    Route::resource('job-plans', JobPlanController::class);

    // Job Comments Routes
    Route::get('/daily-reports/{reportId}/comments', [\App\Http\Controllers\JobCommentController::class, 'getComments'])->name('job-comments.index');
    Route::post('/daily-reports/{reportId}/comments', [\App\Http\Controllers\JobCommentController::class, 'store'])->name('job-comments.store');
    Route::delete('/comments/{commentId}', [\App\Http\Controllers\JobCommentController::class, 'destroy'])->name('job-comments.destroy');

    // Ticket Routes (internal dashboard)
    Route::get('tickets/mine', [TicketController::class, 'mine'])->name('tickets.mine');
    Route::resource('tickets', TicketController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.update-status');
    Route::post('tickets/{ticket}/comment', [TicketController::class, 'addComment'])->name('tickets.comment');
    Route::post('tickets/{ticket}/respond', [TicketController::class, 'respond'])->name('tickets.respond');
    Route::post('tickets/{ticket}/priority', [TicketController::class, 'changePriority'])->name('tickets.priority');
    Route::get('tickets/{ticket}/attachment', [AttachmentController::class, 'ticket'])->name('tickets.attachment');
    Route::delete('tickets-batch', [TicketController::class, 'batchDelete'])->name('tickets.batch-delete');

    // Sections by department (for dropdown in daily report form)
    Route::get('/sections/by-department', [\App\Http\Controllers\Admin\SectionController::class, 'getByDepartment'])->name('sections.by-department');

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

        // Job Site management
        Route::delete('/job-sites/batch-delete', [\App\Http\Controllers\Admin\JobSiteController::class, 'batchDelete'])->name('job-sites.batch-delete');
        Route::resource('job-sites', \App\Http\Controllers\Admin\JobSiteController::class);

        // Pengaturan SLA tiket & hari libur
        Route::get('/sla', [\App\Http\Controllers\Admin\SlaController::class, 'index'])->name('sla.index');
        Route::put('/sla', [\App\Http\Controllers\Admin\SlaController::class, 'update'])->name('sla.update');
        Route::post('/sla/holidays', [\App\Http\Controllers\Admin\SlaController::class, 'storeHoliday'])->name('sla.holidays.store');
        Route::delete('/sla/holidays/{holiday}', [\App\Http\Controllers\Admin\SlaController::class, 'destroyHoliday'])->name('sla.holidays.destroy');

        // Master karyawan (verifikasi NIK pemohon tiket)
        Route::get('/employees/template', [\App\Http\Controllers\Admin\EmployeeController::class, 'exportTemplate'])->name('employees.template');
        Route::post('/employees/import', [\App\Http\Controllers\Admin\EmployeeController::class, 'import'])->name('employees.import');
        Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class)->except(['show']);

        // Section management
        Route::get('/sections/by-department', [\App\Http\Controllers\Admin\SectionController::class, 'getByDepartment'])->name('sections.by-department');
        Route::delete('/sections/batch-delete', [\App\Http\Controllers\Admin\SectionController::class, 'batchDelete'])->name('sections.batch-delete');
        Route::resource('sections', \App\Http\Controllers\Admin\SectionController::class);

        // Admin settings
        Route::get('/settings', function() {
            return view('admin.settings');
        })->name('settings');
        
        // Report cleanup management
        Route::get('/reports/cleanup', [\App\Http\Controllers\Admin\ReportCleanupController::class, 'index'])->name('reports.cleanup.index');
        Route::post('/reports/cleanup/preview', [\App\Http\Controllers\Admin\ReportCleanupController::class, 'preview'])->name('reports.cleanup.preview');
        Route::post('/reports/cleanup/execute', [\App\Http\Controllers\Admin\ReportCleanupController::class, 'execute'])->name('reports.cleanup.execute');
    });
});

// Attachments are stored on a private disk and only served after a policy check
Route::get('/storage/attachments/{filename}', [AttachmentController::class, 'report'])
    ->middleware('auth')
    ->name('attachments.show');

require __DIR__.'/auth.php';

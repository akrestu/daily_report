<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Exports\DailyReportsExport;
use App\Exports\DailyReportsTemplateExport;
use App\Imports\DailyReportsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Department;
use App\Models\JobComment;
use App\Models\Notification;

class DailyReportController extends Controller
{
    /**
     * Common validation rules for daily reports
     * 
     * @return array
     */
    private function getValidationRules()
    {
        $attachmentRules = 'nullable|file|max:5120|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx|mimetypes:image/jpeg,image/png,image/gif,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return [
            'job_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'report_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:report_date',
            'job_pic' => 'required|exists:users,id',
            'description' => 'required|string|max:2000',
            'remark' => 'nullable|string|max:1000',
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'attachment' => $attachmentRules,
            'attachment_2' => $attachmentRules,
            'attachment_3' => $attachmentRules,
        ];
    }

    /**
     * Get eligible PICs for reports based on user's role and department
     *
     * @param \App\Models\User $user The user creating/editing the report
     * @param int|null $currentPicId Include current PIC for existing reports
     * @return array Array of eligible user IDs
     */
    private function getEligiblePics(User $user, $currentPicId = null)
    {
        // Get eligible role slugs based on user's role
        $eligibleRoleSlugs = $user->getEligiblePicRoles();

        if (empty($eligibleRoleSlugs)) {
            return $currentPicId ? [$currentPicId] : [];
        }

        // Get role IDs for eligible slugs
        $eligibleRoleIds = Role::whereIn('slug', $eligibleRoleSlugs)->pluck('id')->toArray();

        // Get users with eligible roles from the SAME DEPARTMENT, excluding self (no self-PIC)
        $query = User::whereIn('role_id', $eligibleRoleIds)
            ->where('id', '!=', $user->id) // Exclude self
            ->where('department_id', $user->department_id) // Filter by same department
            ->pluck('id')
            ->toArray();

        // Include the current PIC for existing reports if not already in the list
        if ($currentPicId && !in_array($currentPicId, $query)) {
            $query[] = $currentPicId;
        }

        return $query;
    }

    /**
     * Process and store file attachment
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string|null $oldAttachmentPath Path to old attachment to delete
     * @return array [attachmentPath, originalName]
     */
    private function processAttachment($file, $oldAttachmentPath = null)
    {
        // Validate MIME type for security
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/svg+xml',
            'application/pdf', 'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('File type not allowed');
        }
        
        // Sanitize original filename and prevent path traversal
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_BASENAME);
        $originalName = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $originalName);
        
        $extension = $file->getClientOriginalExtension();
        $filename = uniqid('attachment_', true) . '_' . time() . '.' . $extension;
        $path = 'attachments/' . $filename;
        
        // Create attachments directory if it doesn't exist
        if (!Storage::disk('public')->exists('attachments')) {
            Storage::disk('public')->makeDirectory('attachments');
        }
        
        // Delete old file if exists
        if ($oldAttachmentPath) {
            Storage::disk('public')->delete($oldAttachmentPath);
        }
        
        // Check if it's an image and process if GD extension is available
        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
            try {
                // Check if GD extension is available
                if (!extension_loaded('gd')) {
                    throw new \Exception('GD extension not available');
                }
                
                // Create image manager with GD driver
                $manager = new ImageManager(Driver::class);
                
                // Load and compress image
                $image = $manager->read($file);
                
                // Resize if width or height is greater than 1920px while maintaining aspect ratio
                $image->scaleDown(1920, 1920);
                
                // Save compressed image to public disk with higher compression (60% quality instead of 80%)
                Storage::disk('public')->put($path, $image->toJpeg(60));
            } catch (\Exception $e) {
                // Fallback: Store image without processing if GD is not available
                Log::warning('Image processing failed, storing without compression: ' . $e->getMessage());
                Storage::disk('public')->putFileAs('attachments', $file, $filename);
            }
        } else {
            // For non-image files, store as is in public disk under attachments directory
            Storage::disk('public')->putFileAs('attachments', $file, $filename);
        }
        
        return [
            'path' => $path,
            'originalName' => $originalName
        ];
    }
    
    /**
     * Check if user can view/edit/delete the report
     *
     * @param \App\Models\User $user
     * @param \App\Models\DailyReport $report
     * @param string $action view|edit|delete
     * @return bool
     */
    private function userCanAccessReport($user, $report, $action = 'view')
    {
        // Admin can access any report
        if ($user->isAdmin()) {
            return true;
        }

        // Level 5 can view all reports (monitoring role)
        if ($user->isLevel5() && $action === 'view') {
            return true;
        }

        switch ($action) {
            case 'view':
                // User can view if they are the creator
                if ($user->id === $report->user_id) {
                    return true;
                }

                // PIC can view reports assigned to them
                if ($user->id === $report->job_pic) {
                    return true;
                }

                // Users can view reports from their department
                if ($user->department_id === $report->department_id) {
                    return true;
                }

                // Legacy role support
                if ($user->isDepartmentHead() || $user->isLeader()) {
                    return true;
                }

                return false;

            case 'edit':
                // Creator can edit their own reports if not yet approved
                if ($user->id === $report->user_id && $report->approval_status === 'pending') {
                    return true;
                }

                return false;

            case 'delete':
                // Creator can delete their own pending reports
                if ($user->id === $report->user_id && $report->approval_status === 'pending') {
                    return true;
                }

                return false;

            default:
                return false;
        }
    }

    public function index()
    {
        // Fetch departments for dropdown
        $departments = \App\Models\Department::pluck('name', 'id');
        
        // Get report type (approved or rejected)
        $reportType = request('type', 'approved');
        
        // Build the query
        $query = DailyReport::with(['user', 'department', 'pic', 'approver'])
            ->whereNotNull('approved_by');

        // Filter by report type
        if ($reportType === 'approved') {
            $query->where('approval_status', 'approved');
        } elseif ($reportType === 'rejected') {
            $query->where('approval_status', 'rejected');
        }

        // Filter by user's department unless they're an admin
        // Show only reports from users in the same department
        $user = Auth::user();
        if ($user && $user->role_id) {
            // Check if user is not an admin and has a department
            $adminRole = Role::where('slug', 'admin')->first();
            if ($user->role_id !== $adminRole->id && $user->department_id) {
                // Join with users table to filter by user's department
                $query->whereHas('user', function($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            }
        }
        
        // Apply filters if present
        if (request()->has('search') && !empty(request('search'))) {
            $search = request('search');
            $query->where('job_name', 'like', "%{$search}%");
        }
        
        if (request()->has('department') && !empty(request('department'))) {
            $query->where('department_id', request('department'));
        }

        // Filter by date range
        if (request()->has('date_from') && !empty(request('date_from'))) {
            $query->whereDate('report_date', '>=', request('date_from'));
        }
        
        if (request()->has('date_to') && !empty(request('date_to'))) {
            $query->whereDate('report_date', '<=', request('date_to'));
        }
        
        // Get the paginated results
        $reports = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        
        return view('daily-reports.index', compact('reports', 'departments', 'reportType'));
    }

    public function create()
    {
        // Fetch departments for dropdown
        $departments = \App\Models\Department::pluck('name', 'id');

        // For PIC dropdown, get eligible users based on current user's role
        /** @var User $user */
        $user = Auth::user();

        // Get eligible PIC IDs
        $eligiblePicIds = $this->getEligiblePics($user);

        // Get eligible PICs with their names
        $eligiblePics = User::whereIn('id', $eligiblePicIds)->pluck('name', 'id')->toArray();

        return view('daily-reports.create', compact('departments', 'eligiblePics'));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Validate request data
        $validated = $request->validate($this->getValidationRules());

        // Get eligible PICs
        $eligiblePics = $this->getEligiblePics($user);

        // Validate PIC belongs to eligible PICs
        if (!in_array($validated['job_pic'], $eligiblePics)) {
            return redirect()->back()
                ->with('error', 'The selected PIC is not valid based on your role level.')
                ->withInput();
        }

        // Validate no self-PIC
        if ($validated['job_pic'] == $user->id) {
            return redirect()->back()
                ->with('error', 'You cannot assign yourself as PIC.')
                ->withInput();
        }
        
        $attachmentPath = null;
        $originalName = null;
        $attachmentPath2 = null;
        $originalName2 = null;
        $attachmentPath3 = null;
        $originalName3 = null;

        // Process the first attachment if provided
        if ($request->hasFile('attachment')) {
            $attachmentData = $this->processAttachment($request->file('attachment'));
            $attachmentPath = $attachmentData['path'];
            $originalName = $attachmentData['originalName'];
        }

        // Process the second attachment if provided
        if ($request->hasFile('attachment_2')) {
            $attachmentData2 = $this->processAttachment($request->file('attachment_2'));
            $attachmentPath2 = $attachmentData2['path'];
            $originalName2 = $attachmentData2['originalName'];
        }

        // Process the third attachment if provided
        if ($request->hasFile('attachment_3')) {
            $attachmentData3 = $this->processAttachment($request->file('attachment_3'));
            $attachmentPath3 = $attachmentData3['path'];
            $originalName3 = $attachmentData3['originalName'];
        }

        // Use transaction to ensure data integrity
        DB::beginTransaction();

        try {
            // Create the report
            $report = DailyReport::create([
                'user_id' => $user->id,
                'job_name' => $validated['job_name'],
                'department_id' => $validated['department_id'],
                'job_pic' => $validated['job_pic'],
                'report_date' => $validated['report_date'],
                'due_date' => $validated['due_date'],
                'description' => $validated['description'],
                'remark' => $validated['remark'] ?? null,
                'status' => $validated['status'],
                'attachment_path' => $attachmentPath,
                'attachment_original_name' => $originalName,
                'attachment_path_2' => $attachmentPath2,
                'attachment_original_name_2' => $originalName2,
                'attachment_path_3' => $attachmentPath3,
                'attachment_original_name_3' => $originalName3,
            ]);
            
            DB::commit();
            
            return redirect()->route('daily-reports.user-jobs')
                ->with('success', 'Daily report created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create daily report: ' . $e->getMessage());

            // Delete uploaded files if they exist
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }
            if ($attachmentPath2) {
                Storage::disk('public')->delete($attachmentPath2);
            }
            if ($attachmentPath3) {
                Storage::disk('public')->delete($attachmentPath3);
            }

            return redirect()->back()
                ->with('error', 'Failed to create report. Please try again.')
                ->withInput();
        }
    }

    /**
     * Store multiple daily reports at once
     */
    public function storeMultiple(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Prepare validation rules for multiple reports
        $rules = $this->getValidationRules();
        $multipleRules = [];
        
        foreach ($rules as $field => $rule) {
            $multipleRules["reports.*.$field"] = $rule;
        }
        
        // Validate the overall request structure
        $request->validate(array_merge(
            ['reports' => 'required|array|min:1'],
            $multipleRules
        ));
        
        // Use a transaction to ensure all reports are saved or none
        DB::beginTransaction();
        
        try {
            $reports = $request->reports;
            $createdCount = 0;
            $createdAttachments = [];
            
            foreach ($reports as $index => $reportData) {
                $attachmentPath = null;
                $originalName = null;
                $attachmentPath2 = null;
                $originalName2 = null;
                $attachmentPath3 = null;
                $originalName3 = null;

                Log::info('Processing report data for index: ' . $index, [
                    'has_attachment' => isset($reportData['attachment']),
                    'has_attachment_2' => isset($reportData['attachment_2']),
                    'has_attachment_3' => isset($reportData['attachment_3'])
                ]);

                // Process the first attachment if provided
                if (isset($reportData['attachment']) && $reportData['attachment'] instanceof \Illuminate\Http\UploadedFile) {
                    $file = $reportData['attachment'];

                    // Check if the file is valid
                    if ($file->isValid()) {
                        try {
                            $attachmentData = $this->processAttachment($file);
                            $attachmentPath = $attachmentData['path'];
                            $originalName = $attachmentData['originalName'];
                            $createdAttachments[] = $attachmentPath;

                            Log::info('Processed attachment 1', [
                                'index' => $index,
                                'path' => $attachmentPath,
                                'original_name' => $originalName
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to process attachment 1 for report index ' . $index . ': ' . $e->getMessage());
                            throw $e;
                        }
                    } else {
                        Log::warning('Invalid file for attachment 1 report index ' . $index);
                    }
                }

                // Process the second attachment if provided
                if (isset($reportData['attachment_2']) && $reportData['attachment_2'] instanceof \Illuminate\Http\UploadedFile) {
                    $file = $reportData['attachment_2'];

                    if ($file->isValid()) {
                        try {
                            $attachmentData2 = $this->processAttachment($file);
                            $attachmentPath2 = $attachmentData2['path'];
                            $originalName2 = $attachmentData2['originalName'];
                            $createdAttachments[] = $attachmentPath2;

                            Log::info('Processed attachment 2', [
                                'index' => $index,
                                'path' => $attachmentPath2,
                                'original_name' => $originalName2
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to process attachment 2 for report index ' . $index . ': ' . $e->getMessage());
                            throw $e;
                        }
                    } else {
                        Log::warning('Invalid file for attachment 2 report index ' . $index);
                    }
                }

                // Process the third attachment if provided
                if (isset($reportData['attachment_3']) && $reportData['attachment_3'] instanceof \Illuminate\Http\UploadedFile) {
                    $file = $reportData['attachment_3'];

                    if ($file->isValid()) {
                        try {
                            $attachmentData3 = $this->processAttachment($file);
                            $attachmentPath3 = $attachmentData3['path'];
                            $originalName3 = $attachmentData3['originalName'];
                            $createdAttachments[] = $attachmentPath3;

                            Log::info('Processed attachment 3', [
                                'index' => $index,
                                'path' => $attachmentPath3,
                                'original_name' => $originalName3
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to process attachment 3 for report index ' . $index . ': ' . $e->getMessage());
                            throw $e;
                        }
                    } else {
                        Log::warning('Invalid file for attachment 3 report index ' . $index);
                    }
                }

                // Create the report
                DailyReport::create([
                    'user_id' => $user->id,
                    'job_name' => $reportData['job_name'],
                    'department_id' => $reportData['department_id'],
                    'job_pic' => $reportData['job_pic'],
                    'report_date' => $reportData['report_date'],
                    'due_date' => $reportData['due_date'],
                    'description' => $reportData['description'],
                    'remark' => $reportData['remark'] ?? null,
                    'status' => $reportData['status'],
                    'attachment_path' => $attachmentPath,
                    'attachment_original_name' => $originalName,
                    'attachment_path_2' => $attachmentPath2,
                    'attachment_original_name_2' => $originalName2,
                    'attachment_path_3' => $attachmentPath3,
                    'attachment_original_name_3' => $originalName3,
                ]);
                
                $createdCount++;
            }
            
            DB::commit();
            
            return redirect()->route('daily-reports.user-jobs')
                ->with('success', $createdCount . ' daily reports created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create multiple daily reports: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Delete all created attachments
            foreach ($createdAttachments as $path) {
                Storage::disk('public')->delete($path);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to create reports: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(DailyReport $dailyReport)
    {
        // Check if user can access this report
        if (!$this->userCanAccessReport(Auth::user(), $dailyReport)) {
            return redirect()->route('daily-reports.index')
                ->with('error', 'You do not have permission to view this report.');
        }
        
        $report = $dailyReport; // Assign to $report variable to match view expectation
        return view('daily-reports.show', compact('report'));
    }

    public function edit(DailyReport $dailyReport)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if user can edit this report
        if (!$this->userCanAccessReport($user, $dailyReport, 'edit')) {
            return redirect()->route('daily-reports.user-jobs')
                ->with('error', 'You do not have permission to edit this report.');
        }

        // Fetch departments for dropdown
        $departments = \App\Models\Department::pluck('name', 'id');

        // Get eligible PIC IDs including current PIC
        $eligiblePicIds = $this->getEligiblePics($user, $dailyReport->job_pic);

        // Get eligible PICs with their names
        $eligiblePics = User::whereIn('id', $eligiblePicIds)->pluck('name', 'id')->toArray();

        // Ensure current PIC is in the list (for display purposes)
        if (!array_key_exists($dailyReport->job_pic, $eligiblePics)) {
            $picUser = User::find($dailyReport->job_pic);
            if ($picUser) {
                $eligiblePics[$picUser->id] = $picUser->name;
            }
        }

        $report = $dailyReport; // Assign to $report variable to match view expectation
        return view('daily-reports.edit', compact('report', 'departments', 'eligiblePics'));
    }

    public function update(Request $request, DailyReport $dailyReport)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if user can edit the report
        if (!$this->userCanAccessReport($user, $dailyReport, 'edit')) {
            if ($dailyReport->approved_by) {
                return redirect()->route('daily-reports.show', $dailyReport)
                    ->with('error', 'You cannot edit a report that has already been approved or rejected.');
            }

            abort(403, 'Unauthorized action.');
        }

        // Validate input data
        $validated = $request->validate($this->getValidationRules());

        // Get eligible PICs
        $eligiblePics = $this->getEligiblePics($user, $dailyReport->job_pic);

        // Check if PIC is in eligible list
        if (!in_array($validated['job_pic'], $eligiblePics)) {
            return redirect()->back()
                ->with('error', 'The selected PIC is not valid based on your role level.')
                ->withInput();
        }

        // Validate no self-PIC
        if ($validated['job_pic'] == $user->id) {
            return redirect()->back()
                ->with('error', 'You cannot assign yourself as PIC.')
                ->withInput();
        }
        
        // Use transaction for data integrity
        DB::beginTransaction();

        try {
            // Handle first attachment if present
            if ($request->hasFile('attachment')) {
                $attachmentData = $this->processAttachment(
                    $request->file('attachment'),
                    $dailyReport->attachment_path
                );

                $validated['attachment_path'] = $attachmentData['path'];
                $validated['attachment_original_name'] = $attachmentData['originalName'];
            }

            // Handle second attachment if present
            if ($request->hasFile('attachment_2')) {
                $attachmentData2 = $this->processAttachment(
                    $request->file('attachment_2'),
                    $dailyReport->attachment_path_2
                );

                $validated['attachment_path_2'] = $attachmentData2['path'];
                $validated['attachment_original_name_2'] = $attachmentData2['originalName'];
            }

            // Handle third attachment if present
            if ($request->hasFile('attachment_3')) {
                $attachmentData3 = $this->processAttachment(
                    $request->file('attachment_3'),
                    $dailyReport->attachment_path_3
                );

                $validated['attachment_path_3'] = $attachmentData3['path'];
                $validated['attachment_original_name_3'] = $attachmentData3['originalName'];
            }

            $dailyReport->update($validated);
            
            DB::commit();
            
            return redirect()->route('daily-reports.user-jobs')
                ->with('success', 'Daily report updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update daily report: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update report. Please try again.')
                ->withInput();
        }
    }

    public function destroy(DailyReport $dailyReport)
    {
        $user = Auth::user();
        
        // Check if user can delete the report
        if (!$this->userCanAccessReport($user, $dailyReport, 'delete')) {
            if ($dailyReport->approval_status !== 'pending') {
                return redirect()->route('daily-reports.show', $dailyReport)
                    ->with('error', 'You cannot delete a report that has already been processed.');
            }
            
            abort(403, 'Unauthorized action.');
        }
        
        // Use transaction for data integrity
        DB::beginTransaction();
        
        try {
            // Delete all attachments if they exist
            if ($dailyReport->attachment_path) {
                Storage::disk('public')->delete($dailyReport->attachment_path);
            }
            if ($dailyReport->attachment_path_2) {
                Storage::disk('public')->delete($dailyReport->attachment_path_2);
            }
            if ($dailyReport->attachment_path_3) {
                Storage::disk('public')->delete($dailyReport->attachment_path_3);
            }

            $dailyReport->delete();
            
            DB::commit();
            
            return redirect()->route('daily-reports.user-jobs')
                ->with('success', 'Daily report deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete daily report: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to delete report. Please try again.');
        }
    }

    public function approval(Request $request, DailyReport $dailyReport)
    {
        /** @var User $user */
        $user = Auth::user();
        $reportOwner = User::find($dailyReport->user_id);

        // Check if user can approve/reject reports using the canApprove method
        if (!$reportOwner || !$user->canApprove($reportOwner)) {
            abort(403, 'Unauthorized action. You cannot approve reports for this user.');
        }
        
        // Allow changing approval status even for already approved/rejected reports for Admin and Department Head
        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'rejection_reason' => 'required_if:status,rejected',
            'redirect_back' => 'nullable',
        ]);
        
        // Use transaction for data integrity
        DB::beginTransaction();
        
        try {
            // Update approval_status instead of status to keep job status separate
            $dailyReport->approval_status = $validated['status'];
            
            if ($validated['status'] === 'rejected') {
                $dailyReport->rejection_reason = $validated['rejection_reason'];
            } else {
                $dailyReport->rejection_reason = null;
            }
            
            $dailyReport->approved_by = Auth::id();
            $dailyReport->save();
            
            // Create notification for the report owner
            if ($reportOwner) {
                $notificationType = $validated['status'] === 'approved' ? 'job_approved' : 'job_rejected';
                $message = $validated['status'] === 'approved' 
                    ? "Your job report '{$dailyReport->job_name}' has been approved by {$user->name}."
                    : "Your job report '{$dailyReport->job_name}' has been rejected by {$user->name}.";
                
                if ($validated['status'] === 'rejected' && isset($validated['rejection_reason'])) {
                    $message .= " Reason: {$validated['rejection_reason']}";
                }
                
                Notification::create([
                    'user_id' => $reportOwner->id,
                    'daily_report_id' => $dailyReport->id,
                    'type' => $notificationType,
                    'message' => $message,
                    'is_read' => false,
                ]);
            }
            
            DB::commit();
            
            $successMessage = 'Daily report ' . $validated['status'] . ' successfully.';
            
            // Check if we should redirect back to the previous page
            if (isset($validated['redirect_back']) && $validated['redirect_back']) {
                // Get the previous URL from the session or referer header
                $previousUrl = url()->previous();
                
                // If the previous URL is the show page, go to the listing page instead
                if (strpos($previousUrl, 'daily-reports/' . $dailyReport->id) !== false) {
                    if (strpos($previousUrl, 'assigned-jobs') !== false) {
                        return redirect()->route('daily-reports.assigned-jobs')
                            ->with('success', $successMessage);
                    } else {
                        return redirect()->route('daily-reports.user-jobs')
                            ->with('success', $successMessage);
                    }
                }
                
                return redirect($previousUrl)->with('success', $successMessage);
            }

            return redirect()->route('daily-reports.show', $dailyReport)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update report approval status: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update approval status. Please try again.');
        }
    }

    public function pending()
    {
        /** @var User $user */
        $user = Auth::user();
        $view = request()->get('view', 'approval');

        // Base query - all reports that need approval (not approved or rejected yet)
        $reportsQuery = DailyReport::where('approval_status', 'pending')
            ->with(['user', 'approver', 'department', 'pic'])
            ->whereNull('approved_by');

        // For monitoring view - Level 5 and Admin can monitor all pending reports
        if ($view === 'monitoring' && ($user->isLevel5() || $user->isAdmin())) {
            $monitoringReports = DailyReport::where('approval_status', 'pending')
                ->whereNull('approved_by')
                ->with(['user', 'approver', 'department', 'pic'])
                ->latest()
                ->paginate(10);

            return view('daily-reports.pending', compact('monitoringReports'));
        }

        // For approval view (default)
        // Admin can see all pending reports that need approval
        if ($user->isAdmin()) {
            $reports = $reportsQuery->latest()->paginate(10);
        }
        // Level 2-5 can see reports where they are PIC
        else if ($user->getRoleLevel() >= 2) {
            $reports = $reportsQuery
                ->where('job_pic', $user->id)
                ->latest()
                ->paginate(10);
        }
        // Level 1 users cannot approve, but can see reports where they are somehow PIC (edge case)
        else {
            $reports = $reportsQuery
                ->where('job_pic', $user->id)
                ->latest()
                ->paginate(10);
        }

        return view('daily-reports.pending', compact('reports'));
    }
    
    /**
     * Display a list of jobs created by the current user
     */
    public function userJobs()
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Build query with relations
        $query = DailyReport::where('user_id', $user->id)
            ->with(['user', 'approver', 'department', 'pic']);
            
        // Apply filters if present
        if (request()->has('search') && !empty(request('search'))) {
            $search = request('search');
            $query->where('job_name', 'like', "%{$search}%");
        }
        
        if (request()->has('status') && !empty(request('status'))) {
            $query->where('status', request('status'));
        }

        // Filter by date range
        if (request()->has('date_from') && !empty(request('date_from'))) {
            $query->whereDate('report_date', '>=', request('date_from'));
        }
        
        if (request()->has('date_to') && !empty(request('date_to'))) {
            $query->whereDate('report_date', '<=', request('date_to'));
        }
        
        // Get paginated results
        $reports = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        
        return view('daily-reports.user-jobs', compact('reports'));
    }

    /**
     * Display a list of jobs assigned to the current user as PIC
     */
    public function assignedJobs()
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Build query with relations
        $query = DailyReport::where('job_pic', $user->id)
            ->with(['user', 'approver', 'department', 'pic']);
            
        // Apply filters if present
        if (request()->has('search') && !empty(request('search'))) {
            $search = request('search');
            $query->where('job_name', 'like', "%{$search}%");
        }
        
        if (request()->has('status') && !empty(request('status'))) {
            $query->where('status', request('status'));
        }

        // Filter by date range
        if (request()->has('date_from') && !empty(request('date_from'))) {
            $query->whereDate('report_date', '>=', request('date_from'));
        }
        
        if (request()->has('date_to') && !empty(request('date_to'))) {
            $query->whereDate('report_date', '<=', request('date_to'));
        }
        
        // Get paginated results
        $reports = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        
        return view('daily-reports.assigned-jobs', compact('reports'));
    }

    public function batchApprove(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Only users with approval authority can batch approve
        if (!$user->isAdmin() && $user->getRoleLevel() < 2) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        try {
            // Extract IDs from request (handles both JSON and form data)
            $ids = $request->input('selected_reports');
            
            // Check if input is a JSON string (sometimes happens with non-AJAX form submissions)
            if (is_string($ids) && $this->isJson($ids)) {
                $ids = json_decode($ids, true);
            }
            
            // Validate the ids
            if (empty($ids) || !is_array($ids)) {
                return redirect()->back()->with('error', 'No reports selected for approval.');
            }

            // Build query for reports to approve
            $query = DailyReport::with('user')
                ->whereIn('id', $ids)
                ->where('approval_status', '!=', 'approved'); // Allow approving any non-approved reports

            $reports = $query->get();

            if ($reports->isEmpty()) {
                return redirect()->back()->with('error', 'No reports selected for approval.');
            }

            // Filter reports that user can approve
            $approvableReports = $reports->filter(function ($report) use ($user) {
                return $report->user && $user->canApprove($report->user);
            });

            if ($approvableReports->isEmpty()) {
                return redirect()->back()->with('error', 'No valid reports found to approve. You may not have permission to approve the selected reports.');
            }

            DB::beginTransaction();
            try {
                // Use bulk update for better performance
                $reportIds = $approvableReports->pluck('id')->toArray();
                DailyReport::whereIn('id', $reportIds)
                    ->update([
                        'approval_status' => 'approved',
                        'approved_by' => $user->id,
                        'rejection_reason' => null // Clear any rejection reason
                    ]);

                // Create notifications for each report owner
                foreach ($approvableReports as $report) {
                    if ($report->user_id) {
                        Notification::create([
                            'user_id' => $report->user_id,
                            'daily_report_id' => $report->id,
                            'type' => 'job_approved',
                            'message' => "Your job report '{$report->job_name}' has been approved by {$user->name}.",
                            'is_read' => false,
                        ]);
                    }
                }

                DB::commit();

                return redirect()->back()->with('success', count($approvableReports) . ' reports have been approved successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error during batch approval transaction', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return redirect()->back()->with('error', 'An error occurred while approving the reports: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Error in batch approval', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error processing request: ' . $e->getMessage());
        }
    }

    /**
     * Batch reject multiple reports
     */
    public function batchReject(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Only users with approval authority can batch reject
        if (!$user->isAdmin() && $user->getRoleLevel() < 2) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        try {
            // Validate the request
            $validated = $request->validate([
                'selected_reports' => 'required|array',
                'selected_reports.*' => 'exists:daily_reports,id',
                'rejection_reason' => 'required|string|max:500',
            ]);

            $ids = $validated['selected_reports'];
            $rejectionReason = $validated['rejection_reason'];

            // Build query for reports to reject
            $query = DailyReport::with('user')
                ->whereIn('id', $ids)
                ->where('approval_status', '!=', 'rejected'); // Allow rejecting any non-rejected reports

            $reports = $query->get();

            if ($reports->isEmpty()) {
                return redirect()->back()->with('error', 'No reports selected for rejection.');
            }

            // Filter reports that user can reject (same as approve permission)
            $rejectableReports = $reports->filter(function ($report) use ($user) {
                return $report->user && $user->canApprove($report->user);
            });

            if ($rejectableReports->isEmpty()) {
                return redirect()->back()->with('error', 'No valid reports found to reject. You may not have permission to reject the selected reports.');
            }

            DB::beginTransaction();
            try {
                // Use bulk update for better performance
                $reportIds = $rejectableReports->pluck('id')->toArray();
                DailyReport::whereIn('id', $reportIds)
                    ->update([
                        'approval_status' => 'rejected',
                        'approved_by' => $user->id,
                        'rejection_reason' => $rejectionReason
                    ]);

                // Create notifications for each report owner
                foreach ($rejectableReports as $report) {
                    if ($report->user_id) {
                        Notification::create([
                            'user_id' => $report->user_id,
                            'daily_report_id' => $report->id,
                            'type' => 'job_rejected',
                            'message' => "Your job report '{$report->job_name}' has been rejected by {$user->name}. Reason: {$rejectionReason}",
                            'is_read' => false,
                        ]);
                    }
                }

                DB::commit();

                return redirect()->back()->with('success', count($rejectableReports) . ' reports have been rejected successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error during batch rejection transaction', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return redirect()->back()->with('error', 'An error occurred while rejecting the reports: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Error in batch rejection', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error processing request: ' . $e->getMessage());
        }
    }

    public function batchDelete(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Only Admin and Level 5 can batch delete
        if (!$user->isAdmin() && !$user->isLevel5()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        try {
            // Extract IDs from request (handles both JSON and form data)
            $ids = $request->input('selected_reports');
            
            // Check if input is a JSON string (sometimes happens with non-AJAX form submissions)
            if (is_string($ids) && $this->isJson($ids)) {
                $ids = json_decode($ids, true);
            }
            
            // Validate the ids
            if (empty($ids) || !is_array($ids)) {
                return redirect()->back()->with('error', 'No reports selected for deletion.');
            }

            // Get reports with attachments to delete
            $query = DailyReport::whereIn('id', $ids);

            // Department heads and leaders can only delete reports from their department
            if (!$user->isAdmin()) {
                $query->where('department_id', $user->department_id);
            }

            $reports = $query->get();

            // Collect all attachment paths
            $attachmentPaths = [];
            foreach ($reports as $report) {
                if ($report->attachment_path) {
                    $attachmentPaths[] = $report->attachment_path;
                }
                if ($report->attachment_path_2) {
                    $attachmentPaths[] = $report->attachment_path_2;
                }
                if ($report->attachment_path_3) {
                    $attachmentPaths[] = $report->attachment_path_3;
                }
            }
                
            if ($reports->isEmpty()) {
                return redirect()->back()->with('error', 'No valid reports found to delete.');
            }

            DB::beginTransaction();
            try {
                // Delete reports
                DailyReport::whereIn('id', $reports->pluck('id')->toArray())->delete();
                
                // Delete attachments
                foreach ($attachmentPaths as $path) {
                    Storage::disk('public')->delete($path);
                }
                
                DB::commit();

                return redirect()->back()->with('success', count($reports) . ' reports have been deleted successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error during batch deletion transaction', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return redirect()->back()->with('error', 'An error occurred while deleting the reports: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Error in batch deletion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error processing request: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if a string is valid JSON
     * 
     * @param string $string
     * @return bool
     */
    private function isJson($string) {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Export daily reports to Excel
     */
    public function export(Request $request)
    {
        // Get filters from request
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to')
        ];
        
        // Determine the source page for the export (my reports vs assigned reports)
        $referer = request()->headers->get('referer');
        $filename = 'my_reports_' . date('Y-m-d') . '.xlsx';
        
        if (strpos($referer, 'assigned-jobs') !== false) {
            $filters['view'] = 'assigned';
            $filename = 'assigned_reports_' . date('Y-m-d') . '.xlsx';
        } else {
            $filters['view'] = 'my';
        }

        return Excel::download(new DailyReportsExport($filters), $filename);
    }

    /**
     * Export all reports based on filters and user permissions
     */
    public function exportAll(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Get filters from request
        $filters = [
            'search' => $request->input('search'),
            'department' => $request->input('department'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'type' => $request->input('type', 'approved'),
        ];
        
        return Excel::download(new DailyReportsExport($filters, true), 'all_reports_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Show import form
     */
    public function showImport()
    {
        return view('daily-reports.import');
    }

    /**
     * Import daily reports from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new DailyReportsImport, $request->file('file'));
            
            return redirect()->route('daily-reports.user-jobs')
                ->with('success', 'Reports imported successfully.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: {$failure->errors()[0]}";
            }
            
            return redirect()->back()->withErrors(['import_errors' => $errors]);
        } catch (\Exception $e) {
            Log::error('Error importing reports', ['error' => $e->getMessage()]);
            
            return redirect()->back()
                ->with('error', 'Error importing reports: ' . $e->getMessage());
        }
    }

    /**
     * Export template for daily reports import
     */
    public function exportTemplate()
    {
        return Excel::download(new DailyReportsTemplateExport, 'daily_reports_template.xlsx');
    }
}
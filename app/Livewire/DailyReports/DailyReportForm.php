<?php

namespace App\Livewire\DailyReports;

use App\Models\DailyReport;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class DailyReportForm extends Component
{
    use WithFileUploads;
    
    // Form fields
    public $reportId;
    public $jobName;
    public $departmentId;
    public $reportDate;
    public $dueDate;
    public $jobPic;
    public $description;
    public $remark;
    public $status = 'pending';
    public $attachment;
    
    // For editing
    public $existingAttachment;
    public $existingAttachmentName;
    public $isEditMode = false;
    
    // For multiple reports form
    public $showMultipleForm = false;
    public $multipleReports = [];
    
    // For validation
    public $eligiblePics;
    
    // Loading state
    public $isSubmitting = false;
    
    // Validation rules
    protected function rules()
    {
        return [
            'jobName' => 'required|string|max:255',
            'departmentId' => 'required|exists:departments,id',
            'reportDate' => 'required|date',
            'dueDate' => 'required|date|after_or_equal:reportDate',
            'jobPic' => 'required|exists:users,id',
            'description' => 'required|string',
            'remark' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'attachment' => $this->isEditMode 
                ? 'nullable|file|max:5120|mimes:jpeg,png,jpg,gif,svg,pdf,doc,docx,xls,xlsx' 
                : 'nullable|file|max:5120|mimes:jpeg,png,jpg,gif,svg,pdf,doc,docx,xls,xlsx',
        ];
    }
    
    public function __construct()
    {
        parent::__construct();
        $this->eligiblePics = collect([]);
    }
    
    public function mount($dailyReport = null)
    {
        // Set today as default for report date
        $this->reportDate = date('Y-m-d');
        
        // Set default department to user's department
        $user = Auth::user();
        $this->departmentId = $user->department_id;
        
        // Load eligible PICs for the user's department
        $this->loadEligiblePics();
        
        // If editing existing report
        if ($dailyReport) {
            $this->isEditMode = true;
            $this->reportId = $dailyReport->id;
            $this->jobName = $dailyReport->job_name;
            $this->departmentId = $dailyReport->department_id;
            $this->reportDate = $dailyReport->report_date->format('Y-m-d');
            $this->dueDate = $dailyReport->due_date->format('Y-m-d');
            $this->jobPic = $dailyReport->job_pic;
            $this->description = $dailyReport->description;
            $this->remark = $dailyReport->remark;
            $this->status = $dailyReport->status;
            
            if ($dailyReport->attachment_path) {
                $this->existingAttachment = $dailyReport->attachment_path;
                $this->existingAttachmentName = $dailyReport->attachment_original_name;
            }
            
            // Load eligible PICs including current PIC
            $this->loadEligiblePics($this->jobPic);
        }
    }
    
    public function updatedDepartmentId()
    {
        $this->loadEligiblePics();
        $this->jobPic = ''; // Reset PIC when department changes
    }
    
    private function loadEligiblePics($currentPicId = null)
    {
        // Get leader and department head roles
        $leaderRoles = Role::whereIn('slug', ['leader', 'department_head'])->pluck('id');
        
        // Get users with those roles in the selected department
        $query = User::whereIn('role_id', $leaderRoles)
            ->where('department_id', $this->departmentId)
            ->get();
            
        $this->eligiblePics = $query;
        
        // If we have a current PIC and they're not in the eligible list, add them
        if ($currentPicId) {
            $currentPic = User::find($currentPicId);
            if ($currentPic && !$this->eligiblePics->contains('id', $currentPicId)) {
                $this->eligiblePics->push($currentPic);
            }
        }
    }
    
    /**
     * Process and store file attachment
     */
    private function processAttachment($file, $oldAttachmentPath = null)
    {
        // Generate a secure filename
        $originalName = $file->getClientOriginalName();
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
        
        // Check if it's an image
        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
            // Create image manager with GD driver
            $manager = new ImageManager(Driver::class);
            
            // Load and compress image
            $image = $manager->read($file->getRealPath());
            
            // Resize if width or height is greater than 1920px while maintaining aspect ratio
            $image->scaleDown(1920, 1920);
            
            // Save compressed image to public disk with higher compression (60% quality instead of 80%)
            Storage::disk('public')->put($path, $image->toJpeg(60));
        } else {
            // For non-image files, store as is in public disk under attachments directory
            Storage::disk('public')->putFileAs('attachments', $file, $filename);
        }
        
        return [
            'path' => $path,
            'originalName' => $originalName
        ];
    }
    
    public function save()
    {
        $this->isSubmitting = true;
        
        // Validate the form data
        $this->validate();
        
        try {
            $attachmentPath = $this->existingAttachment;
            $originalName = $this->existingAttachmentName;
            
            // Process the file attachment if provided
            if ($this->attachment) {
                $attachmentData = $this->processAttachment($this->attachment, $this->existingAttachment);
                $attachmentPath = $attachmentData['path'];
                $originalName = $attachmentData['originalName'];
            }
            
            $data = [
                'user_id' => Auth::id(),
                'job_name' => $this->jobName,
                'department_id' => $this->departmentId,
                'job_pic' => $this->jobPic,
                'report_date' => $this->reportDate,
                'due_date' => $this->dueDate,
                'description' => $this->description,
                'remark' => $this->remark,
                'status' => $this->status,
                'attachment_path' => $attachmentPath,
                'attachment_original_name' => $originalName,
            ];
            
            if ($this->isEditMode) {
                // Update existing report
                $report = DailyReport::findOrFail($this->reportId);
                $report->update($data);
                $message = 'Daily report updated successfully.';
            } else {
                // Create new report
                DailyReport::create($data);
                $message = 'Daily report created successfully.';
            }
            
            // Show success message and redirect
            session()->flash('success', $message);
            $this->redirect(route('daily-reports.index'));
        } catch (\Exception $e) {
            // Delete uploaded file if exists and there was an error
            if (isset($attachmentPath) && $attachmentPath !== $this->existingAttachment && Storage::disk('public')->exists($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
            }
            
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
        
        $this->isSubmitting = false;
    }
    
    public function render()
    {
        $departments = Department::all();
        
        return view('livewire.daily-reports.daily-report-form', [
            'departments' => $departments,
            'pics' => $this->eligiblePics,
            'dailyReport' => $this->isEditMode ? DailyReport::find($this->reportId) : null,
        ]);
    }
    
    // Methods for multiple reports
    public function addReport()
    {
        $this->multipleReports[] = [
            'job_name' => '',
            'description' => '',
            'due_date' => date('Y-m-d', strtotime('+7 days')),
        ];
    }
    
    public function removeReport($index)
    {
        unset($this->multipleReports[$index]);
        $this->multipleReports = array_values($this->multipleReports);
    }
}

<?php

namespace App\Livewire\DailyReports;

use App\Models\DailyReport;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class DailyReportList extends Component
{
    use WithPagination;
    
    // Search and filter properties
    public $search = '';
    public $statusFilter = '';
    public $departmentFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    
    // Selected items for batch actions
    public $selected = [];
    public $selectAll = false;
    
    // Listeners
    protected $listeners = ['refreshReports' => '$refresh'];
    
    // Helper methods for user role checking (delegate to User model)
    private function isAdmin($user)
    {
        return $user->isAdmin();
    }

    private function canApproveReports($user)
    {
        // Users with Level 2 and above (or Admin) can approve
        return $user->isAdmin() || $user->getRoleLevel() >= 2;
    }

    private function canDeleteReports($user)
    {
        // Only Admin and Level 5 can batch delete
        return $user->isAdmin() || $user->isLevel5();
    }
    
    // Reset pagination when filters change
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    public function updatedStatusFilter()
    {
        $this->resetPage();
    }
    
    public function updatedDepartmentFilter()
    {
        $this->resetPage();
    }
    
    public function updatedDateFrom()
    {
        $this->resetPage();
    }
    
    public function updatedDateTo()
    {
        $this->resetPage();
    }
    
    // Toggle select all functionality
    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selected = $this->getReportsQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }
    
    // Get reports with filtering
    private function getReportsQuery()
    {
        $user = Auth::user();
        
        $query = DailyReport::query()
            ->with(['user', 'approver', 'department', 'pic']);
        
        // Filter by status if selected
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        
        // Filter by department if selected and not admin
        if ($this->departmentFilter) {
            $query->where('department_id', $this->departmentFilter);
        } else if (!$this->isAdmin($user)) {
            $query->where('department_id', $user->department_id);
        }
        
        // Filter by date range
        if (request('date_from')) {
            $query->whereDate('report_date', '>=', request('date_from'));
        }
        
        if (request('date_to')) {
            $query->whereDate('report_date', '<=', request('date_to'));
        }
        
        // Search in job name, description or remark
        if ($this->search) {
            $query->where(function($q) {
                $q->where('job_name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('remark', 'like', '%' . $this->search . '%');
            });
        }
        
        return $query;
    }
    
    // Batch approve selected reports
    public function batchApprove()
    {
        if (empty($this->selected)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'message' => 'No reports selected for approval'
            ]);
            return;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $approvedCount = 0;

        // Check if user has permission to approve
        if (!$this->canApproveReports($user)) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'You do not have permission to approve reports'
            ]);
            return;
        }

        foreach ($this->selected as $id) {
            $report = DailyReport::with('user')->find($id);

            // Skip if not found or already approved
            if (!$report || $report->approval_status === 'approved') {
                continue;
            }

            // Check if user can approve this specific report based on role hierarchy
            if (!$report->user || !$user->canApprove($report->user)) {
                continue;
            }

            $report->approval_status = 'approved';
            $report->approved_by = $user->id;
            $report->rejection_reason = null;
            $report->save();
            $approvedCount++;
        }

        // Reset selections
        $this->selected = [];
        $this->selectAll = false;

        $this->dispatch('showAlert', [
            'type' => 'success',
            'message' => "Successfully approved {$approvedCount} reports"
        ]);
    }
    
    // Batch delete selected reports
    public function batchDelete()
    {
        if (empty($this->selected)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'message' => 'No reports selected for deletion'
            ]);
            return;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $deletedCount = 0;

        foreach ($this->selected as $id) {
            $report = DailyReport::find($id);

            if (!$report) {
                continue;
            }

            // Check if user can delete this report
            $canDelete = false;

            // Admin and Level 5 can delete any report
            if ($this->canDeleteReports($user)) {
                $canDelete = true;
            }
            // Owner can delete their own pending reports
            else if ($report->user_id === $user->id && $report->approval_status === 'pending') {
                $canDelete = true;
            }

            if ($canDelete) {
                // Delete all attachments if they exist
                if ($report->attachment_path) {
                    if (Storage::disk('public')->exists($report->attachment_path)) {
                        Storage::disk('public')->delete($report->attachment_path);
                    }
                }
                if ($report->attachment_path_2) {
                    if (Storage::disk('public')->exists($report->attachment_path_2)) {
                        Storage::disk('public')->delete($report->attachment_path_2);
                    }
                }
                if ($report->attachment_path_3) {
                    if (Storage::disk('public')->exists($report->attachment_path_3)) {
                        Storage::disk('public')->delete($report->attachment_path_3);
                    }
                }

                $report->delete();
                $deletedCount++;
            }
        }

        // Reset selections
        $this->selected = [];
        $this->selectAll = false;

        $this->dispatch('showAlert', [
            'type' => 'success',
            'message' => "Successfully deleted {$deletedCount} reports"
        ]);
    }
    
    public function render()
    {
        $reports = $this->getReportsQuery()
            ->latest()
            ->paginate(10);
            
        $departments = Department::all();
        
        return view('livewire.daily-reports.daily-report-list', [
            'reports' => $reports,
            'departments' => $departments
        ]);
    }
}

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
    
    // Helper methods for user role checking
    private function userHasRole($user, $roleName)
    {
        return $user->role && $user->role->slug === $roleName;
    }
    
    private function isAdmin($user)
    {
        return $this->userHasRole($user, 'admin');
    }
    
    private function isDepartmentHead($user)
    {
        return $this->userHasRole($user, 'department_head');
    }
    
    private function isLeader($user)
    {
        return $this->userHasRole($user, 'leader');
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
        
        $user = Auth::user();
        $approvedCount = 0;
        
        // Check if user has permission to approve
        if (!$this->isAdmin($user) && !$this->isDepartmentHead($user) && !$this->isLeader($user)) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'You do not have permission to approve reports'
            ]);
            return;
        }
        
        foreach ($this->selected as $id) {
            $report = DailyReport::find($id);
            
            // Skip if not found or already approved
            if (!$report || $report->status === 'approved') {
                continue;
            }
            
            // Check if user can approve this report (must be from same department)
            if (!$this->isAdmin($user) && $user->department_id !== $report->department_id) {
                continue;
            }
            
            $report->status = 'approved';
            $report->approved_by = $user->id;
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
        
        $user = Auth::user();
        $deletedCount = 0;
        
        foreach ($this->selected as $id) {
            $report = DailyReport::find($id);
            
            if (!$report) {
                continue;
            }
            
            // Check if user can delete this report
            $canDelete = false;
            
            if ($this->isAdmin($user)) {
                $canDelete = true;
            } else if ($report->user_id === $user->id && $report->status === 'pending') {
                $canDelete = true;
            }
            
            if ($canDelete) {
                // Delete attachment if exists
                if ($report->attachment_path) {
                    if (Storage::disk('public')->exists($report->attachment_path)) {
                        Storage::disk('public')->delete($report->attachment_path);
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

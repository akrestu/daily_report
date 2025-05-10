<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        
        // Common data for all roles
        $data = [
            'user' => $user,
        ];
        
        // Common dashboard statistics for all users
        $data['totalReports'] = DailyReport::count();
        $data['pendingReports'] = DailyReport::where('status', 'pending')
            ->where('approval_status', '!=', 'rejected')
            ->count();
        $data['inProgressReports'] = DailyReport::where('status', 'in_progress')
            ->where('approval_status', '!=', 'rejected')
            ->count();
        $data['completedReports'] = DailyReport::where('status', 'completed')
            ->where('approval_status', '!=', 'rejected')
            ->count();
        $data['rejectedReports'] = DailyReport::where('approval_status', 'rejected')->count();
        $data['reportsToday'] = DailyReport::whereDate('created_at', $today)->count();
        $data['reportsThisWeek'] = DailyReport::where('created_at', '>=', $startOfWeek)->count();
        $data['reportsThisMonth'] = DailyReport::where('created_at', '>=', $startOfMonth)->count();
        
        // Calculate completion percentage (excluding rejected reports)
        $totalNonRejectedReports = DailyReport::where('approval_status', '!=', 'rejected')->count();
        $data['completionPercentage'] = $totalNonRejectedReports > 0 
            ? round(($data['completedReports'] / $totalNonRejectedReports) * 100, 1) 
            : 0;
            
        // Recent reports for all users
        $data['recentReports'] = DailyReport::with(['department'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Top performers - users with highest completion rates
        $data['topPerformers'] = User::select('users.id', 'users.name')
            ->selectRaw('COUNT(CASE WHEN daily_reports.status = "completed" THEN 1 ELSE NULL END) as completed_reports')
            ->selectRaw('COUNT(CASE WHEN daily_reports.approval_status != "rejected" THEN daily_reports.id ELSE NULL END) as total_reports')
            ->selectRaw('ROUND((COUNT(CASE WHEN daily_reports.status = "completed" THEN 1 ELSE NULL END) / COUNT(CASE WHEN daily_reports.approval_status != "rejected" THEN daily_reports.id ELSE NULL END)) * 100, 1) as completion_rate')
            ->leftJoin('daily_reports', 'users.id', '=', 'daily_reports.job_pic')
            ->groupBy('users.id', 'users.name')
            ->having('total_reports', '>', 0)
            ->orderBy('completion_rate', 'desc')
            ->limit(5)
            ->get();
            
        // Urgent reports
        $data['urgentReports'] = DailyReport::with(['department'])
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', Carbon::today())
            ->whereDate('due_date', '<=', Carbon::today()->addDays(3))
            ->orderBy('due_date')
            ->limit(5)
            ->get();
        
        // Average completion times by department
        $data['avgCompletionTimes'] = Department::select('departments.id', 'departments.name')
            ->selectRaw('ROUND(AVG(DATEDIFF(daily_reports.updated_at, daily_reports.created_at)), 1) as avg_days')
            ->join('daily_reports', 'departments.id', '=', 'daily_reports.department_id')
            ->where('daily_reports.status', '=', 'completed')
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('avg_days')
            ->limit(5)
            ->get();
            
        // Recent activities
        $data['recentActivities'] = DailyReport::with(['department', 'pic'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
            
        // Average approval time (in hours)
        // This calculates the average time it takes for a report to go from 'pending' to 'completed' status
        // You may need to adjust this calculation based on your actual approval workflow
        $completedReports = DailyReport::where('status', 'completed')
            ->whereNotNull('updated_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours'))
            ->first();
            
        $data['avgApprovalTime'] = round($completedReports->avg_hours ?? 0, 1);
        
        // Report trend data for all charts - using default 14 days of data
        $data['reportTrend'] = $this->getReportTrendData();
        
        // Department performance data for the bar chart
        $data['departmentPerformance'] = $this->getDepartmentPerformanceData();
        
        // Admin data
        if ($user->hasRole('admin')) {
            $data['adminStats'] = [
                'totalUsers' => User::count(),
                'totalDepartments' => Department::count(),
                'activeUsers' => User::count(),
                'totalReports' => DailyReport::count(),
                'usersByDepartment' => Department::withCount('users')->get(),
            ];
            
            // Trend of reports created over time
            $data['reportTrend'] = $this->getReportTrendData();
            
            // Department performance comparison
            $data['departmentPerformance'] = $this->getDepartmentPerformanceData();
            
            // User activity metrics
            $data['userActivityMetrics'] = $this->getUserActivityMetrics();
            
            // For admin dashboard
            $data['totalUsers'] = User::count();
            $data['pendingReports'] = DailyReport::where('status', 'pending')
                ->where('approval_status', '!=', 'rejected')
                ->count();
            $data['inProgressReports'] = DailyReport::where('status', 'in_progress')
                ->where('approval_status', '!=', 'rejected')
                ->count();
            $data['completedReports'] = DailyReport::where('status', 'completed')
                ->where('approval_status', '!=', 'rejected')
                ->count();
            $data['rejectedReports'] = DailyReport::where('approval_status', 'rejected')->count();
            
            // Recent users and reports
            $data['recentUsers'] = User::orderBy('created_at', 'desc')->limit(5)->get();
            $data['recentReports'] = DailyReport::with(['user', 'department'])->orderBy('created_at', 'desc')->limit(5)->get();
            
            // User stats
            $data['adminsCount'] = User::whereHas('role', function($query) {
                $query->where('slug', 'admin');
            })->count();
            $data['leadersCount'] = User::whereHas('role', function($query) {
                $query->where('slug', 'leader');
            })->count();
            $data['staffCount'] = User::whereHas('role', function($query) {
                $query->where('slug', 'staff');
            })->count();
            
            // Active users today
            $data['activeUsersToday'] = DailyReport::whereDate('created_at', $today)->distinct('user_id')->count('user_id');
            
            // Calculate active user change percentage
            $yesterdayActiveUsers = DailyReport::whereDate('created_at', Carbon::yesterday())->distinct('user_id')->count('user_id');
            $data['activeUsersChange'] = $yesterdayActiveUsers > 0 
                ? round((($data['activeUsersToday'] - $yesterdayActiveUsers) / $yesterdayActiveUsers) * 100, 1)
                : 0;
                
            // Top departments
            $data['topDepartments'] = Department::withCount('dailyReports')
                ->orderBy('daily_reports_count', 'desc')
                ->limit(5)
                ->get();
                
            // Prepare report trends data
            $data['reportTrendsData'] = $this->getReportTrendsChartData();
        }
        
        // Department Head data
        if ($user->hasRole('department_head') && $user->department_id) {
            // Department's performance
            $data['departmentPerformance'] = $this->getDepartmentPerformanceData($user->department_id);
            
            // Total reports in department that need approval (approval_status = pending)
            $data['pendingReports'] = DailyReport::where('approval_status', 'pending')
                ->where('department_id', $user->department_id)
                ->count();
            
            // Reports with status in_progress in the department
            $data['inProgressReports'] = DailyReport::where('status', 'in_progress')
                ->where('approval_status', '!=', 'rejected')
                ->where('department_id', $user->department_id)
                ->count();
            
            // Reports with status completed in the department
            $data['completedReports'] = DailyReport::where('status', 'completed')
                ->where('approval_status', '!=', 'rejected')
                ->where('department_id', $user->department_id)
                ->count();
            
            // Reports with approval_status rejected in the department
            $data['rejectedReports'] = DailyReport::where('approval_status', 'rejected')
                ->where('department_id', $user->department_id)
                ->count();
                
            // Department's productivity trend
            $data['departmentTrend'] = $this->getReportTrendData($user->department_id);
            
            // Total reports in department
            $data['totalReports'] = DailyReport::where('department_id', $user->department_id)->count();
            
            // Completion percentage for department
            $totalNonRejectedDeptReports = DailyReport::where('department_id', $user->department_id)
                ->where('approval_status', '!=', 'rejected')
                ->count();
            $data['completionPercentage'] = $totalNonRejectedDeptReports > 0 
                ? round(($data['completedReports'] / $totalNonRejectedDeptReports) * 100, 1) 
                : 0;
                
            // Reports needing approval
            $data['needsApproval'] = DailyReport::with(['department', 'pic'])
                ->where('approval_status', 'pending')
                ->where('department_id', $user->department_id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }
        
        // Leader data
        if ($user->hasRole('leader') && $user->department_id) {
            // Reports needing approval (if leader can approve staff reports)
            $data['needsApproval'] = DailyReport::with(['department', 'pic'])
                ->where('approval_status', 'pending')
                ->where('department_id', $user->department_id)
                ->where('job_pic', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Team reports status
            $data['pendingReports'] = DailyReport::where('status', 'pending')
                ->where('approval_status', '!=', 'rejected')
                ->where('department_id', $user->department_id)->count();
            $data['inProgressReports'] = DailyReport::where('status', 'in_progress')
                ->where('approval_status', '!=', 'rejected')
                ->where('department_id', $user->department_id)->count();
            $data['completedReports'] = DailyReport::where('status', 'completed')
                ->where('approval_status', '!=', 'rejected')
                ->where('department_id', $user->department_id)->count();
            $data['rejectedReports'] = DailyReport::where('approval_status', 'rejected')
                ->where('department_id', $user->department_id)->count();
            
            // Personal report status
            $data['myPendingReports'] = DailyReport::where('status', 'pending')
                ->where('approval_status', '!=', 'rejected')
                ->where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                          ->orWhere('user_id', $user->id);
                })->count();
            $data['myInProgressReports'] = DailyReport::where('status', 'in_progress')
                ->where('approval_status', '!=', 'rejected')
                ->where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                          ->orWhere('user_id', $user->id);
                })->count();
            $data['myCompletedReports'] = DailyReport::where('status', 'completed')
                ->where('approval_status', '!=', 'rejected')
                ->where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                          ->orWhere('user_id', $user->id);
                })->count();
            $data['myRejectedReports'] = DailyReport::where('approval_status', 'rejected')
                ->where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                          ->orWhere('user_id', $user->id);
                })->count();
            
            // Personal report history
            $data['myRecentReports'] = DailyReport::where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                          ->orWhere('user_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Deadline reminders
            $data['urgentReports'] = DailyReport::with(['department', 'pic'])
                ->where('status', '!=', 'completed')
                ->where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                        ->orWhere('department_id', $user->department_id);
                })
                ->whereDate('due_date', '>=', Carbon::today())
                ->whereDate('due_date', '<=', Carbon::today()->addDays(3))
                ->orderBy('due_date')
                ->limit(5)
                ->get();
            
            // Total reports and trending
            $data['reportTrend'] = $this->getReportTrendData($user->department_id);
        }
        
        // Staff data
        if ($user->hasRole('staff')) {
            // Personal report status berdasarkan Job Status dan Approval Status
            // Status Job: pending, in_progress, completed
            // Status Approval: rejected
            
            // Pending reports (Job Status = 'pending', but not rejected)
            $data['myPendingReports'] = DailyReport::where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                        ->orWhere('user_id', $user->id);
                })
                ->where('status', 'pending')
                ->where('approval_status', '!=', 'rejected')
                ->count();
                
            // In Progress reports (Job Status = 'in_progress')
            $data['myInProgressReports'] = DailyReport::where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                        ->orWhere('user_id', $user->id);
                })
                ->where('status', 'in_progress')
                ->where('approval_status', '!=', 'rejected')
                ->count();
                
            // Completed reports (Job Status = 'completed')
            $data['myCompletedReports'] = DailyReport::where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                        ->orWhere('user_id', $user->id);
                })
                ->where('status', 'completed')
                ->where('approval_status', '!=', 'rejected')
                ->count();
                
            // Rejected reports (Approval Status = 'rejected')
            $data['myRejectedReports'] = DailyReport::where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                        ->orWhere('user_id', $user->id);
                })
                ->where('approval_status', 'rejected')
                ->count();
            
            // Personal report history
            $data['myRecentReports'] = DailyReport::where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                          ->orWhere('user_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Deadline reminders
            $data['urgentReports'] = DailyReport::with(['department', 'pic'])
                ->where('status', '!=', 'completed')
                ->where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                        ->orWhere('user_id', $user->id);
                })
                ->whereDate('due_date', '>=', Carbon::today())
                ->whereDate('due_date', '<=', Carbon::today()->addDays(3))
                ->orderBy('due_date')
                ->limit(5)
                ->get();
            
            // Personal trend
            $data['myReportTrend'] = $this->getPersonalReportTrendData($user->id);
            
            // Calculate completion rate - include both created and assigned reports, excluding rejected reports
            $totalUserReports = DailyReport::where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                        ->orWhere('user_id', $user->id);
                })
                ->where('approval_status', '!=', 'rejected')
                ->count();
            $data['totalUserReports'] = $totalUserReports;
            $data['completionRate'] = $totalUserReports > 0 
                ? round(($data['myCompletedReports'] / $totalUserReports) * 100, 1) 
                : 0;
            
            // Calculate on-time delivery rate
            $completedReports = DailyReport::where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                        ->orWhere('user_id', $user->id);
                })
                ->where('status', 'completed')
                ->count();
            
            $onTimeReports = DailyReport::where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                        ->orWhere('user_id', $user->id);
                })
                ->where('status', 'completed')
                ->where(function($query) {
                    $query->whereNull('due_date')
                        ->orWhereRaw('DATE(updated_at) <= DATE(due_date)');
                })
                ->count();
            
            $data['onTimeRate'] = $completedReports > 0 
                ? round(($onTimeReports / $completedReports) * 100, 1) 
                : 0;
            
            // Reports this month and percentage change
            $data['reportsThisMonth'] = DailyReport::where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                        ->orWhere('user_id', $user->id);
                })
                ->whereDate('created_at', '>=', $startOfMonth)
                ->count();
            
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
            $reportsLastMonth = DailyReport::where(function($query) use ($user) {
                    $query->where('job_pic', $user->id)
                        ->orWhere('user_id', $user->id);
                })
                ->whereDate('created_at', '>=', $lastMonthStart)
                ->whereDate('created_at', '<=', $lastMonthEnd)
                ->count();
            
            $data['reportsChangePercentage'] = $reportsLastMonth > 0 
                ? round((($data['reportsThisMonth'] - $reportsLastMonth) / $reportsLastMonth) * 100, 1) 
                : ($data['reportsThisMonth'] > 0 ? 100 : 0);
            
            // Calculate feedback metrics based on comments on jobs created by the user
            $userReportIds = DailyReport::where('user_id', $user->id)->pluck('id')->toArray();
            $commentsCount = 0;
            
            if (!empty($userReportIds)) {
                $commentsCount = \App\Models\JobComment::whereIn('daily_report_id', $userReportIds)->count();
            }
            
            $data['feedbackCount'] = $commentsCount;
            
            if ($totalUserReports > 0) {
                // Calculate average feedback (comments per report)
                $data['averageFeedback'] = $totalUserReports > 0 
                    ? round($commentsCount / $totalUserReports, 1) 
                    : 0;
            } else {
                // No feedback data available
                $data['averageFeedback'] = 0;
                $data['feedbackCount'] = 0;
            }
            
            // Setup performance data for chart
            $totalUserReports = DailyReport::where('user_id', $user->id)->count();
            
            // Get daily job reports created by the user for the last 14 days
            $startDate = Carbon::now()->subDays(13)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            $labels = [];
            $totalData = [];
            $completedData = [];
            $pendingData = [];
            $inProgressData = [];
            
            // Get all job reports created by the user (excluding rejected)
            $totalReports = DailyReport::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('user_id', $user->id)
                ->where('approval_status', '!=', 'rejected')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // Get completed job reports created by the user
            $completedReports = DailyReport::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();
                
            // Get pending job reports created by the user (excluding rejected)
            $pendingReports = DailyReport::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->where('approval_status', '!=', 'rejected')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();
                
            // Get in-progress job reports created by the user
            $inProgressReports = DailyReport::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('user_id', $user->id)
                ->where('status', 'in_progress')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // Create lookup arrays for easy access
            $totalsByDate = [];
            foreach ($totalReports as $report) {
                $totalsByDate[$report->date] = $report->count;
            }
            
            $completedByDate = [];
            foreach ($completedReports as $report) {
                $completedByDate[$report->date] = $report->count;
            }
            
            $pendingByDate = [];
            foreach ($pendingReports as $report) {
                $pendingByDate[$report->date] = $report->count;
            }
            
            $inProgressByDate = [];
            foreach ($inProgressReports as $report) {
                $inProgressByDate[$report->date] = $report->count;
            }
            
            // Generate data for each day in the range
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                $dateString = $date->format('Y-m-d');
                $labels[] = $date->format('M d, Y'); // Include year in the label
                $totalData[] = isset($totalsByDate[$dateString]) ? $totalsByDate[$dateString] : 0;
                $completedData[] = isset($completedByDate[$dateString]) ? $completedByDate[$dateString] : 0;
                $pendingData[] = isset($pendingByDate[$dateString]) ? $pendingByDate[$dateString] : 0;
                $inProgressData[] = isset($inProgressByDate[$dateString]) ? $inProgressByDate[$dateString] : 0;
            }
            
            $data['performanceData'] = [
                'labels' => $labels,
                'completed' => $completedData,
                'pending' => $pendingData,
                'in_progress' => $inProgressData,
                'total' => $totalData
            ];
        }
        
        return view('dashboard', $data);
    }
    
    /**
     * Get report trend data for the past 14 days
     */
    private function getReportTrendData($departmentId = null)
    {
        $startDate = Carbon::now()->subDays(13)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        $query = DailyReport::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->where('approval_status', '!=', 'rejected')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);
            
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        $data = $query->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
            
        $result = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $result[] = [
                'date' => $date->format('M d'),
                'count' => $data[$dateString]->total ?? 0
            ];
        }
        
        return $result;
    }
    
    /**
     * Get personal report trend data
     */
    private function getPersonalReportTrendData($userId)
    {
        $startDate = Carbon::now()->subDays(13)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        $data = DailyReport::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->where('approval_status', '!=', 'rejected')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->where(function($query) use ($userId) {
                $query->where('job_pic', $userId)
                    ->orWhere('user_id', $userId);
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
            
        $result = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $result[] = [
                'date' => $date->format('M d'),
                'count' => $data[$dateString]->total ?? 0
            ];
        }
        
        return $result;
    }
    
    /**
     * Get department performance data
     */
    private function getDepartmentPerformanceData($departmentId = null)
    {
        $query = Department::select('departments.id', 'departments.name')
            ->selectRaw('COUNT(CASE WHEN daily_reports.approval_status != "rejected" THEN daily_reports.id ELSE NULL END) as total_reports')
            ->selectRaw('SUM(CASE WHEN daily_reports.status = "completed" THEN 1 ELSE 0 END) as completed_reports')
            ->leftJoin('daily_reports', 'departments.id', '=', 'daily_reports.department_id')
            ->groupBy('departments.id', 'departments.name')
            ->having('total_reports', '>', 0);
            
        if ($departmentId) {
            $query->where('departments.id', $departmentId);
        } else {
            $query->orderBy('completed_reports', 'desc')
                ->limit(8);
        }
        
        return $query->get()
            ->map(function ($dept) {
                $dept->completion_rate = $dept->total_reports > 0 
                    ? round(($dept->completed_reports / $dept->total_reports) * 100, 1) 
                    : 0;
                return $dept;
            });
    }
    
    /**
     * Get user activity metrics
     */
    private function getUserActivityMetrics()
    {
        // Get count of reports by user
        $activeUsers = User::select('users.id', 'users.name')
            ->selectRaw('COUNT(daily_reports.id) as report_count')
            ->leftJoin('daily_reports', 'users.id', '=', 'daily_reports.user_id')
            ->whereRaw('daily_reports.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')
            ->groupBy('users.id', 'users.name')
            ->orderBy('report_count', 'desc')
            ->limit(5)
            ->get();
            
        return $activeUsers;
    }
    
    /**
     * Get report trends data for the admin dashboard
     */
    private function getReportTrendsChartData()
    {
        $startDate = Carbon::now()->subDays(29)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        // Get total reports by date (excluding rejected)
        $totalReports = DailyReport::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('approval_status', '!=', 'rejected')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        
        // Get completed reports by date
        $completedReports = DailyReport::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        
        $labels = [];
        $totalData = [];
        $completedData = [];
        
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $dateLabel = $date->format('M d');
            
            $labels[] = $dateLabel;
            $totalData[] = $totalReports[$dateString]->count ?? 0;
            $completedData[] = $completedReports[$dateString]->count ?? 0;
        }
        
        return [
            'labels' => $labels,
            'total' => $totalData,
            'completed' => $completedData
        ];
    }
    
    /**
     * Toggle sidebar collapsed state
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toggleSidebar(Request $request)
    {
        $collapsed = $request->input('collapsed', false);
        session(['sidebarCollapsed' => $collapsed]);
        
        return response()->json(['success' => true]);
    }
}
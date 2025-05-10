<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrganizationChartController extends Controller
{
    /**
     * Display the organization chart for the user's department
     */
    public function index(): View
    {
        $user = Auth::user();
        $department = Department::with(['users' => function($query) {
            $query->with('role')->orderBy('role_id');
        }])->findOrFail($user->department_id);
        
        // Get all roles for mapping
        $roles = Role::orderBy('id')->get();
        
        // Organize users by role 
        $roleHierarchy = [];
        
        // Department head at the top
        $departmentHead = $department->users->where('role.slug', 'department_head')->first();
        
        // Leaders in the middle
        $leaders = $department->users->where('role.slug', 'leader');
        
        // Staff at the bottom
        $staff = $department->users->where('role.slug', 'staff');
        
        // Build organization tree
        $organizationTree = [
            'department' => $department,
            'head' => $departmentHead,
            'leaders' => $leaders,
            'staff' => $staff
        ];
        
        return view('organization.chart', [
            'department' => $department,
            'organizationTree' => $organizationTree,
            'roles' => $roles
        ]);
    }
} 
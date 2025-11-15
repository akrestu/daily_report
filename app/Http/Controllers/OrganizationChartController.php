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

        // Organize users by role level (new hierarchy: Level 7 → Level 1)
        // Note: Admin and Level 8 roles excluded - Admin is for system management, Level 8 is highest management level
        $organizationTree = [
            'department' => $department,
            'level7' => $department->users->where('role.slug', 'level7'),
            'level6' => $department->users->where('role.slug', 'level6'),
            'level5' => $department->users->where('role.slug', 'level5'),
            'level4' => $department->users->where('role.slug', 'level4'),
            'level3' => $department->users->where('role.slug', 'level3'),
            'level2' => $department->users->where('role.slug', 'level2'),
            'level1' => $department->users->where('role.slug', 'level1'),

            // Legacy roles for backward compatibility
            'head' => $department->users->where('role.slug', 'department_head')->first(),
            'leaders' => $department->users->where('role.slug', 'leader'),
            'staff' => $department->users->where('role.slug', 'staff'),
        ];

        return view('organization.chart', [
            'department' => $department,
            'organizationTree' => $organizationTree,
            'roles' => $roles
        ]);
    }
} 
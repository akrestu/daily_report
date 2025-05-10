<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Exports\UsersTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Notification;

class UserController extends Controller
{
    /**
     * Constructor to apply middleware
     */
    public function __construct()
    {
        $this->middleware('admin.only');
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['role', 'department'])->latest()->paginate(10);
        $roles = Role::pluck('name', 'id');
        $departments = Department::pluck('name', 'id');
        
        return view('admin.users.index', compact('users', 'roles', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $departments = Department::all();
        return view('admin.users.create', compact('roles', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Log the request for debugging
            Log::info('User creation request received', ['request' => $request->all()]);
            
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', Rules\Password::defaults()],
                'role_id' => ['required', 'exists:roles,id'],
                'department_id' => ['nullable', 'exists:departments,id'],
                'user_id' => ['nullable', 'string', 'max:255', 'unique:users'],
            ]);

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'department_id' => $request->department_id,
                'user_id' => $request->user_id,
                'email_verified_at' => now(),
            ];
            
            // Log attempt
            Log::info('Attempting to create user with data', $userData);

            try {
                $user = User::create($userData);
                
                // Create welcome notification for the new user
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'welcome',
                    'message' => 'Welcome to Daily Report System! We\'re glad to have you on board.',
                    'is_read' => false,
                ]);
                
                // Log success
                Log::info('User created successfully', ['user_id' => $user->id]);
                
                return redirect()->route('admin.users.index')
                    ->with('success', 'User created successfully.');
            } catch (\Exception $e) {
                // Log detailed database error
                Log::error('Database error creating user', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'userData' => $userData
                ]);
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Error creating user: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            // Log validation or other errors
            Log::error('Error in user creation process', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with(['role', 'department'])->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $departments = Department::all();
        return view('admin.users.edit', compact('user', 'roles', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'role_id' => ['required', 'exists:roles,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'user_id' => ['nullable', 'string', 'max:255', 'unique:users,user_id,' . $id],
        ];
        
        // Only validate password if it's provided
        if ($request->filled('password')) {
            $rules['password'] = ['required', Rules\Password::defaults()];
        }
        
        $request->validate($rules);
        
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'department_id' => $request->department_id,
            'user_id' => $request->user_id,
        ];
        
        // Only update password if it's provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        // Ensure email is verified
        if (!$user->email_verified_at) {
            $data['email_verified_at'] = now();
        }
        
        $user->update($data);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Add detailed logging at the beginning
            Log::info('Destroy method called with ID', [
                'id' => $id,
                'request_method' => request()->method(),
                'request_path' => request()->path(),
                'request_all' => request()->all(),
            ]);
            
            $user = User::findOrFail($id);
            
            // Don't allow deleting your own account
            if ($user->id === Auth::id()) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot delete your own account.');
            }
            
            // Log user deletion attempt
            Log::info('Attempting to delete user', ['user_id' => $id, 'user_email' => $user->email]);
            
            $user->delete();
            
            Log::info('User deleted successfully', ['user_id' => $id]);
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting user', ['user_id' => $id, 'error' => $e->getMessage()]);
            
            return redirect()->route('admin.users.index')
                ->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }

    /**
     * Remove multiple users at once.
     */
    public function batchDelete(Request $request)
    {
        try {
            // Log the request for debugging
            Log::info('Batch delete request received', ['request' => $request->all()]);
            
            if (!$request->has('selected_users')) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'No users selected for deletion.');
            }
            
            $selectedUsers = $request->selected_users;
            
            // Ensure $selectedUsers is an array
            if (!is_array($selectedUsers)) {
                $selectedUsers = [$selectedUsers];
            }
            
            // Get current user's ID
            $currentUserId = Auth::id();
            
            // Filter out current user from the selection
            $selectedUsers = array_filter($selectedUsers, function($userId) use ($currentUserId) {
                return (int)$userId !== (int)$currentUserId;
            });
            
            if (empty($selectedUsers)) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Cannot delete your own account or no valid users selected.');
            }
            
            // Delete the selected users
            $deletedCount = User::whereIn('id', $selectedUsers)->delete();
            
            Log::info('Batch delete successful', ['count' => $deletedCount]);
            
            return redirect()->route('admin.users.index')
                ->with('success', $deletedCount . ' users deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error in batch delete', ['error' => $e->getMessage()]);
            
            return redirect()->route('admin.users.index')
                ->with('error', 'Error deleting users: ' . $e->getMessage());
        }
    }

    /**
     * Export users to Excel
     */
    public function export()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    /**
     * Show import form
     */
    public function showImport()
    {
        return view('admin.users.import');
    }

    /**
     * Import users from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new UsersImport, $request->file('file'));
            
            return redirect()->route('admin.users.index')
                ->with('success', 'Users imported successfully.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: {$failure->errors()[0]}";
            }
            
            return redirect()->back()->withErrors(['import_errors' => $errors]);
        } catch (\Exception $e) {
            Log::error('Error importing users', ['error' => $e->getMessage()]);
            
            return redirect()->back()
                ->with('error', 'Error importing users: ' . $e->getMessage());
        }
    }

    /**
     * Export template for user import
     */
    public function exportTemplate()
    {
        return Excel::download(new UsersTemplateExport, 'users_template.xlsx');
    }
}

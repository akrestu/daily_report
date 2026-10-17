<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
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
    public function index(Request $request)
    {
        // Apply search filter if provided
        $query = Department::withCount('users');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $departments = $query->latest()->paginate(10);
        
        if ($request->ajax()) {
            return response()->json([
                'departments' => $departments,
                'links' => $departments->links()->toHtml(),
            ]);
        }
        
        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:departments'],
            'description' => ['nullable', 'string'],
        ]);

        $department = Department::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Departemen berhasil dibuat.',
                'department' => $department
            ]);
        }

        return redirect()->route('admin.departments.index')
            ->with('success', 'Departemen berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $department = Department::with('users')->findOrFail($id);
        return view('admin.departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $department = Department::findOrFail($id);
        return view('admin.departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $department = Department::findOrFail($id);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:departments,code,' . $department->id],
            'description' => ['nullable', 'string'],
        ]);

        $department->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Departemen berhasil diperbarui.',
                'department' => $department
            ]);
        }

        return redirect()->route('admin.departments.index')
            ->with('success', 'Departemen berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $department = Department::findOrFail($id);
        
        // Check if department has users
        $userCount = $department->users()->count();
        if ($userCount > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departemen tidak bisa dihapus karena masih memiliki ' . $userCount . ' pengguna.'
                ], 422);
            }
            
            return redirect()->route('admin.departments.index')
                ->with('error', 'Departemen tidak bisa dihapus karena masih memiliki ' . $userCount . ' pengguna.');
        }
        
        $department->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Departemen berhasil dihapus.'
            ]);
        }

        return redirect()->route('admin.departments.index')
            ->with('success', 'Departemen berhasil dihapus.');
    }
    
    /**
     * Batch delete departments
     */
    public function batchDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:departments,id',
        ]);

        $ids = $request->ids;
        $deletedCount = 0;
        $errorCount = 0;

        foreach ($ids as $id) {
            $department = Department::find($id);
            if ($department) {
                // Check if department has users
                $userCount = $department->users()->count();
                if ($userCount > 0) {
                    $errorCount++;
                    continue;
                }
                
                $department->delete();
                $deletedCount++;
            }
        }

        $message = $deletedCount . ' departemen berhasil dihapus.';
        if ($errorCount > 0) {
            $message .= ' ' . $errorCount . ' departemen tidak bisa dihapus karena masih memiliki pengguna.';
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'deleted' => $deletedCount,
                    'errors' => $errorCount
                ]);
            }
            
            return redirect()->route('admin.departments.index')
                ->with('warning', $message);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted' => $deletedCount
            ]);
        }

        return redirect()->route('admin.departments.index')
            ->with('success', $message);
    }
} 
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Department;
use Illuminate\Http\Request;

class SectionController extends Controller
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
        $query = Section::with('department')->withCount('dailyReports');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('department', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $sections = $query->latest()->paginate(10);
        $departments = Department::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'sections' => $sections,
                'links' => $sections->links()->toHtml(),
            ]);
        }

        return view('admin.sections.index', compact('sections', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.sections.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:sections'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $section = Section::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Section created successfully.',
                'section' => $section->load('department')
            ]);
        }

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $section = Section::with(['department', 'dailyReports'])->findOrFail($id);
        return view('admin.sections.show', compact('section'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $section = Section::findOrFail($id);
        $departments = Department::orderBy('name')->get();
        return view('admin.sections.edit', compact('section', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $section = Section::findOrFail($id);

        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:sections,code,' . $section->id],
            'description' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $section->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Section updated successfully.',
                'section' => $section->load('department')
            ]);
        }

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $section = Section::findOrFail($id);

        // Check if section has daily reports
        $reportCount = $section->dailyReports()->count();
        if ($reportCount > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete section because it has ' . $reportCount . ' reports associated with it.'
                ], 422);
            }

            return redirect()->route('admin.sections.index')
                ->with('error', 'Cannot delete section because it has ' . $reportCount . ' reports associated with it.');
        }

        $section->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Section deleted successfully.'
            ]);
        }

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section deleted successfully.');
    }

    /**
     * Batch delete sections
     */
    public function batchDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:sections,id',
        ]);

        $ids = $request->ids;
        $deletedCount = 0;
        $errorCount = 0;

        foreach ($ids as $id) {
            $section = Section::find($id);
            if ($section) {
                // Check if section has reports
                $reportCount = $section->dailyReports()->count();
                if ($reportCount > 0) {
                    $errorCount++;
                    continue;
                }

                $section->delete();
                $deletedCount++;
            }
        }

        $message = $deletedCount . ' sections deleted successfully.';
        if ($errorCount > 0) {
            $message .= ' ' . $errorCount . ' sections could not be deleted because they have reports associated.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'deleted' => $deletedCount,
                    'errors' => $errorCount
                ]);
            }

            return redirect()->route('admin.sections.index')
                ->with('warning', $message);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted' => $deletedCount
            ]);
        }

        return redirect()->route('admin.sections.index')
            ->with('success', $message);
    }

    /**
     * Get sections by department (for AJAX requests)
     */
    public function getByDepartment(Request $request)
    {
        $departmentId = $request->get('department_id');

        $sections = Section::where('department_id', $departmentId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($sections);
    }
}

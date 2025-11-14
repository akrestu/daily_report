<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobSite;
use Illuminate\Http\Request;

class JobSiteController extends Controller
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
        $query = JobSite::withCount('dailyReports');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $jobSites = $query->latest()->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'jobSites' => $jobSites,
                'links' => $jobSites->links()->toHtml(),
            ]);
        }

        return view('admin.job-sites.index', compact('jobSites'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.job-sites.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:job_sites'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $jobSite = JobSite::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Job Site created successfully.',
                'jobSite' => $jobSite
            ]);
        }

        return redirect()->route('admin.job-sites.index')
            ->with('success', 'Job Site created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $jobSite = JobSite::with('dailyReports')->findOrFail($id);
        return view('admin.job-sites.show', compact('jobSite'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $jobSite = JobSite::findOrFail($id);
        return view('admin.job-sites.edit', compact('jobSite'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $jobSite = JobSite::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:job_sites,code,' . $jobSite->id],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $jobSite->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Job Site updated successfully.',
                'jobSite' => $jobSite
            ]);
        }

        return redirect()->route('admin.job-sites.index')
            ->with('success', 'Job Site updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $jobSite = JobSite::findOrFail($id);

        // Check if job site has daily reports
        $reportCount = $jobSite->dailyReports()->count();
        if ($reportCount > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete job site because it has ' . $reportCount . ' reports associated with it.'
                ], 422);
            }

            return redirect()->route('admin.job-sites.index')
                ->with('error', 'Cannot delete job site because it has ' . $reportCount . ' reports associated with it.');
        }

        $jobSite->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Job Site deleted successfully.'
            ]);
        }

        return redirect()->route('admin.job-sites.index')
            ->with('success', 'Job Site deleted successfully.');
    }

    /**
     * Batch delete job sites
     */
    public function batchDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:job_sites,id',
        ]);

        $ids = $request->ids;
        $deletedCount = 0;
        $errorCount = 0;

        foreach ($ids as $id) {
            $jobSite = JobSite::find($id);
            if ($jobSite) {
                // Check if job site has reports
                $reportCount = $jobSite->dailyReports()->count();
                if ($reportCount > 0) {
                    $errorCount++;
                    continue;
                }

                $jobSite->delete();
                $deletedCount++;
            }
        }

        $message = $deletedCount . ' job sites deleted successfully.';
        if ($errorCount > 0) {
            $message .= ' ' . $errorCount . ' job sites could not be deleted because they have reports associated.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'deleted' => $deletedCount,
                    'errors' => $errorCount
                ]);
            }

            return redirect()->route('admin.job-sites.index')
                ->with('warning', $message);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted' => $deletedCount
            ]);
        }

        return redirect()->route('admin.job-sites.index')
            ->with('success', $message);
    }
}

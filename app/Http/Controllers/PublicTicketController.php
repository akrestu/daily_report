<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Models\Employee;
use App\Models\JobSite;
use App\Models\Ticket;
use App\Models\TicketCategory;
use Illuminate\Http\Request;

class PublicTicketController extends Controller
{
    public function showForm()
    {
        $categories = TicketCategory::active()->orderBy('name')->get();
        $jobSites = JobSite::where('is_active', true)->orderBy('name')->get();

        return view('tickets.public-form', compact('categories', 'jobSites'));
    }

    /**
     * Verifikasi NIK di form publik: hanya mengembalikan nama & departemen karyawan aktif.
     */
    public function lookupEmployee(Request $request)
    {
        $employee = Employee::findActiveByNik($request->query('nik'));

        if (! $employee) {
            return response()->json(['found' => false, 'message' => 'NIK tidak terdaftar atau tidak aktif.'], 404);
        }

        return response()->json([
            'found' => true,
            'name' => $employee->name,
            'department' => $employee->department?->name,
            'job_site_id' => $employee->job_site_id,
        ]);
    }

    public function store(StoreTicketRequest $request)
    {
        $ticket = Ticket::createWithNumber($request->ticketAttributes());

        // Detail tiket hanya ditampilkan sekali ke pengirim via session, bukan via nomor di URL
        return redirect()->route('tickets.public.success')->with('submitted_ticket_id', $ticket->id);
    }

    public function success(Request $request)
    {
        $ticketId = $request->session()->get('submitted_ticket_id');

        if (! $ticketId) {
            return redirect()->route('tickets.public.track');
        }

        $ticket = Ticket::with('category')->findOrFail($ticketId);

        return view('tickets.public-success', compact('ticket'));
    }

    public function trackForm()
    {
        return view('tickets.public-track');
    }

    public function track(Request $request)
    {
        $validated = $request->validate([
            'ticket_number' => 'required|string|max:30',
            'token' => 'required|string|max:16',
        ], [
            'ticket_number.required' => 'Nomor tiket wajib diisi.',
            'token.required' => 'Kode akses wajib diisi.',
        ]);

        $ticket = Ticket::findForTracking($validated['ticket_number'], $validated['token']);

        if (! $ticket) {
            return back()->withInput()
                ->withErrors(['ticket_number' => 'Nomor tiket atau kode akses tidak cocok.']);
        }

        $ticket->load(['category', 'assignedUser']);

        return view('tickets.public-track', compact('ticket'));
    }
}

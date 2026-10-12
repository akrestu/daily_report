<?php

namespace App\Http\Controllers;

use App\Exceptions\TicketWorkflowException;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Employee;
use App\Models\JobSite;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Services\TicketWorkflow;
use Illuminate\Http\Request;

class PublicTicketController extends Controller
{
    private const SESSION_KEY = 'tracked_tickets';

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
            'supervisors' => Employee::supervisorOptions($employee->department_id, $employee->id),
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

        // Akses ke halaman detail disimpan di session, sehingga kode akses tidak muncul di URL
        $request->session()->put(self::SESSION_KEY, array_unique([
            ...$request->session()->get(self::SESSION_KEY, []), $ticket->id,
        ]));

        return redirect()->route('tickets.public.track.show', $ticket);
    }

    public function showTracked(Request $request, Ticket $ticket)
    {
        $this->authorizeTracked($request, $ticket);

        $ticket->load(['category', 'assignedUser', 'histories',
            'comments' => fn ($q) => $q->with('user')->where('is_internal', false)]);

        return view('tickets.public-detail', compact('ticket'));
    }

    public function comment(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorizeTracked($request, $ticket);

        $validated = $request->validate(['comment' => 'required|string|max:2000'], [
            'comment.required' => 'Komentar wajib diisi.',
        ]);

        if (! $ticket->isActive()) {
            return back()->with('error', 'Tiket sudah ditutup. Silakan ajukan tiket baru.');
        }

        $workflow->addComment($ticket, $validated['comment'], null, $ticket->requester_name);

        return back()->with('success', 'Komentar terkirim ke tim IT.');
    }

    public function respond(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorizeTracked($request, $ticket);

        $validated = $request->validate([
            'action' => 'required|in:confirm,reopen,cancel',
            'reason' => 'nullable|string|max:2000',
        ]);

        try {
            $message = $workflow->applyRequesterResponse($ticket, $validated['action'], $validated['reason'] ?? null, null, $ticket->requester_name);
        } catch (TicketWorkflowException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }

    public function approvalForm(Request $request, Ticket $ticket)
    {
        $this->authorizeTracked($request, $ticket);
        abort_unless($ticket->approval_status, 404);

        $ticket->load(['category', 'supervisor.department']);

        return view('tickets.approval-print', compact('ticket'));
    }

    public function uploadApproval(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorizeTracked($request, $ticket);

        $request->validate(TicketController::approvalProofRules(), TicketController::approvalProofMessages());

        try {
            $workflow->uploadApprovalProof($ticket, $request->file('approval_proof'), null, $ticket->requester_name);
        } catch (TicketWorkflowException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Bukti persetujuan terkirim. Tim IT akan memverifikasi.');
    }

    public function approvalProof(Request $request, Ticket $ticket)
    {
        $this->authorizeTracked($request, $ticket);
        abort_unless($ticket->approval_proof_path, 404);

        return app(AttachmentController::class)->serve($ticket->approval_proof_path,
            $ticket->approval_proof_original_name ?: basename($ticket->approval_proof_path), ['ticket_id' => $ticket->id]);
    }

    public function submitCsat(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorizeTracked($request, $ticket);

        $validated = $request->validate(TicketController::csatRules(), ['rating.required' => 'Pilih nilai 1 sampai 5.']);

        try {
            $workflow->submitCsat($ticket, (int) $validated['rating'], $validated['comment'] ?? null, null, $ticket->requester_name);
        } catch (TicketWorkflowException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Terima kasih atas penilaian Anda.');
    }

    private function authorizeTracked(Request $request, Ticket $ticket): void
    {
        if (! in_array($ticket->id, $request->session()->get(self::SESSION_KEY, []), true)) {
            abort(redirect()->route('tickets.public.track')
                ->withErrors(['ticket_number' => 'Masukkan nomor tiket dan kode akses untuk melihat tiket.']));
        }
    }
}

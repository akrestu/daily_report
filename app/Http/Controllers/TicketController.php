<?php

namespace App\Http\Controllers;

use App\Exceptions\TicketWorkflowException;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Employee;
use App\Models\JobSite;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketSla;
use App\Services\TicketWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $query = Ticket::with(['category', 'assignedUser', 'jobPlan'])
            ->orderByRaw(self::orderByList('status', array_keys(Ticket::STATUSES)))
            ->orderByRaw(self::orderByList('priority', array_keys(Ticket::PRIORITIES)))
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->input('sla') === 'breached') {
            $query->where(fn ($q) => $q->where('resolution_breached', true)->orWhere('response_breached', true)
                ->orWhere(fn ($q) => $q->whereIn('status', TicketSla::RUNNING_STATUSES)->where('resolution_due_at', '<', now())));
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('ticket_number', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%")
                    ->orWhere('requester_department', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(15)->withQueryString();
        $categories = TicketCategory::active()->orderBy('name')->get();
        $openCount = Ticket::where('status', 'open')->count();

        return view('tickets.index', compact('tickets', 'categories', 'openCount'));
    }

    /**
     * Tiket yang diajukan oleh user yang login.
     */
    public function mine(Request $request)
    {
        $tickets = Ticket::with(['category', 'assignedUser'])
            ->where('requester_user_id', Auth::id())
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('tickets.mine', compact('tickets'));
    }

    public function create()
    {
        $categories = TicketCategory::active()->orderBy('name')->get();
        $jobSites = JobSite::where('is_active', true)->orderBy('name')->get();
        $supervisors = Employee::supervisorOptions(Auth::user()->department_id);

        return view('tickets.create', compact('categories', 'jobSites', 'supervisors'));
    }

    public function store(StoreTicketRequest $request)
    {
        $ticket = Ticket::createWithNumber($request->ticketAttributes());

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Tiket '.$ticket->ticket_number.' berhasil dibuat. Tim IT akan segera menindaklanjuti.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $canViewInternal = Gate::allows('viewInternal', $ticket);

        $ticket->load([
            'category', 'jobSite', 'assignedUser', 'jobPlan.assignee', 'histories',
            'comments' => fn ($q) => $q->with('user')->when(! $canViewInternal, fn ($q) => $q->where('is_internal', false)),
        ]);

        $eligibleHandlers = Gate::allows('assign', Ticket::class) ? $this->eligibleHandlers()->get() : collect();
        $transitions = Gate::allows('updateStatus', $ticket) ? TicketWorkflow::staffTransitions($ticket) : [];

        return view('tickets.show', compact('ticket', 'eligibleHandlers', 'transitions', 'canViewInternal'));
    }

    public function assign(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorize('assign', Ticket::class);

        $validated = $request->validate([
            'assigned_to' => ['required', Rule::in($this->eligibleHandlers()->pluck('id'))],
            'due_date' => 'required|date|after_or_equal:today',
        ], [
            'assigned_to.required' => 'Anggota tim wajib dipilih.',
            'assigned_to.in' => 'Handler harus anggota tim IT.',
            'due_date.required' => 'Tanggal target selesai wajib diisi.',
            'due_date.after_or_equal' => 'Tanggal tidak boleh sebelum hari ini.',
        ]);

        $handler = User::findOrFail($validated['assigned_to']);

        try {
            $workflow->assign($ticket, $handler, $validated['due_date'], Auth::user());
        } catch (TicketWorkflowException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Tiket ditugaskan ke '.$handler->name.'. Job plan otomatis masuk ke Tugas Masuk miliknya.');
    }

    public function updateStatus(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorize('updateStatus', $ticket);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Ticket::STATUSES))],
            'note' => 'nullable|string|max:2000',
        ]);

        try {
            $workflow->changeStatus($ticket, $validated['status'], Auth::user(), $validated['note'] ?? null);
        } catch (TicketWorkflowException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Status tiket diubah menjadi "'.$ticket->status_label.'".');
    }

    public function addComment(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorize('addComment', $ticket);

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        // Hanya tim IT yang bisa menulis catatan internal; pemohon selalu publik
        $internal = $request->boolean('is_internal') && Gate::allows('viewInternal', $ticket);

        $workflow->addComment($ticket, $validated['comment'], Auth::user(), Auth::user()->name, $internal);

        return back()->with('success', $internal ? 'Catatan internal ditambahkan.' : 'Balasan terkirim.');
    }

    /** Formulir persetujuan atasan siap cetak */
    public function approvalForm(Ticket $ticket)
    {
        $this->authorize('view', $ticket);
        abort_unless($ticket->approval_status, 404);

        $ticket->load(['category', 'supervisor.department']);

        return view('tickets.approval-print', compact('ticket'));
    }

    public function uploadApproval(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorize('respond', $ticket);

        $request->validate(self::approvalProofRules(), self::approvalProofMessages());

        try {
            $workflow->uploadApprovalProof($ticket, $request->file('approval_proof'), Auth::user(), Auth::user()->name);
        } catch (TicketWorkflowException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Bukti persetujuan terkirim. Tim IT akan memverifikasi.');
    }

    public function verifyApproval(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorize('verifyApproval', $ticket);

        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            $workflow->verifyApproval($ticket, $validated['decision'] === 'approve', Auth::user(), $validated['note'] ?? null);
        } catch (TicketWorkflowException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', $validated['decision'] === 'approve'
            ? 'Persetujuan diverifikasi. Tiket sekarang bisa di-assign.'
            : 'Bukti persetujuan ditolak. Pemohon diminta mengunggah ulang.');
    }

    public function submitCsat(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorize('respond', $ticket);

        $validated = $request->validate(self::csatRules());

        try {
            $workflow->submitCsat($ticket, (int) $validated['rating'], $validated['comment'] ?? null, Auth::user(), Auth::user()->name);
        } catch (TicketWorkflowException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Terima kasih atas penilaian Anda.');
    }

    public static function csatRules(): array
    {
        return [
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ];
    }

    public static function approvalProofRules(): array
    {
        return ['approval_proof' => 'required|file|max:5120|mimes:jpeg,jpg,png,pdf'];
    }

    public static function approvalProofMessages(): array
    {
        return [
            'approval_proof.required' => 'Pilih foto/scan formulir yang sudah ditandatangani.',
            'approval_proof.max' => 'Ukuran file maksimal 5 MB.',
            'approval_proof.mimes' => 'Format harus JPG, PNG, atau PDF.',
        ];
    }

    public function changePriority(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorize('assign', Ticket::class);

        $validated = $request->validate([
            'priority' => ['required', Rule::in(array_keys(Ticket::PRIORITIES))],
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $workflow->changePriority($ticket, $validated['priority'], Auth::user(), $validated['reason'] ?? null);
        } catch (TicketWorkflowException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Prioritas diubah menjadi '.$ticket->priority_label.'. Tenggat SLA dihitung ulang.');
    }

    /**
     * Respons pemohon (user login): konfirmasi selesai, buka kembali, atau batalkan.
     */
    public function respond(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorize('respond', $ticket);

        $validated = $request->validate([
            'action' => 'required|in:confirm,reopen,cancel',
            'reason' => 'nullable|string|max:2000',
        ]);

        try {
            $message = $workflow->applyRequesterResponse($ticket, $validated['action'], $validated['reason'] ?? null, Auth::user(), Auth::user()->name);
        } catch (TicketWorkflowException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }

    /**
     * Urutan kustom yang portabel (MySQL & SQLite), pengganti FIELD() milik MySQL.
     * Nilai berasal dari konstanta model, bukan input pengguna.
     */
    private static function orderByList(string $column, array $values): string
    {
        $cases = collect($values)->map(fn ($value, $i) => "WHEN '{$value}' THEN {$i}")->implode(' ');

        return "CASE {$column} {$cases} ELSE ".count($values).' END';
    }

    private function eligibleHandlers()
    {
        // Handler tiket: anggota departemen layanan tiket
        return User::with('role')
            ->whereHas('department', fn ($q) => $q->handlesTickets())
            ->orderBy('name');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Tiket berhasil dihapus.');
    }

    public function batchDelete(Request $request)
    {
        $this->authorize('deleteAny', Ticket::class);

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->route('tickets.index')->with('error', 'Tidak ada tiket yang dipilih.');
        }

        $count = Ticket::whereIn('id', $ids)->count();
        Ticket::whereIn('id', $ids)->delete();

        return redirect()->route('tickets.index')
            ->with('success', $count.' tiket berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\JobPlan;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $query = Ticket::with(['category', 'assignedUser', 'jobPlan'])
            ->orderByRaw("FIELD(status, 'open', 'in_progress', 'resolved', 'closed')")
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
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

        $tickets    = $query->paginate(15)->withQueryString();
        $categories = TicketCategory::active()->orderBy('name')->get();
        $openCount  = Ticket::where('status', 'open')->count();

        return view('tickets.index', compact('tickets', 'categories', 'openCount'));
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['category', 'jobSite', 'assignedUser', 'jobPlan.assignee', 'comments.user']);

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        if ($authUser->isAdmin() || $authUser->getRoleLevel() >= 5) {
            $eligibleHandlers = User::with('role')->orderBy('name')->get();
        } else {
            // Level 4: hanya user di departemennya sendiri
            $eligibleHandlers = User::with('role')
                ->where('department_id', $authUser->department_id)
                ->orderBy('name')
                ->get();
        }

        return view('tickets.show', compact('ticket', 'eligibleHandlers'));
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $this->authorize('assign', Ticket::class);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'due_date'    => 'required|date|after_or_equal:today',
        ], [
            'assigned_to.required'    => 'Anggota tim wajib dipilih.',
            'due_date.required'       => 'Tanggal target selesai wajib diisi.',
            'due_date.after_or_equal' => 'Tanggal tidak boleh sebelum hari ini.',
        ]);

        if ($ticket->status !== 'open') {
            return redirect()->back()->with('error', 'Tiket ini sudah diproses sebelumnya.');
        }

        $assignedUser = User::findOrFail($validated['assigned_to']);
        $assignerUser = Auth::user();

        DB::transaction(function () use ($ticket, $assignedUser, $assignerUser, $validated) {
            $jobPlan = JobPlan::create([
                'creator_id'   => $assignerUser->id,       // A: yang assign = pembuat job plan
                'assignee_id'  => $assignedUser->id,       // C: yang di-assign → Tugas Masuk (Assigned to Me)
                'department_id' => $assignedUser->department_id,
                'job_site_id'  => $ticket->job_site_id,
                'job_name'     => '[' . $ticket->ticket_number . '] ' . $ticket->title,
                'description'  => $ticket->description,
                'remark'       => 'Dari Tiket ' . $ticket->ticket_number
                    . ' — Pemohon: ' . $ticket->requester_name
                    . ' (' . $ticket->requester_department . ')',
                'planned_date' => today(),
                'due_date'     => $validated['due_date'],
                'status'       => 'assigned',
            ]);

            $ticket->update([
                'assigned_to' => $assignedUser->id,
                'status'      => 'in_progress',
                'job_plan_id' => $jobPlan->id,
            ]);
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Tiket berhasil di-assign. Job plan otomatis masuk ke Tugas Masuk milik ' . $assignedUser->name . '.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $this->authorize('updateStatus', $ticket);

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'resolved' && ! $ticket->resolved_at) {
            $updates['resolved_at'] = now();
        }

        $ticket->update($updates);

        return redirect()->back()->with('success', 'Status tiket berhasil diperbarui.');
    }

    public function addComment(Request $request, Ticket $ticket)
    {
        $this->authorize('addComment', $ticket);

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'comment'   => $validated['comment'],
        ]);

        return redirect()->back()->with('success', 'Komentar berhasil ditambahkan.');
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
            ->with('success', $count . ' tiket berhasil dihapus.');
    }
}

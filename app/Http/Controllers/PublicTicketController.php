<?php

namespace App\Http\Controllers;

use App\Models\JobSite;
use App\Models\Ticket;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicTicketController extends Controller
{
    public function showForm()
    {
        $categories = TicketCategory::active()->orderBy('name')->get();
        $jobSites   = JobSite::where('is_active', true)->orderBy('name')->get();

        return view('tickets.public-form', compact('categories', 'jobSites'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'requester_name'        => 'required|string|max:255',
            'requester_employee_id' => 'nullable|string|max:50',
            'requester_department'  => 'required|string|max:255',
            'requester_phone'       => 'nullable|string|max:30',
            'job_site_id'           => 'nullable|exists:job_sites,id',
            'category_id'           => 'required|exists:ticket_categories,id',
            'title'                 => 'required|string|max:255',
            'description'           => 'required|string',
            'priority'              => 'required|in:low,medium,high,urgent',
            'attachment'            => 'nullable|file|max:5120|mimes:jpeg,jpg,png,gif,pdf,doc,docx,xls,xlsx',
        ], [
            'requester_name.required'       => 'Nama pemohon wajib diisi.',
            'requester_department.required' => 'Departemen wajib diisi.',
            'category_id.required'          => 'Kategori permintaan wajib dipilih.',
            'title.required'                => 'Judul permintaan wajib diisi.',
            'description.required'          => 'Deskripsi detail wajib diisi.',
            'priority.required'             => 'Prioritas wajib dipilih.',
            'attachment.max'                => 'Ukuran file maksimal 5 MB.',
            'attachment.mimes'              => 'Format file tidak didukung.',
        ]);

        $attachmentPath         = null;
        $attachmentOriginalName = null;

        if ($request->hasFile('attachment')) {
            $file                   = $request->file('attachment');
            $attachmentOriginalName = $file->getClientOriginalName();
            $filename               = 'ticket_' . uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $attachmentPath         = $file->storeAs('attachments', $filename, 'public');
        }

        $ticket = Ticket::create([
            'ticket_number'          => Ticket::generateTicketNumber(),
            'requester_name'         => $validated['requester_name'],
            'requester_employee_id'  => $validated['requester_employee_id'] ?? null,
            'requester_department'   => $validated['requester_department'],
            'requester_phone'        => $validated['requester_phone'] ?? null,
            'job_site_id'            => $validated['job_site_id'] ?? null,
            'category_id'            => $validated['category_id'],
            'title'                  => $validated['title'],
            'description'            => $validated['description'],
            'priority'               => $validated['priority'],
            'attachment_path'        => $attachmentPath,
            'attachment_original_name' => $attachmentOriginalName,
        ]);

        return redirect()->route('tickets.public.success', ['ticket' => $ticket->ticket_number]);
    }

    public function success(Request $request)
    {
        $ticketNumber = $request->query('ticket');
        $ticket = Ticket::where('ticket_number', $ticketNumber)
            ->with('category')
            ->first();

        return view('tickets.public-success', compact('ticket', 'ticketNumber'));
    }
}

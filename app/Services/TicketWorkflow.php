<?php

namespace App\Services;

use App\Exceptions\TicketWorkflowException;
use App\Models\DailyReport;
use App\Models\JobPlan;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketStatusHistory;
use App\Models\User;
use App\Policies\TicketPolicy;
use App\Support\WorkingCalendar;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Satu-satunya tempat perubahan status tiket. Setiap transisi divalidasi,
 * dicatat di riwayat, dan memicu notifikasi ke pihak terkait.
 *
 * open → assigned → in_progress ⇄ pending → resolved → closed
 *                                            resolved → in_progress (reopen oleh pemohon)
 * cancelled dapat dicapai dari status aktif mana pun sebelum resolved.
 */
class TicketWorkflow
{
    public function __construct(private TicketSla $sla) {}

    /** Transisi yang boleh dilakukan petugas IT lewat panel "Ubah Status" */
    const STAFF_TRANSITIONS = [
        Ticket::STATUS_OPEN => [Ticket::STATUS_CANCELLED],
        Ticket::STATUS_ASSIGNED => [Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_PENDING, Ticket::STATUS_RESOLVED, Ticket::STATUS_CANCELLED],
        Ticket::STATUS_IN_PROGRESS => [Ticket::STATUS_PENDING, Ticket::STATUS_RESOLVED, Ticket::STATUS_CANCELLED],
        Ticket::STATUS_PENDING => [Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_RESOLVED, Ticket::STATUS_CANCELLED],
    ];

    const NOTE_REQUIRED = [Ticket::STATUS_PENDING, Ticket::STATUS_RESOLVED, Ticket::STATUS_CANCELLED];

    /** Pemohon hanya boleh membatalkan sebelum tiket mulai dikerjakan */
    const REQUESTER_CANCELLABLE = [Ticket::STATUS_OPEN, Ticket::STATUS_ASSIGNED];

    public static function staffTransitions(Ticket $ticket): array
    {
        return self::STAFF_TRANSITIONS[$ticket->status] ?? [];
    }

    public static function canBeAssigned(Ticket $ticket): bool
    {
        return ! $ticket->awaitingApproval() && in_array($ticket->status, [
            Ticket::STATUS_OPEN, Ticket::STATUS_ASSIGNED, Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_PENDING,
        ], true);
    }

    public function recordCreated(Ticket $ticket): void
    {
        $this->record($ticket, null, Ticket::STATUS_OPEN, $ticket->requesterUser, $ticket->requester_name);
    }

    /**
     * Assign atau alihkan (reassign) tiket ke teknisi. Job Plan yang masih aktif ikut dialihkan.
     */
    public function assign(Ticket $ticket, User $handler, string $dueDate, User $actor): void
    {
        if ($ticket->awaitingApproval()) {
            throw new TicketWorkflowException('Tiket belum bisa di-assign: persetujuan atasan belum diverifikasi.');
        }
        if (! self::canBeAssigned($ticket)) {
            throw new TicketWorkflowException('Tiket dengan status "'.$ticket->status_label.'" tidak dapat di-assign.');
        }

        DB::transaction(function () use ($ticket, $handler, $dueDate, $actor) {
            $isReassign = $ticket->assigned_to !== null;
            $jobPlan = $ticket->jobPlan;

            if ($jobPlan && $jobPlan->status === 'assigned') {
                $jobPlan->update([
                    'assignee_id' => $handler->id,
                    'department_id' => $handler->department_id,
                    'due_date' => $dueDate,
                ]);
            } else {
                $jobPlan = JobPlan::create([
                    'creator_id' => $actor->id,
                    'assignee_id' => $handler->id,
                    'department_id' => $handler->department_id,
                    'job_site_id' => $ticket->job_site_id,
                    'job_name' => '['.$ticket->ticket_number.'] '.$ticket->title,
                    'description' => $ticket->description,
                    'remark' => 'Dari Tiket '.$ticket->ticket_number
                        .' — Pemohon: '.$ticket->requester_name.' ('.$ticket->requester_department.')',
                    'planned_date' => today(),
                    'due_date' => $dueDate,
                    'status' => 'assigned',
                ]);
            }

            $from = $ticket->status;
            $ticket->fill([
                'assigned_to' => $handler->id,
                'assigned_at' => now(),
                'job_plan_id' => $jobPlan->id,
                'status' => $from === Ticket::STATUS_OPEN ? Ticket::STATUS_ASSIGNED : $from,
            ]);
            $this->sla->markResponded($ticket);
            $ticket->save();
            $ticket->setRelation('assignedUser', $handler);

            $this->record($ticket, $from, $ticket->status, $actor, $actor->name,
                ($isReassign ? 'Dialihkan ke ' : 'Ditugaskan ke ').$handler->name);

            $this->notify($handler, $ticket, 'Tiket ['.$ticket->ticket_number.'] ditugaskan kepada Anda: '.$ticket->title, $actor);
            $this->notify($ticket->requesterUser, $ticket, 'Tiket ['.$ticket->ticket_number.'] sedang ditangani oleh '.$handler->name.'.', $actor);
        });
    }

    /**
     * Perubahan status oleh petugas IT (handler / triager).
     */
    public function changeStatus(Ticket $ticket, string $to, User $actor, ?string $note = null): void
    {
        if (! in_array($to, self::staffTransitions($ticket), true)) {
            throw new TicketWorkflowException('Perubahan status dari "'.$ticket->status_label.'" ke "'
                .(Ticket::STATUSES[$to]['label'] ?? $to).'" tidak diizinkan.');
        }

        $this->requireNote($to, $note);

        $updates = ['status' => $to];
        if ($to === Ticket::STATUS_RESOLVED) {
            $updates += ['resolved_at' => now(), 'resolution_note' => $note];
        }
        if ($to === Ticket::STATUS_CANCELLED) {
            $updates += ['cancelled_at' => now()];
        }

        $this->sla->markResponded($ticket);
        $this->transition($ticket, $updates, $actor, $actor->name, $note);

        $message = match ($to) {
            Ticket::STATUS_PENDING => 'Tiket ['.$ticket->ticket_number.'] menunggu informasi dari Anda: '.$note,
            Ticket::STATUS_RESOLVED => 'Tiket ['.$ticket->ticket_number.'] telah diselesaikan. Mohon konfirmasi apakah masalah sudah teratasi.',
            Ticket::STATUS_CANCELLED => 'Tiket ['.$ticket->ticket_number.'] dibatalkan: '.$note,
            default => 'Status tiket ['.$ticket->ticket_number.'] berubah menjadi '.$ticket->status_label.'.',
        };
        $this->notify($ticket->requesterUser, $ticket, $message, $actor);
        $this->notify($ticket->assignedUser, $ticket, $message, $actor);
    }

    /**
     * Teknisi mengirim laporan dari Job Plan tiket (belum disetujui): tiket "Ditugaskan" → "Dikerjakan".
     * Tiket baru "Selesai" setelah laporannya DISETUJUI (resolveFromReport).
     */
    public function markWorkReported(Ticket $ticket, JobPlan $jobPlan): void
    {
        if ($ticket->status !== Ticket::STATUS_ASSIGNED) {
            return;
        }

        $actor = $jobPlan->assignee;
        $this->transition($ticket, ['status' => Ticket::STATUS_IN_PROGRESS], $actor, $actor?->name ?? 'Sistem',
            'Laporan pekerjaan dikirim, menunggu persetujuan atasan.');
    }

    /**
     * Laporan harian dari Job Plan tiket disetujui → tiket "Selesai" dengan catatan penyelesaian dari isi laporan.
     */
    public function resolveFromReport(Ticket $ticket, DailyReport $report): void
    {
        if (! in_array(Ticket::STATUS_RESOLVED, self::staffTransitions($ticket), true)) {
            return;
        }

        // Laporan tanpa PIC disetujui saat dibuat, sebelum controller menandai Job Plan → tandai di sini
        $jobPlan = $ticket->jobPlan;
        if ($jobPlan?->isActive()) {
            $jobPlan->forceFill(['status' => 'converted', 'converted_at' => now()])->saveQuietly();
        }

        $actor = $report->user;
        $note = trim($report->description.($report->remark ? "\n\nCatatan: ".$report->remark : ''));
        $this->sla->markResponded($ticket);

        $this->transition($ticket, [
            'status' => Ticket::STATUS_RESOLVED,
            'resolved_at' => now(),
            'resolution_note' => $note,
        ], $actor, $actor?->name ?? 'Sistem', 'Diselesaikan melalui laporan harian "'.$report->job_name.'" (disetujui).');

        $this->notify($ticket->requesterUser, $ticket,
            'Tiket ['.$ticket->ticket_number.'] telah diselesaikan. Mohon konfirmasi apakah masalah sudah teratasi.', $actor);
    }

    public function confirmResolved(Ticket $ticket, ?User $actor, string $actorName): void
    {
        $this->requireStatus($ticket, Ticket::STATUS_RESOLVED, 'Hanya tiket berstatus selesai yang dapat dikonfirmasi.');

        $this->transition($ticket, [
            'status' => Ticket::STATUS_CLOSED,
            'closed_at' => now(),
            'close_method' => 'requester',
        ], $actor, $actorName, 'Pemohon mengonfirmasi masalah sudah teratasi.');

        $this->notify($ticket->assignedUser, $ticket, 'Pemohon mengonfirmasi tiket ['.$ticket->ticket_number.'] selesai. Tiket ditutup.', $actor);
    }

    public function reopen(Ticket $ticket, ?User $actor, string $actorName, ?string $reason): void
    {
        $this->requireStatus($ticket, Ticket::STATUS_RESOLVED, 'Hanya tiket berstatus selesai yang dapat dibuka kembali.');
        $this->requireText($reason, 'Jelaskan bagian mana yang belum selesai.');

        $this->transition($ticket, [
            'status' => Ticket::STATUS_IN_PROGRESS,
            'resolved_at' => null,
            'reopen_count' => $ticket->reopen_count + 1,
        ], $actor, $actorName, $reason);

        $this->createFollowUpPlan($ticket, $reason);

        $this->notify($ticket->assignedUser, $ticket,
            'Tiket ['.$ticket->ticket_number.'] dibuka kembali oleh pemohon: '.$reason, $actor);
    }

    public function cancelByRequester(Ticket $ticket, ?User $actor, string $actorName, ?string $reason): void
    {
        if (! in_array($ticket->status, self::REQUESTER_CANCELLABLE, true)) {
            throw new TicketWorkflowException('Tiket yang sudah mulai dikerjakan tidak dapat dibatalkan. Silakan hubungi tim IT melalui komentar.');
        }
        $this->requireText($reason, 'Alasan pembatalan wajib diisi.');

        $this->transition($ticket, [
            'status' => Ticket::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ], $actor, $actorName, $reason);

        $this->notify($ticket->assignedUser, $ticket, 'Tiket ['.$ticket->ticket_number.'] dibatalkan oleh pemohon: '.$reason, $actor);
    }

    /**
     * Pemohon mengunggah foto/scan formulir persetujuan yang sudah ditandatangani atasan.
     */
    public function uploadApprovalProof(Ticket $ticket, UploadedFile $file, ?User $actor, string $actorName): void
    {
        if (! $ticket->canUploadApproval()) {
            throw new TicketWorkflowException('Bukti persetujuan tidak dapat diunggah pada status tiket saat ini.');
        }

        $filename = 'approval_'.$ticket->id.'_'.uniqid().'.'.($file->extension() ?: 'bin');
        $path = $file->storeAs('approvals', $filename, 'attachments');
        $oldPath = $ticket->approval_proof_path;

        DB::transaction(function () use ($ticket, $file, $path, $actor, $actorName) {
            $ticket->update([
                'approval_status' => Ticket::APPROVAL_WAITING_VERIFICATION,
                'approval_proof_path' => $path,
                'approval_proof_original_name' => $file->getClientOriginalName(),
                'approval_uploaded_at' => now(),
            ]);
            $this->record($ticket, $ticket->status, $ticket->status, $actor, $actorName,
                'Bukti persetujuan atasan diunggah, menunggu verifikasi tim IT.');
        });

        if ($oldPath && $oldPath !== $path) {
            Storage::disk('attachments')->delete($oldPath);
        }

        User::with(['role', 'department'])->get()
            ->filter(fn (User $u) => TicketPolicy::isTriager($u))
            ->each(fn (User $u) => $this->notify($u, $ticket,
                'Bukti persetujuan atasan tiket ['.$ticket->ticket_number.'] perlu diverifikasi.', $actor));
    }

    /**
     * Tim IT (level 4 ke atas) memverifikasi bukti persetujuan. Disetujui → jam SLA mulai berjalan.
     */
    public function verifyApproval(Ticket $ticket, bool $approved, User $actor, ?string $note = null): void
    {
        if ($ticket->approval_status !== Ticket::APPROVAL_WAITING_VERIFICATION) {
            throw new TicketWorkflowException('Tidak ada bukti persetujuan yang menunggu verifikasi.');
        }
        if (! $approved) {
            $this->requireText($note, 'Alasan penolakan bukti persetujuan wajib diisi.');
        }

        DB::transaction(function () use ($ticket, $approved, $actor, $note) {
            $ticket->fill([
                'approval_status' => $approved ? Ticket::APPROVAL_APPROVED : Ticket::APPROVAL_REJECTED,
                'approval_verified_by' => $actor->id,
                'approval_verified_at' => now(),
                'approval_note' => $note,
            ]);
            if ($approved) {
                $this->sla->resume($ticket);
            }
            $ticket->save();

            $this->record($ticket, $ticket->status, $ticket->status, $actor, $actor->name, $approved
                ? 'Persetujuan atasan diverifikasi. Tiket siap ditangani.'.($note ? ' '.$note : '')
                : 'Bukti persetujuan ditolak: '.$note);
        });

        $this->notify($ticket->requesterUser, $ticket, $approved
            ? 'Persetujuan atasan untuk tiket ['.$ticket->ticket_number.'] telah diverifikasi. Tiket akan segera ditangani.'
            : 'Bukti persetujuan tiket ['.$ticket->ticket_number.'] ditolak: '.$note.'. Silakan unggah ulang.', $actor);
    }

    /**
     * Survei kepuasan pemohon (1–5). Nilai rendah (≤ 2) langsung diberitahukan ke tim IT.
     */
    public function submitCsat(Ticket $ticket, int $rating, ?string $comment, ?User $actor, string $actorName): void
    {
        if (! $ticket->canSubmitCsat()) {
            throw new TicketWorkflowException('Penilaian hanya bisa diberikan sekali setelah tiket ditutup.');
        }
        if ($rating < 1 || $rating > 5) {
            throw new TicketWorkflowException('Nilai harus antara 1 sampai 5.');
        }

        $ticket->update([
            'csat_rating' => $rating,
            'csat_comment' => $comment ?: null,
            'csat_submitted_at' => now(),
        ]);

        if ($rating <= 2) {
            $message = 'Penilaian rendah ('.$rating.'/5) untuk tiket ['.$ticket->ticket_number.'] dari '.$actorName
                .($comment ? ': '.str($comment)->limit(80) : '.');

            User::with(['role', 'department'])->get()
                ->filter(fn (User $u) => $u->id === $ticket->assigned_to || TicketPolicy::isTriager($u))
                ->each(fn (User $u) => $this->notify($u, $ticket, $message, $actor));
        }
    }

    /**
     * Koreksi prioritas oleh triager (mis. pemohon salah menilai dampak). Tenggat SLA dihitung ulang.
     */
    public function changePriority(Ticket $ticket, string $priority, User $actor, ?string $reason): void
    {
        if (! array_key_exists($priority, Ticket::PRIORITIES)) {
            throw new TicketWorkflowException('Prioritas tidak valid.');
        }
        if ($priority === $ticket->priority) {
            throw new TicketWorkflowException('Prioritas tidak berubah.');
        }
        if (! $ticket->isActive()) {
            throw new TicketWorkflowException('Prioritas tiket yang sudah ditutup tidak dapat diubah.');
        }
        $this->requireText($reason, 'Alasan perubahan prioritas wajib diisi.');

        DB::transaction(function () use ($ticket, $priority, $actor, $reason) {
            $old = $ticket->priority_label;
            $ticket->priority = $priority;
            $this->sla->calculateDueDates($ticket);
            $ticket->save();

            $this->record($ticket, $ticket->status, $ticket->status, $actor, $actor->name,
                'Prioritas diubah dari '.$old.' menjadi '.$ticket->priority_label.': '.$reason);
        });

        $this->notify($ticket->assignedUser, $ticket,
            'Prioritas tiket ['.$ticket->ticket_number.'] diubah menjadi '.$ticket->priority_label.'.', $actor);
    }

    /**
     * Notifikasi pelanggaran SLA ke teknisi yang menangani + triager.
     */
    public function notifySlaBreach(Ticket $ticket, string $kind): void
    {
        $label = $kind === 'response' ? 'respons' : 'penyelesaian';
        $message = 'SLA '.$label.' tiket ['.$ticket->ticket_number.'] terlewat ('.$ticket->priority_label.'): '.$ticket->title;

        User::with(['role', 'department'])->get()
            ->filter(fn (User $u) => $u->id === $ticket->assigned_to || TicketPolicy::isTriager($u))
            ->each(fn (User $user) => $this->notify($user, $ticket, $message, null));
    }

    /**
     * Respons pemohon (user login atau via form publik). Return pesan sukses untuk ditampilkan.
     */
    public function applyRequesterResponse(Ticket $ticket, string $action, ?string $reason, ?User $actor, string $actorName): string
    {
        return match ($action) {
            'confirm' => tap('Terima kasih, tiket telah ditutup.', fn () => $this->confirmResolved($ticket, $actor, $actorName)),
            'reopen' => tap('Tiket dibuka kembali dan akan ditindaklanjuti tim IT.', fn () => $this->reopen($ticket, $actor, $actorName, $reason)),
            'cancel' => tap('Tiket dibatalkan.', fn () => $this->cancelByRequester($ticket, $actor, $actorName, $reason)),
        };
    }

    /**
     * Tutup otomatis tiket "resolved" yang tidak dikonfirmasi pemohon dalam N hari kerja.
     */
    public function autoCloseResolved(): int
    {
        $days = (int) config('tickets.auto_close_working_days');
        $closed = 0;

        Ticket::where('status', Ticket::STATUS_RESOLVED)
            ->whereNotNull('resolved_at')
            ->get()
            ->filter(fn (Ticket $ticket) => WorkingCalendar::addWorkingDays($ticket->resolved_at, $days)->lessThanOrEqualTo(now()))
            ->each(function (Ticket $ticket) use ($days, &$closed) {
                $this->transition($ticket, [
                    'status' => Ticket::STATUS_CLOSED,
                    'closed_at' => now(),
                    'close_method' => 'auto',
                ], null, 'Sistem', "Ditutup otomatis: tidak ada konfirmasi pemohon dalam {$days} hari kerja.");
                $closed++;
            });

        return $closed;
    }

    /**
     * Komentar. Catatan internal hanya terlihat oleh tim IT; balasan publik terlihat oleh pemohon.
     */
    public function addComment(Ticket $ticket, string $body, ?User $author, string $authorName, bool $internal = false): TicketComment
    {
        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $author?->id,
            'author_name' => $author ? null : $authorName,
            'comment' => $body,
            'is_internal' => $internal,
        ]);

        $fromRequester = $author === null || $author->id === $ticket->requester_user_id;

        if (! $fromRequester && ! $internal && ! $ticket->first_responded_at) {
            $this->sla->markResponded($ticket);
            $ticket->save();
        }
        $message = $authorName.' mengomentari tiket ['.$ticket->ticket_number.']: '.str($body)->limit(80);

        if ($fromRequester) {
            $recipients = $ticket->assignedUser
                ? collect([$ticket->assignedUser])
                : User::with(['role', 'department'])->get()->filter(fn (User $u) => TicketPolicy::isTriager($u));
            $recipients->each(fn (User $user) => $this->notify($user, $ticket, $message, $author));
        } elseif (! $internal) {
            $this->notify($ticket->requesterUser, $ticket, $message, $author);
        }

        return $comment;
    }

    private function transition(Ticket $ticket, array $updates, ?User $actor, string $actorName, ?string $note): void
    {
        DB::transaction(function () use ($ticket, $updates, $actor, $actorName, $note) {
            $from = $ticket->status;
            $ticket->fill($updates);
            $this->sla->onStatusChange($ticket, $from, $ticket->status);
            $ticket->save();
            $this->record($ticket, $from, $ticket->status, $actor, $actorName, $note);
            $this->closeOpenJobPlan($ticket);
        });
    }

    /** Tiket berakhir tanpa lewat laporan (diselesaikan manual/dibatalkan/ditutup) → Job Plan aktifnya ikut ditutup */
    private function closeOpenJobPlan(Ticket $ticket): void
    {
        if (! in_array($ticket->status, [Ticket::STATUS_RESOLVED, Ticket::STATUS_CANCELLED, Ticket::STATUS_CLOSED], true)) {
            return;
        }

        $jobPlan = $ticket->jobPlan()->first();
        if ($jobPlan?->isActive()) {
            $jobPlan->forceFill(['status' => 'closed'])->saveQuietly();
        }
    }

    /** Tiket dibuka kembali: buat Job Plan lanjutan untuk teknisi yang sama (rencana lama sudah dikonversi/ditutup) */
    private function createFollowUpPlan(Ticket $ticket, ?string $reason): void
    {
        $handler = $ticket->assignedUser;
        if (! $handler || $ticket->jobPlan()->first()?->isActive()) {
            return;
        }

        $previous = $ticket->jobPlan()->first();
        $plan = JobPlan::create([
            'creator_id' => $previous?->creator_id ?? $handler->id,
            'assignee_id' => $handler->id,
            'department_id' => $handler->department_id,
            'job_site_id' => $ticket->job_site_id,
            'job_name' => '['.$ticket->ticket_number.'] (dibuka kembali) '.$ticket->title,
            'description' => $ticket->description,
            'remark' => 'Dibuka kembali oleh pemohon: '.$reason,
            'planned_date' => today(),
            'due_date' => today()->addDay(),
            'status' => 'assigned',
        ]);
        $ticket->forceFill(['job_plan_id' => $plan->id])->saveQuietly();
    }

    private function record(Ticket $ticket, ?string $from, string $to, ?User $actor, string $actorName, ?string $note = null): void
    {
        TicketStatusHistory::create([
            'ticket_id' => $ticket->id,
            'from_status' => $from,
            'to_status' => $to,
            'user_id' => $actor?->id,
            'actor_name' => $actorName,
            'note' => $note,
        ]);
    }

    private function notify(?User $recipient, Ticket $ticket, string $message, ?User $actor): void
    {
        if (! $recipient || $recipient->id === $actor?->id || ! $recipient->wantsNotification('ticket_updated')) {
            return;
        }

        Notification::create([
            'user_id' => $recipient->id,
            'ticket_id' => $ticket->id,
            'type' => 'ticket_updated',
            'message' => $message,
            'is_read' => false,
        ]);
    }

    private function requireStatus(Ticket $ticket, string $status, string $message): void
    {
        if ($ticket->status !== $status) {
            throw new TicketWorkflowException($message);
        }
    }

    private function requireNote(string $to, ?string $note): void
    {
        if (! in_array($to, self::NOTE_REQUIRED, true)) {
            return;
        }

        $this->requireText($note, match ($to) {
            Ticket::STATUS_PENDING => 'Jelaskan informasi apa yang ditunggu dari pemohon.',
            Ticket::STATUS_RESOLVED => 'Catatan penyelesaian (solusi) wajib diisi.',
            default => 'Alasan pembatalan wajib diisi.',
        });
    }

    private function requireText(?string $text, string $message): void
    {
        if (trim((string) $text) === '') {
            throw new TicketWorkflowException($message);
        }
    }
}

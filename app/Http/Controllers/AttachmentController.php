<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttachmentController extends Controller
{
    /**
     * MIME types that are safe to render inline in the browser.
     * SVG is intentionally excluded: it can carry script.
     */
    private const INLINE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'text/plain',
    ];

    /**
     * Serve a daily report attachment to users allowed to view the report.
     */
    public function report(string $filename): BinaryFileResponse
    {
        $filename = basename($filename);
        $path = 'attachments/' . $filename;

        $report = DailyReport::where('attachment_path', $path)
            ->orWhere('attachment_path_2', $path)
            ->orWhere('attachment_path_3', $path)
            ->first();

        if (!$report) {
            abort(404);
        }

        $this->authorize('view', $report);

        return $this->serve($path, $filename, ['report_id' => $report->id]);
    }

    /**
     * Serve a ticket attachment to users allowed to view the ticket.
     */
    public function ticket(Ticket $ticket): BinaryFileResponse
    {
        $this->authorize('view', $ticket);

        if (!$ticket->attachment_path) {
            abort(404);
        }

        $downloadName = $ticket->attachment_original_name ?: basename($ticket->attachment_path);

        return $this->serve($ticket->attachment_path, $downloadName, ['ticket_id' => $ticket->id]);
    }

    /**
     * Bukti persetujuan atasan (foto/scan formulir) — untuk user yang boleh melihat tiket.
     */
    public function ticketApproval(Ticket $ticket): BinaryFileResponse
    {
        $this->authorize('view', $ticket);

        abort_unless($ticket->approval_proof_path, 404);

        return $this->serve($ticket->approval_proof_path,
            $ticket->approval_proof_original_name ?: basename($ticket->approval_proof_path), ['ticket_id' => $ticket->id]);
    }

    /**
     * Otorisasi dilakukan pemanggil (mis. sesi cek status tiket publik).
     */
    public function serve(string $path, string $downloadName, array $logContext): BinaryFileResponse
    {
        $filePath = $this->resolvePath($path);
        if (!$filePath) {
            abort(404);
        }

        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $isSafeType = in_array($mimeType, self::INLINE_MIME_TYPES, true);

        if (!$isSafeType) {
            Log::warning('Unsafe file type downloaded', $logContext + [
                'path' => $path,
                'mime_type' => $mimeType,
                'user_id' => Auth::id(),
            ]);
        }

        $response = response()->file($filePath, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'; style-src 'unsafe-inline';",
        ]);

        $response->setContentDisposition($isSafeType ? 'inline' : 'attachment', $this->asciiName($downloadName));

        return $response;
    }

    /**
     * Files live on the private disk; files uploaded before the migration
     * may still sit on the public disk until `attachments:move-private` runs.
     */
    private function resolvePath(string $path): ?string
    {
        foreach (['attachments', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->path($path);
            }
        }

        return null;
    }

    private function asciiName(string $name): string
    {
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($name));

        return $name !== '' ? $name : 'attachment';
    }
}

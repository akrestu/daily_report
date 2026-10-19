<?php

namespace App\Support;

use App\Models\Ticket;

/**
 * Draf pesan WhatsApp ke pemohon tiket (template tanpa AI), menyesuaikan status tiket.
 * Dua nada: formal & ramah. Petugas masih bisa menyunting teks sebelum dikirim lewat wa.me.
 */
class TicketMessage
{
    public const TONES = ['formal' => 'Formal', 'ramah' => 'Ramah'];

    /** Nomor HP Indonesia → format internasional untuk wa.me (08xx / +628xx / 628xx → 628xx) */
    public static function whatsappNumber(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return strlen($digits) >= 10 ? $digits : null;
    }

    /** Link untuk pemohon: akun SiGAP → detail tiket (login); form publik → link akses langsung (tanpa ketik kode) */
    public static function requesterLink(Ticket $ticket): string
    {
        if ($ticket->requester_user_id || ! $ticket->tracking_token) {
            return route('tickets.show', $ticket);
        }

        return route('tickets.public.track.access', ['ticket' => $ticket->ticket_number, 'token' => $ticket->tracking_token]);
    }

    /** @return array<string, string> draf per nada */
    public static function drafts(Ticket $ticket): array
    {
        return collect(array_keys(self::TONES))
            ->mapWithKeys(fn (string $tone) => [$tone => self::compose($ticket, $tone)])
            ->all();
    }

    public static function compose(Ticket $ticket, string $tone = 'formal'): string
    {
        $formal = $tone !== 'ramah';
        $name = trim((string) $ticket->requester_name) ?: 'Bapak/Ibu';
        $ref = "*{$ticket->ticket_number}* — {$ticket->title}";
        $link = self::requesterLink($ticket);
        $handler = $ticket->assignedUser?->name;

        $greeting = $formal ? "Selamat ".self::partOfDay().", Bapak/Ibu {$name}." : "Halo {$name}, ".self::partOfDay()."!";

        $body = match ($ticket->status) {
            Ticket::STATUS_OPEN => $formal
                ? "Tiket Anda {$ref} telah kami terima dan sedang dalam antrean penanganan tim IT."
                : "Tiket kamu {$ref} sudah kami terima ya, sebentar lagi akan ditangani tim IT.",
            Ticket::STATUS_ASSIGNED, Ticket::STATUS_IN_PROGRESS => $formal
                ? "Tiket Anda {$ref} sedang ditangani".($handler ? " oleh {$handler}" : '')." dari tim IT. Kami akan mengabarkan kembali setelah selesai."
                : "Tiket kamu {$ref} lagi kami kerjakan".($handler ? " (oleh {$handler})" : '').". Nanti kami kabari lagi kalau sudah beres.",
            Ticket::STATUS_PENDING => $formal
                ? "Untuk melanjutkan penanganan tiket {$ref}, kami memerlukan informasi tambahan dari Bapak/Ibu:\n\n- (tuliskan informasi yang dibutuhkan)\n\nMohon balas melalui tautan berikut atau pesan ini."
                : "Untuk lanjut mengerjakan tiket {$ref}, kami butuh sedikit info tambahan nih:\n\n- (tuliskan informasi yang dibutuhkan)\n\nBisa dibalas lewat link di bawah atau langsung di chat ini ya.",
            Ticket::STATUS_RESOLVED => ($formal
                ? "Tiket Anda {$ref} telah selesai ditangani oleh tim IT."
                : "Kabar baik, tiket kamu {$ref} sudah selesai kami kerjakan.")
                .self::resolution($ticket, $formal)
                ."\n\n".($formal
                    ? "Mohon kesediaan Bapak/Ibu untuk memeriksa hasilnya, lalu *konfirmasi dan tutup tiket* melalui tautan berikut. Jika masih ada kendala, tiket dapat dibuka kembali dari halaman yang sama."
                    : "Tolong dicek dulu ya, lalu *konfirmasi & tutup tiketnya* lewat link di bawah. Kalau ternyata masih bermasalah, tiketnya bisa dibuka lagi dari halaman yang sama."),
            Ticket::STATUS_CLOSED => $formal
                ? "Tiket Anda {$ref} telah ditutup. Terima kasih atas konfirmasinya."
                : "Tiket kamu {$ref} sudah ditutup. Makasih ya sudah konfirmasi!",
            Ticket::STATUS_CANCELLED => $formal
                ? "Tiket Anda {$ref} telah dibatalkan. Silakan ajukan tiket baru apabila masih memerlukan bantuan."
                : "Tiket kamu {$ref} sudah dibatalkan. Kalau masih butuh bantuan, ajukan tiket baru aja ya.",
            default => $formal ? "Berikut informasi terkait tiket Anda {$ref}." : "Info soal tiket kamu {$ref}.",
        };

        $closing = $formal ? "Hormat kami,\nTim IT" : "Salam,\nTim IT";

        return "{$greeting}\n\n{$body}\n\n{$link}\n\n{$closing}";
    }

    private static function resolution(Ticket $ticket, bool $formal): string
    {
        $note = trim((string) $ticket->resolution_note);
        if ($note === '') {
            return '';
        }

        return "\n\n".($formal ? 'Penyelesaian:' : 'Yang sudah kami lakukan:')."\n{$note}";
    }

    private static function partOfDay(): string
    {
        $hour = (int) now()->format('G');

        return match (true) {
            $hour < 11 => 'pagi',
            $hour < 15 => 'siang',
            $hour < 18 => 'sore',
            default => 'malam',
        };
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Ticket;
use App\Models\User;
use App\Support\Format;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Pencarian global (Ctrl K): laporan, tiket, pengguna.
 * Kandidat diambil dengan LIKE lalu disaring lewat policy `view`, jadi aturan akses tetap satu sumber.
 */
class SearchController extends Controller
{
    private const PER_GROUP = 6;

    private const CANDIDATES = 40;

    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['query' => $q, 'groups' => []]);
        }

        $user = $request->user();
        $like = '%'.addcslashes($q, '%_\\').'%';

        $reports = DailyReport::with('user')
            ->where(fn ($w) => $w->where('job_name', 'like', $like)->orWhere('description', 'like', $like))
            ->latest()
            ->limit(self::CANDIDATES)
            ->get()
            ->filter(fn ($r) => $user->can('view', $r))
            ->take(self::PER_GROUP)
            ->map(fn ($r) => [
                'title' => $r->job_name,
                'meta' => collect([$r->user?->name, Format::date($r->report_date)])->filter()->implode(' · '),
                'url' => route('daily-reports.show', $r),
                'icon' => 'bi-file-earmark-text',
            ])->values();

        $tickets = Ticket::query()
            ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('ticket_number', 'like', $like))
            ->latest()
            ->limit(self::CANDIDATES)
            ->get()
            ->filter(fn ($t) => $user->can('view', $t))
            ->take(self::PER_GROUP)
            ->map(fn ($t) => [
                'title' => $t->title,
                'meta' => $t->ticket_number.' · '.config('sigap-ui.ticket_status.'.$t->status.'.label', $t->status),
                'url' => route('tickets.show', $t),
                'icon' => 'bi-ticket-detailed',
            ])->values();

        $users = collect();
        if ($user->isAdmin()) {
            $users = User::with('department')
                ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('user_id', 'like', $like)->orWhere('email', 'like', $like))
                ->orderBy('name')
                ->limit(self::PER_GROUP)
                ->get()
                ->map(fn ($u) => [
                    'title' => $u->name,
                    'meta' => collect([$u->user_id, $u->department?->name])->filter()->implode(' · '),
                    'url' => route('admin.users.edit', $u),
                    'icon' => 'bi-person',
                ]);
        }

        $groups = collect([
            ['label' => 'Laporan', 'items' => $reports],
            ['label' => 'Tiket', 'items' => $tickets],
            ['label' => 'Pengguna', 'items' => $users],
        ])->filter(fn ($g) => $g['items']->isNotEmpty())->values();

        return response()->json(['query' => $q, 'groups' => $groups]);
    }
}

{{-- Notifikasi — mockup: NotifMobile, NotifDesktop --}}
@php
    $types = [
        'job_approved' => ['bi-check-circle', 'approved'],
        'job_rejected' => ['bi-x-circle', 'rejected'],
        'pending_approval' => ['bi-hourglass-split', 'pending'],
        'new_comment' => ['bi-chat-left-text', 'info'],
    ];
    $target = function ($n) {
        if ($n->daily_report_id) {
            return route('daily-reports.show', $n->daily_report_id).($n->comment_id ? '#comment-'.$n->comment_id : '');
        }
        if ($n->job_plan_id && $n->jobPlan) {
            return route('job-plans.show', $n->job_plan_id);
        }
        if ($n->ticket_id) {
            return route('tickets.show', $n->ticket_id);
        }

        return $n->type === 'pending_approval' ? route('daily-reports.pending') : null;
    };
    $groups = $notifications->getCollection()->groupBy(fn ($n) => match (true) {
        $n->created_at->isToday() => 'Hari ini',
        $n->created_at->isYesterday() => 'Kemarin',
        default => 'Lebih lama',
    });
@endphp

<x-app-layout title="Notifikasi">
    <div class="mx-auto" style="max-width:760px" x-data x-on:sg-notifications-cleared.window="location.reload()">
        <x-sg.page-header title="Notifikasi" :subtitle="$unreadCount ? $unreadCount.' belum dibaca' : 'Semua sudah dibaca'">
            <x-slot:actions>
                @if ($unreadCount)
                    <a href="{{ route('notifications.view-all', ['mark_read' => 1]) }}" class="btn btn-outline-secondary"><i class="bi bi-check2-all" aria-hidden="true"></i> Tandai semua dibaca</a>
                @endif
                @if ($notifications->total())
                    <button type="button" class="btn btn-outline-secondary btn-icon" data-bs-toggle="modal" data-bs-target="#sg-notif-clear" aria-label="Hapus semua notifikasi">
                        <i class="bi bi-trash3" aria-hidden="true"></i>
                    </button>
                @endif
            </x-slot:actions>
        </x-sg.page-header>

        @forelse ($groups as $label => $items)
            <h2 class="sg-section-label mt-3">{{ $label }}</h2>
            <div class="card overflow-hidden mb-3">
                @foreach ($items as $n)
                    @php([$icon, $tone] = $types[$n->type] ?? ['bi-bell', 'info'])
                    @php($url = $target($n))
                    <a @if ($url) href="{{ $url }}" @else role="button" tabindex="0" @endif
                       @class(['sg-notif text-decoration-none', 'is-unread' => ! $n->is_read])
                       @if (! $n->is_read) @click="$store.notif.markRead({{ $n->id }}); $el.classList.remove('is-unread')" @endif>
                        <span class="sg-notif__icon sg-notif__icon--{{ $tone }}"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
                        <span class="min-w-0 flex-grow-1">
                            <span class="d-block">{{ $n->message }}</span>
                            <span class="sg-notif__time">{{ $n->created_at->diffForHumans() }} · {{ \App\Support\Format::datetime($n->created_at) }}</span>
                        </span>
                        @if (! $n->is_read)<span class="sg-unread-dot" aria-label="Belum dibaca"></span>@endif
                    </a>
                @endforeach
            </div>
        @empty
            <x-sg.empty icon="bi-bell-slash" title="Belum ada notifikasi" text="Kami akan memberi tahu saat ada laporan, persetujuan, atau tiket yang perlu Anda lihat." />
        @endforelse

        <div class="mt-3">{{ $notifications->links() }}</div>
    </div>
</x-app-layout>

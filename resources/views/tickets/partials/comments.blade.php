{{-- Daftar komentar tiket. $comments sudah difilter oleh controller (internal hanya untuk tim IT). --}}
@forelse($comments as $comment)
    <div class="d-flex gap-2 mb-3">
        <div class="flex-shrink-0">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle text-white {{ $comment->user_id ? 'bg-primary' : 'bg-success' }}"
                style="width:36px;height:36px;font-size:.8rem;font-weight:600">
                {{ strtoupper(substr($comment->author_display_name, 0, 1)) }}
            </div>
        </div>
        <div class="flex-grow-1">
            <div class="rounded p-2 {{ $comment->is_internal ? 'border border-warning' : 'bg-light' }}"
                style="{{ $comment->is_internal ? 'background:#fff8e1' : '' }}">
                <div class="fw-semibold small">
                    {{ $comment->author_display_name }}
                    @if($comment->is_internal)
                        <span class="badge bg-warning text-dark ms-1"><i class="bi bi-lock me-1"></i>Internal</span>
                    @endif
                    <span class="text-muted fw-normal">&middot; {{ $comment->created_at->diffForHumans() }}</span>
                </div>
                <div class="small mt-1" style="white-space:pre-wrap">{{ $comment->comment }}</div>
            </div>
        </div>
    </div>
@empty
    <p class="text-muted small text-center py-3 mb-0">Belum ada komentar.</p>
@endforelse

{{-- Daftar komentar tiket. $comments sudah difilter oleh controller (internal hanya untuk tim IT). --}}
@forelse($comments as $comment)
    <article class="sg-comment">
        <span class="sg-avatar" style="width:36px;height:36px;font-size:13px" aria-hidden="true">{{ mb_strtoupper(mb_substr($comment->author_display_name, 0, 1)) }}</span>
        <div class="min-w-0 flex-grow-1">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <strong>{{ $comment->author_display_name }}</strong>
                @if ($comment->is_internal)
                    <span class="sg-level" style="background:var(--sg-accent);color:var(--sg-on-accent)"><i class="bi bi-lock-fill me-1" aria-hidden="true"></i>Internal</span>
                @endif
                <span class="small text-muted">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <p @class(['sg-comment__body sg-prewrap mb-0', 'sg-internal-note' => $comment->is_internal])>{{ $comment->comment }}</p>
        </div>
    </article>
@empty
    <x-sg.empty icon="bi-chat-left-text" title="Belum ada komentar" class="py-3" />
@endforelse

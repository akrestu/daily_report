{{--
    Satu markup: bottom sheet di <992px, modal tengah di desktop (Bootstrap Modal API).
    Buka dengan data-bs-toggle="modal" data-bs-target="#{id}". Slot "footer" untuk tombol aksi.
--}}
@props(['id', 'title', 'size' => null])
<div {{ $attributes->class(['modal fade sg-sheet-modal']) }} id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}-title" aria-hidden="true">
    <div @class(['modal-dialog modal-dialog-centered modal-dialog-scrollable', "modal-{$size}" => $size])>
        <div class="modal-content">
            <div class="sg-sheet-handle d-lg-none" aria-hidden="true"></div>
            <div class="modal-header">
                <h2 class="modal-title" id="{{ $id }}-title">{{ $title }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">{{ $slot }}</div>
            @isset($footer)
                <div class="modal-footer">{{ $footer }}</div>
            @endisset
        </div>
    </div>
</div>

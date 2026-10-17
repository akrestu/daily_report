{{--
    Konfirmasi aksi destruktif. :confirmText diisi → pengguna wajib mengetik ulang teks itu (penghapusan massal).
    Form dikirim ke :action dengan :method. Slot = penjelasan tambahan / input tersembunyi.
--}}
@props(['id', 'title', 'text' => null, 'action', 'method' => 'DELETE', 'confirmText' => null, 'button' => 'Hapus'])
<x-sg.sheet :id="$id" :title="$title">
    <form method="POST" action="{{ $action }}" id="{{ $id }}-form" x-data="{ typed: '' }">
        @csrf
        @if (strtoupper($method) !== 'POST')
            @method($method)
        @endif
        <div class="d-flex gap-3">
            <div class="sg-empty__icon flex-none m-0" style="background:var(--sg-danger-bg);color:var(--sg-reject);width:48px;height:48px;font-size:22px" aria-hidden="true">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div>
                @if ($text)
                    <p class="mb-2">{{ $text }}</p>
                @endif
                {{ $slot }}
            </div>
        </div>
        @if ($confirmText)
            <label for="{{ $id }}-typed" class="form-label fw-semibold mt-3">Ketik <code class="text-danger">{{ $confirmText }}</code> untuk melanjutkan</label>
            <input type="text" id="{{ $id }}-typed" class="form-control" x-model="typed" autocomplete="off" autocapitalize="off" spellcheck="false">
        @endif
        <div class="d-flex gap-2 mt-4">
            <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-reject flex-fill"
                    @if ($confirmText) :disabled="typed !== @js($confirmText)" @endif>{{ $button }}</button>
        </div>
    </form>
</x-sg.sheet>

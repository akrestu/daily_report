{{-- Aksi tim IT pada tiket: assign/alihkan & ubah status. Di-include 2x (sheet mobile, panel desktop) → $sfx membedakan id. --}}
@if ($canAssign)
    <form action="{{ route('tickets.assign', $ticket) }}" method="POST" class="mb-4">
        @csrf
        <p class="sg-section-label">{{ $ticket->assigned_to ? 'Alihkan penanganan' : 'Tugaskan & buat Rencana Kerja' }}</p>
        @if ($errors->hasAny(['assigned_to', 'due_date']))
            <div class="alert alert-danger small py-2">@foreach ($errors->only(['assigned_to', 'due_date']) as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif
        <label for="assign-{{ $sfx }}" class="form-label fw-semibold">Anggota tim IT <span class="text-danger">*</span></label>
        <select id="assign-{{ $sfx }}" name="assigned_to" class="form-select mb-2" required>
            <option value="">Pilih anggota tim</option>
            @foreach ($eligibleHandlers as $handler)
                <option value="{{ $handler->id }}" @disabled($ticket->assigned_to === $handler->id)>
                    {{ $handler->name }} ({{ $handler->role->name ?? '-' }}){{ $ticket->assigned_to === $handler->id ? ' — saat ini' : '' }}
                </option>
            @endforeach
        </select>
        <label for="due-{{ $sfx }}" class="form-label fw-semibold">Target selesai <span class="text-danger">*</span></label>
        <input id="due-{{ $sfx }}" type="date" name="due_date" class="form-control mb-2" min="{{ today()->format('Y-m-d') }}" required
               value="{{ optional($ticket->jobPlan?->due_date)->format('Y-m-d') ?? today()->addDays(3)->format('Y-m-d') }}">
        <p class="small text-muted">{{ $ticket->assigned_to ? 'Job Plan aktif dipindahkan ke anggota yang dipilih.' : 'Tiket diteruskan sebagai Job Plan ke anggota yang dipilih.' }}</p>
        <button type="submit" class="btn btn-primary w-100">{{ $ticket->assigned_to ? 'Alihkan' : 'Tugaskan & buat Rencana Kerja' }}</button>
    </form>
@endif

@if (count($transitions) > 0)
    <form action="{{ route('tickets.update-status', $ticket) }}" method="POST"
          x-data="{ status: @js(old('status', '')), hints: {
              pending: 'Wajib: jelaskan informasi apa yang ditunggu dari pemohon. Terlihat oleh pemohon.',
              resolved: 'Wajib: tuliskan solusi / tindakan yang dilakukan. Pemohon akan diminta konfirmasi.',
              cancelled: 'Wajib: alasan pembatalan. Terlihat oleh pemohon.' } }">
        @csrf
        <p class="sg-section-label">Ubah status</p>
        <div class="sg-chip-row mb-3" role="radiogroup" aria-label="Status baru">
            @foreach ($transitions as $status)
                <input type="radio" class="btn-check" name="status" id="st-{{ $status }}-{{ $sfx }}" value="{{ $status }}" x-model="status" required>
                <label class="sg-chip" for="st-{{ $status }}-{{ $sfx }}" :class="{ active: status === '{{ $status }}' }">{{ config('sigap-ui.ticket_status.'.$status.'.label', $status) }}</label>
            @endforeach
        </div>
        <label for="note-{{ $sfx }}" class="form-label fw-semibold">Catatan <span class="text-danger" x-show="hints[status]">*</span></label>
        <textarea id="note-{{ $sfx }}" name="note" rows="3" maxlength="2000" class="form-control" :required="!!hints[status]"
                  :placeholder="hints[status] ? 'Catatan (wajib)' : 'Catatan (opsional)'">{{ old('note') }}</textarea>
        <div class="form-text mb-2" x-text="hints[status] || ''"></div>
        <button type="submit" class="btn btn-primary w-100" :disabled="!status">Simpan status</button>
    </form>
@endif

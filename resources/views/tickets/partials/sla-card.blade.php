@php($summary = \App\Services\TicketSla::summary($ticket))
@php($labels = ['running' => ['Berjalan', 'text-primary'], 'breached' => ['Terlewat', 'text-danger'], 'paused' => ['Dijeda', 'text-secondary'], 'met' => ['Tercapai', 'text-success'], 'none' => ['-', 'text-muted']])
<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="fas fa-stopwatch text-danger me-2"></i>SLA</h6>
        <div class="small text-muted">Dihitung dalam jam kerja (Senin–Sabtu {{ config('tickets.working_hours.start') }}–{{ config('tickets.working_hours.end') }})</div>
    </div>
    <div class="card-body small">
        @if(! $ticket->resolution_due_at)
            <p class="text-muted mb-0">Tiket ini dibuat sebelum SLA diberlakukan.</p>
        @else
            @foreach(['response' => 'Respons', 'resolution' => 'Penyelesaian'] as $key => $title)
                @php($item = $summary[$key])
                <div class="{{ $loop->first ? 'mb-3' : '' }}">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">{{ $title }}</span>
                        <span class="fw-semibold {{ $labels[$item['state']][1] }}">{{ $labels[$item['state']][0] }}</span>
                    </div>
                    <div class="text-muted">Tenggat: {{ $item['due']?->format('d M Y H:i') ?? '-' }}</div>
                    @if($item['remaining'] !== null && in_array($item['state'], ['running', 'paused']))
                        <div class="text-muted">Sisa: {{ \App\Services\TicketSla::formatMinutes($item['remaining']) }} jam kerja</div>
                    @endif
                    @if($key === 'response' && $ticket->first_responded_at)
                        <div class="text-muted">Direspons: {{ $ticket->first_responded_at->format('d M Y H:i') }}</div>
                    @endif
                </div>
            @endforeach
        @endif

        @if($ticket->impact && $ticket->urgency)
            <hr class="my-2">
            <div class="text-muted">
                Dampak: {{ \App\Models\Ticket::IMPACTS[$ticket->impact] ?? '-' }}<br>
                Urgensi: {{ \App\Models\Ticket::URGENCIES[$ticket->urgency] ?? '-' }}
            </div>
        @endif

        @if(auth()->user()->can('assign', \App\Models\Ticket::class) && $ticket->isActive())
            <hr class="my-2">
            <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="collapse" data-bs-target="#priorityForm">
                <i class="fas fa-flag me-1"></i>Koreksi prioritas
            </button>
            <form action="{{ route('tickets.priority', $ticket) }}" method="POST" class="collapse mt-2" id="priorityForm">
                @csrf
                <select name="priority" class="form-select form-select-sm mb-2" required>
                    @foreach(\App\Models\Ticket::PRIORITIES as $value => $label)
                        <option value="{{ $value }}" {{ $ticket->priority === $value ? 'selected disabled' : '' }}>
                            {{ $label }}{{ $ticket->priority === $value ? ' (saat ini)' : '' }}
                        </option>
                    @endforeach
                </select>
                <input type="text" name="reason" class="form-control form-control-sm mb-2" required maxlength="500"
                    placeholder="Alasan (wajib)">
                <button type="submit" class="btn btn-outline-danger btn-sm w-100">Ubah Prioritas</button>
            </form>
        @endif
    </div>
</div>

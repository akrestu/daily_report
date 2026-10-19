{{-- Buat Tiket (pengguna login) — pemohon diambil dari akun; isian permintaan di tickets/partials/request-fields --}}
@php($user = auth()->user())
<x-app-layout title="Buat Tiket" :back="route('tickets.mine')">
    <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" novalidate x-data="{ busy: false }" @submit="busy = true">
        @csrf

        <x-sg.page-header title="Buat Tiket Layanan IT" class="d-none d-lg-flex" subtitle="Laporkan gangguan atau ajukan permintaan ke tim IT.">
            <x-slot:actions>
                <a href="{{ route('tickets.mine') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary" :disabled="busy" x-text="busy ? 'Mengirim…' : 'Kirim Tiket'">Kirim Tiket</button>
            </x-slot:actions>
        </x-sg.page-header>

        @if ($errors->any())
            <div class="alert alert-danger d-flex gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-octagon fs-5" aria-hidden="true"></i>
                <div><strong>Periksa kembali isian yang ditandai.</strong></div>
            </div>
        @endif

        <div class="row g-3 g-lg-4">
            <div class="col-lg-8">
                <section class="card mb-3" aria-labelledby="t-requester">
                    <div class="sg-entry__body border-0">
                        <h2 id="t-requester" class="sg-section-label">Pemohon</h2>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <x-sg.avatar :user="$user" :size="44" />
                            <div class="min-w-0">
                                <div class="fw-semibold sg-clamp-1">{{ $user->name }}</div>
                                <div class="small text-muted">{{ $user->department->name ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="t-phone" class="form-label fw-semibold">No. HP / WhatsApp</label>
                                <input id="t-phone" type="tel" inputmode="tel" name="requester_phone" maxlength="30" placeholder="Untuk follow-up (opsional)"
                                       class="form-control @error('requester_phone') is-invalid @enderror" value="{{ old('requester_phone') }}">
                                @error('requester_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @if ($jobSites->count() > 0)
                                <div class="col-md-6">
                                    <label for="t-site" class="form-label fw-semibold">Lokasi / site</label>
                                    <select id="t-site" name="job_site_id" class="form-select @error('job_site_id') is-invalid @enderror">
                                        <option value="">Pilih lokasi (opsional)</option>
                                        @foreach ($jobSites as $site)
                                            <option value="{{ $site->id }}" @selected(old('job_site_id', $user->job_site_id) == $site->id)>{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('job_site_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="card" aria-label="Detail permintaan">
                    <div class="sg-entry__body border-0">
                        @include('tickets.partials.request-fields')
                    </div>
                </section>
            </div>

            <aside class="col-lg-4 d-none d-lg-block">
                <section class="card p-3 sg-create-aside" aria-labelledby="t-flow">
                    <h2 id="t-flow" class="h6 fw-bold mb-3">Setelah dikirim</h2>
                    <x-sg.timeline :items="[
                        ['title' => 'Tiket masuk', 'meta' => 'Nomor tiket dikirim ke notifikasi Anda', 'tone' => 'approved', 'icon' => 'bi-inbox'],
                        ['title' => 'Ditangani tim IT', 'meta' => 'Target waktu sesuai prioritas', 'tone' => 'pending', 'icon' => 'bi-tools'],
                        ['title' => 'Konfirmasi selesai', 'meta' => 'Anda menilai hasil penanganan', 'tone' => 'pending', 'icon' => 'bi-star'],
                    ]" />
                </section>
            </aside>
        </div>

        <x-sg.sticky-actions>
            <a href="{{ route('tickets.mine') }}" class="btn btn-outline-secondary flex-fill">Batal</a>
            <button type="submit" class="btn btn-primary flex-fill" :disabled="busy" x-text="busy ? 'Mengirim…' : 'Kirim Tiket'">Kirim Tiket</button>
        </x-sg.sticky-actions>
    </form>
</x-app-layout>

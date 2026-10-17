<x-app-layout>
    <x-sg.flash />

    @php
        $reports = [
            ['title' => 'Penggantian seal hidraulik boom excavator EX-207', 'meta' => 'Maintenance · PIC Rina Wulandari', 'time' => '17 Okt, 14:30', 'status' => 'pending', 'progress' => 'in_progress'],
            ['title' => 'Inspeksi harian conveyor belt CV-03 dan pengecekan alignment idler sisi return', 'meta' => 'Plant · PIC Rina Wulandari', 'time' => '16 Okt, 16:05', 'status' => 'approved', 'progress' => 'completed'],
            ['title' => 'Kalibrasi timbangan jembatan (weighbridge) pos 2', 'meta' => 'HRGA · PIC Budi Santoso', 'time' => '15 Okt, 09:12', 'status' => 'rejected', 'progress' => 'pending'],
        ];
        $timeline = [
            ['title' => 'Dikirim', 'meta' => 'Andi Pratama · 17 Okt 2026, 14:30', 'tone' => 'approved', 'icon' => 'bi-check-lg'],
            ['title' => 'Ditolak oleh Budi Santoso', 'meta' => 'Level 4 · 17 Okt 2026, 15:02', 'tone' => 'rejected', 'icon' => 'bi-x-lg', 'note' => 'Foto sebelum/sesudah belum dilampirkan.'],
            ['title' => 'Menunggu Rina Wulandari', 'meta' => 'Supervisor Maintenance · Level 5', 'tone' => 'pending', 'icon' => 'bi-clock'],
        ];
    @endphp

    <div class="container-fluid py-3 px-3 px-lg-4" style="max-width:1280px">
        <x-sg.page-header title="Komponen SiGAP" subtitle="Katalog x-sg.* — cek di 390 / 768 / 1440 px, light & dark.">
            <x-slot:actions>
                <div class="btn-group" role="group" aria-label="Tema" x-data>
                    @foreach (['light' => 'Terang', 'dark' => 'Gelap', 'system' => 'Sistem'] as $key => $text)
                        <button type="button" class="btn btn-outline-secondary" :class="{ active: $store.theme.mode === '{{ $key }}' }"
                                @click="$store.theme.set('{{ $key }}')">{{ $text }}</button>
                    @endforeach
                </div>
            </x-slot:actions>
        </x-sg.page-header>

        <x-sg.brand-panel class="mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="small fw-semibold" style="color:var(--sg-accent)">Selamat siang,</div>
                    <h2 class="h3 fw-bold mb-1">{{ $user->name }}</h2>
                    <div class="opacity-75">3 laporan menunggu persetujuan Anda hari ini.</div>
                </div>
                <a href="#" class="btn btn-accent"><i class="bi bi-plus-lg" aria-hidden="true"></i> Buat Laporan</a>
            </div>
        </x-sg.brand-panel>

        <p class="sg-section-label">KPI</p>
        <div class="sg-kpi-grid mb-4">
            <x-sg.kpi label="Laporan bulan ini" value="{{ \App\Support\Format::number(1248) }}" note="Naik 12% dari bulan lalu" note-tone="approved" />
            <x-sg.kpi label="Menunggu approval" value="4" note="2 lebih dari 24 jam" note-tone="pending" />
            <x-sg.kpi label="Ditolak" value="1" note="Perlu diperbaiki" note-tone="rejected" />
            <x-sg.kpi label="Pemenuhan SLA" value="{{ \App\Support\Format::percent(91.43) }}" note="30 hari terakhir" />
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card p-3 p-lg-4 h-100">
                    <p class="sg-section-label">Status approval (pill)</p>
                    <div class="sg-chip-row mb-3">
                        @foreach (['pending', 'approved', 'rejected'] as $s)
                            <x-sg.badge-status :value="$s" />
                        @endforeach
                        @foreach (['open', 'in_progress', 'closed'] as $s)
                            <x-sg.badge-status type="ticket_status" :value="$s" />
                        @endforeach
                    </div>
                    <p class="sg-section-label">Progres pekerjaan (kotak + ikon)</p>
                    <div class="sg-chip-row mb-3">
                        @foreach (['pending', 'in_progress', 'completed'] as $s)
                            <x-sg.badge-progress :value="$s" />
                        @endforeach
                    </div>
                    <p class="sg-section-label">Prioritas &amp; SLA</p>
                    <div class="sg-chip-row mb-3">
                        @foreach (['urgent', 'high', 'medium', 'low'] as $p)
                            <x-sg.badge-priority :value="$p" code />
                        @endforeach
                    </div>
                    <div class="sg-chip-row mb-3">
                        <x-sg.badge-sla :minutes="340" />
                        <x-sg.badge-sla :minutes="18" />
                        <x-sg.badge-sla :minutes="-80" />
                        <x-sg.badge-sla paused />
                    </div>
                    <p class="sg-section-label">Level jabatan</p>
                    <div class="sg-chip-row">
                        @for ($l = 1; $l <= 8; $l++)
                            <x-sg.badge-level :level="$l" />
                        @endfor
                        <x-sg.badge-level :level="0" />
                        <x-sg.badge-level :user="$user" short />
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card p-3 p-lg-4 h-100">
                    <p class="sg-section-label">Tombol</p>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <button type="button" class="btn btn-primary">Kirim Laporan</button>
                        <button type="button" class="btn btn-outline-secondary">Simpan Draf</button>
                        <button type="button" class="btn btn-approve"><i class="bi bi-check-lg" aria-hidden="true"></i> Setujui</button>
                        <button type="button" class="btn btn-reject-outline"><i class="bi bi-x-lg" aria-hidden="true"></i> Tolak</button>
                        <button type="button" class="btn btn-quiet">Lihat semua</button>
                        <button type="button" class="btn btn-outline-secondary btn-icon" aria-label="Bagikan via WhatsApp"><i class="bi bi-whatsapp" aria-hidden="true"></i></button>
                    </div>
                    <p class="sg-section-label">Input</p>
                    <label for="ui-job" class="form-label fw-semibold">Nama Pekerjaan <span class="text-danger">*</span></label>
                    <input id="ui-job" class="form-control mb-3" value="Inspeksi conveyor CV-03">
                    <label for="ui-nik" class="form-label fw-semibold">NIK</label>
                    <input id="ui-nik" class="form-control is-invalid" inputmode="numeric" value="19870" aria-describedby="ui-nik-err">
                    <div id="ui-nik-err" class="invalid-feedback">NIK tidak ditemukan. Periksa kembali 16 digit.</div>
                    <div class="mt-3"><x-sg.search placeholder="Cari laporan, PIC, section…" /></div>
                </div>
            </div>
        </div>

        <p class="sg-section-label">Filter + daftar (kartu di mobile, tabel di desktop)</p>
        <x-sg.chip-filter class="mb-3" :items="[
            ['label' => 'Semua', 'href' => '#', 'active' => true, 'count' => 1248],
            ['label' => 'Menunggu', 'href' => '#', 'count' => 4],
            ['label' => 'Disetujui', 'href' => '#'],
            ['label' => 'Ditolak', 'href' => '#'],
        ]">
            <button type="button" class="sg-chip" data-bs-toggle="modal" data-bs-target="#ui-filter"><i class="bi bi-sliders" aria-hidden="true"></i> Filter</button>
        </x-sg.chip-filter>

        <div class="sg-list d-lg-none mb-4">
            @foreach ($reports as $i => $r)
                <x-sg.list-card href="#" :title="$r['title']" :time="$r['time']" :meta="$r['meta']" :selected="$i === 1">
                    <x-slot:badge><x-sg.badge-status :value="$r['status']" /></x-slot:badge>
                </x-sg.list-card>
            @endforeach
        </div>

        <x-sg.table class="d-none d-lg-block mb-4" caption="Contoh daftar laporan">
            <x-slot:bulk>
                <div class="sg-bulk-bar">1 dipilih
                    <button type="button" class="btn btn-sm btn-approve ms-auto">Setujui</button>
                    <button type="button" class="btn btn-sm btn-reject-outline">Tolak</button>
                </div>
            </x-slot:bulk>
            <x-slot:head>
                <tr>
                    <th scope="col" style="width:40px"><input class="form-check-input" type="checkbox" aria-label="Pilih semua"></th>
                    <th scope="col">Pekerjaan</th><th scope="col">Tanggal</th><th scope="col">Progres</th><th scope="col">Status</th>
                </tr>
            </x-slot:head>
            @foreach ($reports as $i => $r)
                <tr @class(['is-selected' => $i === 1])>
                    <td><input class="form-check-input" type="checkbox" @checked($i === 1) aria-label="Pilih baris"></td>
                    <td class="fw-semibold" style="max-width:420px"><div class="sg-clamp-1">{{ $r['title'] }}</div></td>
                    <td class="sg-num text-nowrap">{{ $r['time'] }} 2026</td>
                    <td><x-sg.badge-progress :value="$r['progress']" /></td>
                    <td><x-sg.badge-status :value="$r['status']" /></td>
                </tr>
            @endforeach
        </x-sg.table>

        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="card h-100">
                    <x-sg.tabs prefix="ui-detail" :items="['desc' => 'Deskripsi', 'remarks' => 'Remarks', 'comments' => ['Komentar', 4]]" />
                    <div class="tab-content p-3">
                        <div class="tab-pane fade show active" id="ui-detail-desc" role="tabpanel" aria-labelledby="ui-detail-desc-tab">Seal bocor di silinder boom, diganti dengan seal kit OEM.</div>
                        <div class="tab-pane fade" id="ui-detail-remarks" role="tabpanel" aria-labelledby="ui-detail-remarks-tab">Unit siap operasi pukul 16:00.</div>
                        <div class="tab-pane fade" id="ui-detail-comments" role="tabpanel" aria-labelledby="ui-detail-comments-tab">4 komentar.</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card p-3 h-100">
                    <p class="sg-section-label">Timeline approval</p>
                    <x-sg.timeline :items="$timeline" />
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card p-3 h-100">
                    <p class="sg-section-label">Sheet, konfirmasi, toast</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#ui-filter">Buka bottom sheet / modal</button>
                        <button type="button" class="btn btn-reject-outline" data-bs-toggle="modal" data-bs-target="#ui-danger">Hapus massal (ketik ulang)</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="sgToast('Laporan disetujui', { undo: () => sgToast('Persetujuan dibatalkan', { tone: 'info' }) })">Toast + Urungkan</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="sgToast('Gagal mengunggah lampiran', { tone: 'danger' })">Toast gagal</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card p-3 h-100">
                    <p class="sg-section-label">Uploader lampiran</p>
                    <x-sg.uploader name="ui_attachments" />
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card p-3 h-100">
                    <p class="sg-section-label">Avatar, empty state, skeleton</p>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <x-sg.avatar :user="$user" :size="44" /> <span class="fw-semibold">{{ $user->name }}</span>
                    </div>
                    <x-sg.empty icon="bi-journal-text" title="Belum ada laporan" text="Laporan yang Anda buat akan muncul di sini.">
                        <a href="#" class="btn btn-primary">Buat Laporan</a>
                    </x-sg.empty>
                    <x-sg.skeleton :count="2" />
                </div>
            </div>
        </div>

        <p class="sg-section-label">Skeleton KPI &amp; tabel</p>
        <x-sg.skeleton type="kpi" :count="4" class="mb-3" />
        <x-sg.skeleton type="table" :count="3" class="mb-5" />
    </div>

    <x-sg.sticky-actions>
        <button type="button" class="btn btn-reject-outline flex-fill">Tolak</button>
        <button type="button" class="btn btn-approve" style="flex:2">Setujui</button>
    </x-sg.sticky-actions>

    <x-sg.sheet id="ui-filter" title="Filter laporan">
        <p class="sg-section-label">Status</p>
        <div class="sg-chip-row mb-3">
            <button type="button" class="sg-chip active">Menunggu</button>
            <button type="button" class="sg-chip">Disetujui</button>
            <button type="button" class="sg-chip">Ditolak</button>
        </div>
        <label for="ui-from" class="form-label fw-semibold">Dari tanggal</label>
        <input type="date" id="ui-from" class="form-control">
        <x-slot:footer>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Reset</button>
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Terapkan</button>
        </x-slot:footer>
    </x-sg.sheet>

    <x-sg.confirm-danger id="ui-danger" title="Hapus 248 laporan?" action="#" confirm-text="HAPUS 248"
                         text="Laporan yang dihapus tidak dapat dikembalikan." button="Hapus permanen" />
</x-app-layout>

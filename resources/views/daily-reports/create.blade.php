{{-- Buat Laporan (multi-entri) — mockup: CreateMobile, CreatePicSheet, CreateDesktop --}}
@php
    $pics = collect($eligiblePics ?? [])->map(fn ($name, $id) => ['id' => (string) $id, 'name' => $name])->values();
    $defaultPic = (string) ($fromPlan?->creator_id ?? ($pics->count() === 1 ? $pics->first()['id'] : ''));

    $entries = old('reports');
    if (! $entries && $fromPlan) {
        $entries = [0 => [
            'job_plan_id' => $fromPlan->id,
            'job_name' => $fromPlan->job_name,
            'description' => $fromPlan->description,
            'remark' => $fromPlan->remark,
            'job_site_id' => $fromPlan->job_site_id,
            'section_id' => $fromPlan->section_id,
            'job_pic' => $fromPlan->creator_id,
            'report_date' => $fromPlan->planned_date?->format('Y-m-d'),
            'due_date' => $fromPlan->due_date?->format('Y-m-d'),
        ]];
    }
    $entryErrors = collect($errors->getMessages())->filter(fn ($m, $k) => str_starts_with($k, 'reports.'))->all();
    $config = [
        'entries' => $entries ?? [],
        'errors' => (object) $entryErrors,
        'pics' => $pics,
        'today' => now()->format('Y-m-d'),
        'defaultPic' => $defaultPic,
        'idempotencyKey' => (string) old('idempotency_key', ''),
    ];
@endphp

<x-app-layout title="Buat Laporan" :back="route('daily-reports.user-jobs')">
    <form action="{{ route('daily-reports.store-multiple') }}" method="POST" enctype="multipart/form-data"
          x-data="sgReportForm(@js($config))" @submit="submit($event)" data-done="{{ route('daily-reports.user-jobs') }}" novalidate>
        @csrf
        <input type="hidden" name="idempotency_key" :value="idempotencyKey">

        <x-sg.page-header title="Buat Laporan" class="d-none d-lg-flex"
                          subtitle="Isi detail pekerjaan hari ini. Semua laporan dikirim sekaligus ke PIC masing-masing.">
            <x-slot:actions>
                <a href="{{ route('daily-reports.user-jobs') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary" :disabled="busy">
                    <span x-text="busy ? 'Mengirim…' : 'Kirim ' + count + ' Laporan'">Kirim Laporan</span>
                </button>
            </x-slot:actions>
        </x-sg.page-header>

        {{-- Antrian offline milik pengguna ini --}}
        <div class="sg-outbox mb-3" x-show="$store.outbox.count" x-cloak role="status">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-cloud-arrow-up fs-5" aria-hidden="true"></i>
                <strong class="me-auto" x-text="$store.outbox.count + ' laporan menunggu dikirim'"></strong>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="$store.outbox.flush()" :disabled="$store.outbox.sending || !navigator.onLine"
                        x-text="$store.outbox.sending ? 'Mengirim…' : 'Kirim sekarang'">Kirim sekarang</button>
            </div>
            <p class="small text-muted mb-2">Dikirim otomatis saat perangkat kembali online.</p>
            <template x-for="item in $store.outbox.items" :key="item.id">
                <div class="sg-outbox__item">
                    <div class="min-w-0 flex-grow-1">
                        <div class="small fw-semibold sg-clamp-1" x-text="item.summary"></div>
                        <div class="small text-danger" x-show="item.error" x-text="'Gagal: ' + item.error"></div>
                    </div>
                    <button type="button" class="btn btn-sm btn-quiet text-danger" @click="$store.outbox.discard(item.id)" :aria-label="'Buang ' + item.summary">Buang</button>
                </div>
            </template>
        </div>

        {{-- Draf otomatis --}}
        <div x-show="draft" x-cloak>
            <div class="sg-outbox mb-3 d-flex flex-wrap align-items-center gap-2">
                <i class="bi bi-journal-text fs-5" aria-hidden="true"></i>
                <div class="me-auto min-w-0">
                    <strong class="d-block">Ada draf yang belum dikirim</strong>
                    <span class="small text-muted" x-text="draftLabel"></span>
                </div>
                <button type="button" class="btn btn-sm btn-quiet" @click="discardDraft()">Buang</button>
                <button type="button" class="btn btn-sm btn-primary" @click="restoreDraft()">Pulihkan</button>
            </div>
        </div>

        @if ($fromPlan)
            <div class="sg-internal-note p-3 mb-3 d-flex gap-2">
                <i class="bi bi-clipboard-check fs-5" aria-hidden="true"></i>
                <div class="small">Diisi otomatis dari rencana kerja <strong>{{ $fromPlan->job_name }}</strong> yang ditugaskan
                    <strong>{{ $fromPlan->creator?->name }}</strong>. Periksa sebelum mengirim.</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger d-flex gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-octagon fs-5" aria-hidden="true"></i>
                <div><strong>Periksa kembali isian yang ditandai.</strong>
                    <div class="small">Lampiran perlu dipilih ulang setelah validasi gagal.</div></div>
            </div>
        @endif

        <div class="row g-3 g-lg-4">
            <div class="col-lg-8">
                <template x-for="entry in entries" :key="entry.key">
                    <section class="card sg-entry mb-3" :class="{ 'is-open': open === entry.key, 'has-error': hasErrors(entry) }"
                             :aria-labelledby="id(entry, 'title')">
                        <input type="hidden" :name="name(entry, 'department_id')" value="{{ auth()->user()->department_id }}">
                        <input type="hidden" :name="name(entry, 'job_plan_id')" :value="entry.job_plan_id">

                        <div class="sg-entry__head">
                            <button type="button" class="sg-entry__toggle" @click="toggle(entry)" :aria-expanded="open === entry.key"
                                    :aria-controls="id(entry, 'body')">
                                <span class="sg-entry__num" x-text="number(entry)"></span>
                                <span class="min-w-0 text-start d-flex flex-column">
                                    <span class="fw-bold sg-clamp-1" :id="id(entry, 'title')"
                                          x-text="open === entry.key || !entry.job_name ? 'Laporan ' + number(entry) : entry.job_name"></span>
                                    <span class="small" x-show="open !== entry.key"
                                          :class="hasErrors(entry) ? 'text-danger fw-semibold' : 'text-muted'" x-text="summary(entry)"></span>
                                </span>
                                <i class="bi ms-auto" :class="open === entry.key ? 'bi-chevron-up' : 'bi-chevron-down'" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="sg-icon-btn text-danger" x-show="count > 1" @click="remove(entry)"
                                    :aria-label="'Hapus laporan ' + number(entry)"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                        </div>

                        <div class="sg-entry__body" :id="id(entry, 'body')" x-show="open === entry.key">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" :for="id(entry, 'job_name')">Nama Pekerjaan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" :id="id(entry, 'job_name')" :name="name(entry, 'job_name')"
                                       x-model="entry.job_name" maxlength="255" required placeholder="Mis. Inspeksi conveyor CV-03"
                                       :class="{ 'is-invalid': error(entry, 'job_name') }">
                                <div class="invalid-feedback" x-text="error(entry, 'job_name')"></div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold" :for="id(entry, 'report_date')">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" :id="id(entry, 'report_date')" :name="name(entry, 'report_date')"
                                           x-model="entry.report_date" required :class="{ 'is-invalid': error(entry, 'report_date') }">
                                    <div class="invalid-feedback" x-text="error(entry, 'report_date')"></div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold" :for="id(entry, 'due_date')">Due date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" :id="id(entry, 'due_date')" :name="name(entry, 'due_date')"
                                           x-model="entry.due_date" :min="entry.report_date" required :class="{ 'is-invalid': error(entry, 'due_date') }">
                                    <div class="invalid-feedback" x-text="error(entry, 'due_date')"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold" :for="id(entry, 'job_site_id')">Job site</label>
                                    <select class="form-select" :id="id(entry, 'job_site_id')" :name="name(entry, 'job_site_id')" x-model="entry.job_site_id"
                                            :class="{ 'is-invalid': error(entry, 'job_site_id') }">
                                        <option value="">Pilih job site</option>
                                        @foreach ($jobSites ?? [] as $site)
                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" x-text="error(entry, 'job_site_id')"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold" :for="id(entry, 'section_id')">Section</label>
                                    <select class="form-select" :id="id(entry, 'section_id')" :name="name(entry, 'section_id')" x-model="entry.section_id"
                                            :class="{ 'is-invalid': error(entry, 'section_id') }">
                                        <option value="">{{ ($sections ?? collect())->isEmpty() ? 'Belum ada section' : 'Pilih section' }}</option>
                                        @foreach ($sections ?? [] as $section)
                                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" x-text="error(entry, 'section_id')"></div>
                                </div>
                            </div>

                            {{-- PIC: tombol pembuka sheet; nilai dikirim lewat input tersembunyi --}}
                            <div class="mb-3">
                                <span class="form-label fw-semibold d-block" :id="id(entry, 'pic-label')">PIC (Atasan) <span class="text-danger">*</span></span>
                                <input type="hidden" :name="name(entry, 'job_pic')" :value="entry.job_pic">
                                <button type="button" class="sg-pic-field" @click="choosePic(entry)" :aria-labelledby="id(entry, 'pic-label')"
                                        :class="{ 'is-invalid': error(entry, 'job_pic') }">
                                    <template x-if="entry.job_pic">
                                        <span class="d-flex align-items-center gap-2 min-w-0">
                                            <span class="sg-avatar" style="width:36px;height:36px;font-size:13px" x-text="picName(entry.job_pic).split(' ').map(w => w[0]).slice(0, 2).join('')"></span>
                                            <span class="fw-semibold sg-clamp-1" x-text="picName(entry.job_pic)"></span>
                                        </span>
                                    </template>
                                    <span x-show="!entry.job_pic" class="text-muted">Pilih atasan yang menyetujui</span>
                                    <i class="bi bi-chevron-right ms-auto text-muted" aria-hidden="true"></i>
                                </button>
                                <div class="invalid-feedback d-block" x-text="error(entry, 'job_pic')"></div>
                                @if ($pics->isEmpty())
                                    <div class="small text-danger mt-1">Belum ada atasan yang berhak menyetujui laporan Anda. Hubungi admin.</div>
                                @endif
                            </div>

                            <fieldset class="mb-3">
                                <legend class="form-label fw-semibold fs-6">Progres Pekerjaan</legend>
                                <div class="sg-segment" role="radiogroup">
                                    @foreach (config('sigap-ui.report_progress') as $value => $progress)
                                        <input type="radio" class="btn-check" :name="name(entry, 'status')" :id="id(entry, 'status-{{ $value }}')"
                                               value="{{ $value }}" x-model="entry.status" autocomplete="off">
                                        <label class="sg-segment__item" :for="id(entry, 'status-{{ $value }}')">
                                            <i class="bi {{ $progress['icon'] }}" aria-hidden="true"></i>{{ $progress['label'] }}
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" :for="id(entry, 'description')">Deskripsi <span class="text-danger">*</span></label>
                                <textarea class="form-control" rows="4" maxlength="2000" :id="id(entry, 'description')" :name="name(entry, 'description')"
                                          x-model="entry.description" required placeholder="Apa yang dikerjakan, temuan, dan hasilnya"
                                          :class="{ 'is-invalid': error(entry, 'description') }"></textarea>
                                <div class="d-flex justify-content-between gap-2">
                                    <div class="invalid-feedback d-block flex-grow-1 w-auto" x-text="error(entry, 'description')"></div>
                                    <span class="small text-muted sg-num flex-none mt-1" x-text="(entry.description || '').length + ' / 2000'"></span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" :for="id(entry, 'remark')">Remark</label>
                                <textarea class="form-control" rows="2" maxlength="1000" :id="id(entry, 'remark')" :name="name(entry, 'remark')"
                                          x-model="entry.remark" placeholder="Catatan tindak lanjut (opsional)"></textarea>
                            </div>

                            {{-- Lampiran: satu file per field attachment / attachment_2 / attachment_3 --}}
                            <div class="sg-uploader" x-data="sgUploader({ maxMb: 5, names: [name(entry, 'attachment'), name(entry, 'attachment_2'), name(entry, 'attachment_3')] })">
                                <div class="form-label fw-semibold">Lampiran <span class="text-muted fw-normal" x-text="files.length + ' / 3 · maks. 5 MB per berkas'"></span></div>
                                <template x-for="(field, slot) in names" :key="field">
                                    <input type="file" class="d-none" :name="field" :data-slot="slot" tabindex="-1" aria-hidden="true">
                                </template>
                                <input type="file" accept="image/*" capture="environment" class="d-none" x-ref="camera" tabindex="-1" aria-hidden="true" @change="add($event)">
                                <input type="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple class="d-none" x-ref="gallery" tabindex="-1" aria-hidden="true" @change="add($event)">

                                <div class="sg-uploader__grid" x-show="files.length" x-cloak>
                                    <template x-for="(item, index) in files" :key="item.key">
                                        <div class="sg-uploader__item">
                                            <template x-if="item.preview"><img :src="item.preview" :alt="item.file.name"></template>
                                            <template x-if="!item.preview">
                                                <div class="sg-uploader__file"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><span x-text="item.file.name"></span></div>
                                            </template>
                                            <button type="button" class="sg-uploader__remove" @click="remove(index)" :aria-label="'Hapus ' + item.file.name">
                                                <i class="bi bi-x-lg" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <div class="sg-uploader__actions" x-show="files.length < max">
                                    <button type="button" class="btn btn-outline-secondary" @click="$refs.camera.click()"><i class="bi bi-camera" aria-hidden="true"></i> Ambil Foto</button>
                                    <button type="button" class="btn btn-outline-secondary" @click="$refs.gallery.click()"><i class="bi bi-images" aria-hidden="true"></i> Galeri / File</button>
                                </div>
                                <div class="sg-uploader__hint" :class="{ 'text-danger': error }" aria-live="polite"
                                     x-text="busy ? 'Menyiapkan foto…' : error || 'JPG, PNG, PDF, Word, atau Excel.'"></div>
                            </div>
                        </div>
                    </section>
                </template>

                <button type="button" class="btn btn-outline-secondary w-100" @click="add()">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah laporan lain
                </button>
                <p class="small text-muted text-center mt-2 mb-0">Semua laporan dikirim sekaligus ke PIC masing-masing.</p>
            </div>

            {{-- Panel samping (desktop) --}}
            <aside class="col-lg-4 d-none d-lg-block">
                <div class="sg-create-aside">
                    <section class="card p-3 mb-3" aria-labelledby="entries-title">
                        <h2 id="entries-title" class="h6 fw-bold mb-2">Entri laporan <span class="sg-count ms-1" x-text="count"></span></h2>
                        <template x-for="entry in entries" :key="'s' + entry.key">
                            <button type="button" class="sg-entry-link" :class="{ active: open === entry.key }" @click="open = entry.key">
                                <span class="sg-entry__num" x-text="number(entry)"></span>
                                <span class="min-w-0 text-start">
                                    <span class="d-block fw-semibold sg-clamp-1" x-text="entry.job_name || 'Laporan ' + number(entry)"></span>
                                    <span class="d-block small" :class="complete(entry) && !hasErrors(entry) ? 'text-success' : 'text-muted'" x-text="summary(entry)"></span>
                                </span>
                            </button>
                        </template>
                        <button type="button" class="btn btn-quiet w-100 mt-1" @click="add()"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah laporan lain</button>
                    </section>

                    <section class="card p-3" aria-labelledby="flow-title">
                        <h2 id="flow-title" class="h6 fw-bold mb-3">Alur persetujuan</h2>
                        <x-sg.timeline :items="[
                            ['title' => 'Anda', 'meta' => 'Mengirim laporan', 'tone' => 'approved', 'icon' => 'bi-send'],
                            ['title' => 'PIC (atasan)', 'meta' => 'Menyetujui atau menolak · mendapat notifikasi', 'tone' => 'pending', 'icon' => 'bi-hourglass-split'],
                        ]" />
                    </section>
                </div>
            </aside>
        </div>

        <x-sg.sticky-actions>
            <button type="submit" class="btn btn-primary w-100" :disabled="busy">
                <span x-text="busy ? 'Mengirim…' : 'Kirim ' + count + ' Laporan'">Kirim Laporan</span>
            </button>
        </x-sg.sticky-actions>

        {{-- Sheet pilih PIC --}}
        <x-sg.sheet id="pic-sheet" title="Pilih PIC (Atasan)">
            <p class="small text-muted">Hanya atasan yang berhak menyetujui laporan Anda yang ditampilkan.</p>
            <div class="sg-search mb-3">
                <label for="pic-search" class="visually-hidden">Cari atasan</label>
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" id="pic-search" class="form-control" placeholder="Cari nama atasan" x-model="picQuery" autocomplete="off">
            </div>
            <div class="list-group">
                <template x-for="pic in filteredPics" :key="pic.id">
                    <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2"
                            :class="{ active: picFor && picFor.job_pic === pic.id }" @click="selectPic(pic.id)">
                        <span class="sg-avatar" style="width:36px;height:36px;font-size:13px" x-text="pic.name.split(' ').map(w => w[0]).slice(0, 2).join('')"></span>
                        <span class="fw-semibold" x-text="pic.name"></span>
                        <i class="bi bi-check2 ms-auto" x-show="picFor && picFor.job_pic === pic.id" aria-hidden="true"></i>
                    </button>
                </template>
            </div>
            <p class="small text-muted mt-3 mb-0" x-show="!filteredPics.length">Tidak ada atasan dengan nama itu.</p>
        </x-sg.sheet>
    </form>
</x-app-layout>

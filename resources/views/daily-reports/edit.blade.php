{{-- Edit Laporan — pola sama dengan Buat Laporan (satu entri). Departemen tetap milik pembuat (tidak bisa diubah). --}}
@php
    $pics = collect($eligiblePics ?? [])->map(fn ($name, $id) => ['id' => (string) $id, 'name' => $name])->values();
    $slots = [
        ['field' => 'attachment', 'path' => $report->attachment_path, 'name' => $report->attachment_original_name],
        ['field' => 'attachment_2', 'path' => $report->attachment_path_2, 'name' => $report->attachment_original_name_2],
        ['field' => 'attachment_3', 'path' => $report->attachment_path_3, 'name' => $report->attachment_original_name_3],
    ];
    $isImage = fn ($path) => $path && preg_match('/\.(jpe?g|png|gif|webp)$/i', $path);
    $status = old('status', $report->status);
@endphp

<x-app-layout title="Edit Laporan" :back="route('daily-reports.show', $report)">
    <form action="{{ route('daily-reports.update', $report) }}" method="POST" enctype="multipart/form-data" novalidate
          x-data="{
              pic: @js((string) old('job_pic', $report->job_pic)),
              pics: @js($pics),
              q: '',
              busy: false,
              get picName() { return (this.pics.find((p) => p.id === this.pic) || {}).name || ''; },
              get filtered() { const q = this.q.trim().toLowerCase(); return q ? this.pics.filter((p) => p.name.toLowerCase().includes(q)) : this.pics; },
              sheet() { return window.bootstrap.Modal.getOrCreateInstance(document.getElementById('pic-sheet')); },
              choose(id) { this.pic = id; this.sheet().hide(); },
          }" @submit="busy = true">
        @csrf
        @method('PATCH')

        <x-sg.page-header title="Edit Laporan" class="d-none d-lg-flex" :subtitle="'#'.$report->id.' · '.$report->job_name">
            <x-slot:actions>
                <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary" :disabled="busy" x-text="busy ? 'Menyimpan…' : @js($report->approval_status === 'rejected' ? 'Kirim Ulang' : 'Simpan Perubahan')">{{ $report->approval_status === 'rejected' ? 'Kirim Ulang' : 'Simpan Perubahan' }}</button>
            </x-slot:actions>
        </x-sg.page-header>

        @if ($report->approval_status === 'rejected')
            <div class="alert alert-warning d-flex gap-2 mb-3" role="note">
                <i class="bi bi-arrow-repeat fs-5" aria-hidden="true"></i>
                <div><strong>Laporan ini ditolak{{ $report->approver ? ' oleh '.$report->approver->name : '' }}.</strong>
                    @if ($report->rejection_reason)<div class="sg-prewrap">{{ $report->rejection_reason }}</div>@endif
                    <div class="small mt-1">Perbaiki sesuai catatan, lalu tekan <strong>Kirim Ulang</strong>. Laporan kembali menunggu persetujuan PIC.</div></div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger d-flex gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-octagon fs-5" aria-hidden="true"></i>
                <div><strong>Periksa kembali isian yang ditandai.</strong>
                    <div class="small">Lampiran baru perlu dipilih ulang setelah validasi gagal.</div></div>
            </div>
        @endif

        <div class="row g-3 g-lg-4">
            <div class="col-lg-8">
                <section class="card mb-3" aria-labelledby="edit-detail">
                    <div class="sg-entry__body border-0">
                        <h2 id="edit-detail" class="visually-hidden">Detail laporan</h2>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="job_name">Nama Pekerjaan <span class="text-danger">*</span></label>
                            <input type="text" id="job_name" name="job_name" class="form-control @error('job_name') is-invalid @enderror"
                                   value="{{ old('job_name', $report->job_name) }}" maxlength="255" required>
                            @error('job_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold" for="report_date">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" id="report_date" name="report_date" class="form-control @error('report_date') is-invalid @enderror"
                                       value="{{ old('report_date', $report->report_date?->format('Y-m-d')) }}" required>
                                @error('report_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold" for="due_date">Due date <span class="text-danger">*</span></label>
                                <input type="date" id="due_date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                       value="{{ old('due_date', $report->due_date?->format('Y-m-d')) }}" required>
                                @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="job_site_id">Job site</label>
                                <select id="job_site_id" name="job_site_id" class="form-select @error('job_site_id') is-invalid @enderror">
                                    <option value="">Pilih job site</option>
                                    @foreach ($jobSites as $site)
                                        <option value="{{ $site->id }}" @selected(old('job_site_id', $report->job_site_id) == $site->id)>{{ $site->name }}</option>
                                    @endforeach
                                </select>
                                @error('job_site_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="section_id">Section</label>
                                <select id="section_id" name="section_id" class="form-select @error('section_id') is-invalid @enderror">
                                    <option value="">{{ $sections->isEmpty() ? 'Belum ada section' : 'Pilih section' }}</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}" @selected(old('section_id', $report->section_id) == $section->id)>{{ $section->name }}</option>
                                    @endforeach
                                </select>
                                @error('section_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <span class="form-label fw-semibold d-block" id="pic-label">PIC (Atasan) @if (auth()->user()->picOptional())<span class="text-muted fw-normal small">(opsional)</span>@else<span class="text-danger">*</span>@endif</span>
                            <input type="hidden" name="job_pic" :value="pic">
                            <button type="button" id="pic-btn" class="sg-pic-field @error('job_pic') is-invalid @enderror" @click="q = ''; sheet().show()"
                                    aria-labelledby="pic-label pic-btn">
                                <span class="d-flex align-items-center gap-2 min-w-0" x-show="pic">
                                    <span class="sg-avatar" style="width:36px;height:36px;font-size:13px" x-text="picName.split(' ').map(w => w[0]).slice(0, 2).join('')"></span>
                                    <span class="fw-semibold sg-clamp-1" x-text="picName"></span>
                                </span>
                                <span x-show="!pic" class="text-muted">{{ auth()->user()->picOptional() ? 'Tanpa PIC · langsung disetujui saat disimpan' : 'Pilih atasan yang menyetujui' }}</span>
                                <i class="bi bi-chevron-right ms-auto text-muted" aria-hidden="true"></i>
                            </button>
                            @error('job_pic')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <fieldset class="mb-3">
                            <legend class="form-label fw-semibold fs-6">Progres Pekerjaan</legend>
                            <div class="sg-segment" role="radiogroup">
                                @foreach (config('sigap-ui.report_progress') as $value => $progress)
                                    <input type="radio" class="btn-check" name="status" id="status-{{ $value }}" value="{{ $value }}" autocomplete="off" @checked($status === $value)>
                                    <label class="sg-segment__item" for="status-{{ $value }}"><i class="bi {{ $progress['icon'] }}" aria-hidden="true"></i>{{ $progress['label'] }}</label>
                                @endforeach
                            </div>
                            @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </fieldset>

                        <div class="mb-3" x-data="{ len: {{ mb_strlen(old('description', (string) $report->description)) }} }">
                            <label class="form-label fw-semibold" for="description">Deskripsi <span class="text-danger">*</span></label>
                            <textarea id="description" name="description" rows="5" maxlength="2000" required @input="len = $event.target.value.length"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $report->description) }}</textarea>
                            <div class="d-flex justify-content-between gap-2">
                                <div class="invalid-feedback d-block flex-grow-1 w-auto">@error('description'){{ $message }}@enderror</div>
                                <span class="small text-muted sg-num flex-none mt-1" x-text="len + ' / 2000'"></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="remark">Remark</label>
                            <textarea id="remark" name="remark" rows="2" maxlength="1000" class="form-control @error('remark') is-invalid @enderror"
                                      placeholder="Catatan tindak lanjut (opsional)">{{ old('remark', $report->remark) }}</textarea>
                            @error('remark')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Lampiran per slot: file lama tetap kecuali diganti --}}
                        <fieldset>
                            <legend class="form-label fw-semibold fs-6">Lampiran <span class="text-muted fw-normal small">maks. 5 MB per berkas</span></legend>
                            @foreach ($slots as $i => $slot)
                                <div class="sg-slot" x-data="sgSlot({ maxMb: 5 })">
                                    <input type="file" name="{{ $slot['field'] }}" x-ref="field" class="d-none" tabindex="-1" aria-hidden="true">
                                    <input type="file" accept="image/*" capture="environment" x-ref="camera" class="d-none" tabindex="-1" aria-hidden="true" @change="pick($event)">
                                    <input type="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" x-ref="gallery" class="d-none" tabindex="-1" aria-hidden="true" @change="pick($event)">

                                    <div class="sg-slot__thumb" aria-hidden="true">
                                        <template x-if="preview"><img :src="preview" alt=""></template>
                                        <template x-if="!preview">
                                            @if ($isImage($slot['path']))
                                                <img src="{{ route('attachments.show', basename($slot['path'])) }}" alt="" loading="lazy">
                                            @else
                                                <i class="bi {{ $slot['path'] ? 'bi-file-earmark-text' : 'bi-paperclip' }}"></i>
                                            @endif
                                        </template>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="small text-muted">Lampiran {{ $i + 1 }}</div>
                                        <div class="fw-semibold sg-clamp-1" x-show="picked" x-cloak x-text="picked + ' (baru)'"></div>
                                        <div class="sg-clamp-1" x-show="!picked">
                                            @if ($slot['path'])
                                                <a href="{{ route('attachments.show', basename($slot['path'])) }}" target="_blank" rel="noopener">{{ $slot['name'] ?: basename($slot['path']) }}</a>
                                            @else
                                                <span class="text-muted">Kosong</span>
                                            @endif
                                        </div>
                                        <div class="small" :class="error ? 'text-danger' : 'text-muted'" x-show="error || busy" x-cloak x-text="busy ? 'Menyiapkan foto…' : error"></div>
                                        @error($slot['field'])<div class="small text-danger">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="sg-slot__actions">
                                        <button type="button" class="btn btn-outline-secondary btn-icon" @click="$refs.camera.click()" aria-label="Ambil foto untuk lampiran {{ $i + 1 }}"><i class="bi bi-camera" aria-hidden="true"></i></button>
                                        <button type="button" class="btn btn-outline-secondary btn-icon" @click="$refs.gallery.click()" aria-label="{{ $slot['path'] ? 'Ganti' : 'Pilih' }} file lampiran {{ $i + 1 }}"><i class="bi bi-images" aria-hidden="true"></i></button>
                                        <button type="button" class="btn btn-quiet btn-icon" x-show="picked" x-cloak @click="undo()" aria-label="Batalkan file baru lampiran {{ $i + 1 }}"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                            @endforeach
                            <p class="small text-muted mt-2 mb-0">File lama tetap tersimpan kecuali Anda menggantinya.</p>
                        </fieldset>
                    </div>
                </section>
            </div>

            <aside class="col-lg-4 d-none d-lg-block">
                <section class="card p-3 sg-create-aside" aria-labelledby="edit-info">
                    <h2 id="edit-info" class="h6 fw-bold mb-2">Sebelum menyimpan</h2>
                    <ul class="small text-muted ps-3 mb-0">
                        <li>Laporan hanya bisa diedit selama belum disetujui atau ditolak.</li>
                        <li>Departemen laporan mengikuti departemen Anda.</li>
                        <li>PIC menerima laporan versi terbaru.</li>
                    </ul>
                </section>
            </aside>
        </div>

        <x-sg.sticky-actions>
            <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-outline-secondary flex-fill">Batal</a>
            <button type="submit" class="btn btn-primary flex-fill" :disabled="busy" x-text="busy ? 'Menyimpan…' : @js($report->approval_status === 'rejected' ? 'Kirim Ulang' : 'Simpan')">{{ $report->approval_status === 'rejected' ? 'Kirim Ulang' : 'Simpan' }}</button>
        </x-sg.sticky-actions>

        <x-sg.sheet id="pic-sheet" title="Pilih PIC (Atasan)">
            <p class="small text-muted">Hanya atasan yang berhak menyetujui laporan Anda yang ditampilkan.</p>
            <div class="sg-search mb-3">
                <label for="pic-search" class="visually-hidden">Cari atasan</label>
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" id="pic-search" class="form-control" placeholder="Cari nama atasan" x-model="q" autocomplete="off">
            </div>
            <div class="list-group">
                {{-- Level 4+: boleh tanpa PIC → laporan langsung disetujui sebagai catatan kerja mandiri --}}
                @if (auth()->user()->picOptional())
                    <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2"
                            :class="{ active: !pic }" @click="choose('')">
                        <span class="sg-avatar" style="width:36px;height:36px;font-size:13px" aria-hidden="true"><i class="bi bi-person-check"></i></span>
                        <span><span class="fw-semibold d-block">Tanpa PIC</span><span class="small opacity-75">Langsung disetujui sebagai catatan kerja</span></span>
                        <i class="bi bi-check2 ms-auto" x-show="!pic" aria-hidden="true"></i>
                    </button>
                @endif
                <template x-for="p in filtered" :key="p.id">
                    <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2"
                            :class="{ active: pic === p.id }" @click="choose(p.id)">
                        <span class="sg-avatar" style="width:36px;height:36px;font-size:13px" x-text="p.name.split(' ').map(w => w[0]).slice(0, 2).join('')"></span>
                        <span class="fw-semibold" x-text="p.name"></span>
                        <i class="bi bi-check2 ms-auto" x-show="pic === p.id" aria-hidden="true"></i>
                    </button>
                </template>
            </div>
            <p class="small text-muted mt-3 mb-0" x-show="!filtered.length">Tidak ada atasan dengan nama itu.</p>
        </x-sg.sheet>
    </form>
</x-app-layout>

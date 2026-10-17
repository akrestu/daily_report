{{--
    Form Rencana Kerja (buat & edit). $plan = null saat membuat.
    Penerima tugas dipilih lewat sheet (seperti PIC di Buat Laporan); setelah dibuat tidak bisa diganti.
--}}
@php
    $editing = (bool) $plan;
    $assignees = $eligibleAssignees->map(fn ($u) => ['id' => (string) $u->id, 'name' => $u->name, 'role' => $u->role->name ?? ''])->values();
    $back = $editing ? route('job-plans.show', $plan) : route('job-plans.index');
    $planned = old('planned_date', $plan?->planned_date?->format('Y-m-d') ?? now()->format('Y-m-d'));
@endphp

<form action="{{ $editing ? route('job-plans.update', $plan) : route('job-plans.store') }}" method="POST" novalidate
      x-data="{
          assignee: @js((string) old('assignee_id', $plan?->assignee_id ?? '')),
          people: @js($assignees),
          q: '',
          planned: @js($planned),
          due: @js(old('due_date', $plan?->due_date?->format('Y-m-d') ?? '')),
          len: {{ mb_strlen(old('description', (string) $plan?->description)) }},
          busy: false,
          get person() { return this.people.find((p) => p.id === this.assignee) || null; },
          get filtered() { const q = this.q.trim().toLowerCase(); return q ? this.people.filter((p) => p.name.toLowerCase().includes(q)) : this.people; },
          sheet() { return window.bootstrap.Modal.getOrCreateInstance(document.getElementById('assignee-sheet')); },
          choose(id) { this.assignee = id; this.sheet().hide(); },
      }" @submit="busy = true">
    @csrf
    @if ($editing) @method('PUT') @endif
    <input type="hidden" name="department_id" value="{{ $editing ? $plan->department_id : $user->department_id }}">

    <x-sg.page-header :title="$editing ? 'Edit Rencana Kerja' : 'Buat Rencana Kerja'" class="d-none d-lg-flex"
                      :subtitle="$editing ? $plan->job_name : 'Tugaskan pekerjaan ke anggota tim. Penerima bisa mengonversinya menjadi laporan.'">
        <x-slot:actions>
            <a href="{{ $back }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" :disabled="busy" x-text="busy ? 'Menyimpan…' : '{{ $editing ? 'Simpan Perubahan' : 'Tugaskan' }}'">{{ $editing ? 'Simpan Perubahan' : 'Tugaskan' }}</button>
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
            <section class="card" aria-label="Detail rencana kerja">
                <div class="sg-entry__body border-0">
                    {{-- Penerima tugas --}}
                    <div class="mb-3">
                        <span class="form-label fw-semibold d-block" id="assignee-label">Ditugaskan kepada @unless ($editing)<span class="text-danger">*</span>@endunless</span>
                        @if ($editing)
                            <div class="sg-pic-field" aria-labelledby="assignee-label">
                                <x-sg.avatar :user="$plan->assignee" :size="36" />
                                <span class="min-w-0">
                                    <span class="d-block fw-semibold sg-clamp-1">{{ $plan->assignee->name ?? '-' }}</span>
                                    <span class="d-block small text-muted">{{ $plan->assignee->role->name ?? '' }}</span>
                                </span>
                            </div>
                            <input type="hidden" name="assignee_id" value="{{ $plan->assignee_id }}">
                            <div class="small text-muted mt-1">Penerima tugas tidak bisa diganti setelah rencana dibuat.</div>
                        @else
                            <input type="hidden" name="assignee_id" :value="assignee">
                            <button type="button" id="assignee-btn" class="sg-pic-field @error('assignee_id') is-invalid @enderror" @click="q = ''; sheet().show()"
                                    aria-labelledby="assignee-label assignee-btn">
                                <span class="d-flex align-items-center gap-2 min-w-0" x-show="person">
                                    <span class="sg-avatar" style="width:36px;height:36px;font-size:13px" x-text="person ? person.name.split(' ').map(w => w[0]).slice(0, 2).join('') : ''"></span>
                                    <span class="min-w-0 text-start">
                                        <span class="d-block fw-semibold sg-clamp-1" x-text="person?.name"></span>
                                        <span class="d-block small text-muted" x-text="person?.role"></span>
                                    </span>
                                </span>
                                <span x-show="!person" class="text-muted">Pilih anggota tim</span>
                                <i class="bi bi-chevron-right ms-auto text-muted" aria-hidden="true"></i>
                            </button>
                            @error('assignee_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @if ($assignees->isEmpty())
                                <div class="small text-danger mt-1">Belum ada anggota tim yang bisa ditugaskan. Hubungi admin.</div>
                            @endif
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="job_name">Nama Pekerjaan <span class="text-danger">*</span></label>
                        <input type="text" id="job_name" name="job_name" maxlength="255" required placeholder="Mis. Inspeksi panel listrik gedung B"
                               class="form-control @error('job_name') is-invalid @enderror" value="{{ old('job_name', $plan?->job_name) }}">
                        @error('job_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="planned_date">Tanggal rencana <span class="text-danger">*</span></label>
                            <input type="date" id="planned_date" name="planned_date" required x-model="planned"
                                   @change="if (due && due < planned) due = planned"
                                   class="form-control @error('planned_date') is-invalid @enderror">
                            @error('planned_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="due_date">Due date <span class="text-danger">*</span></label>
                            <input type="date" id="due_date" name="due_date" required x-model="due" :min="planned"
                                   class="form-control @error('due_date') is-invalid @enderror">
                            @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" for="job_site_id">Job site</label>
                            <select id="job_site_id" name="job_site_id" class="form-select @error('job_site_id') is-invalid @enderror">
                                <option value="">Pilih job site</option>
                                @foreach ($jobSites as $site)
                                    <option value="{{ $site->id }}" @selected(old('job_site_id', $plan?->job_site_id) == $site->id)>{{ $site->name }}</option>
                                @endforeach
                            </select>
                            @error('job_site_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" for="section_id">Section</label>
                            <select id="section_id" name="section_id" class="form-select @error('section_id') is-invalid @enderror">
                                <option value="">{{ $sections->isEmpty() ? 'Belum ada section' : 'Pilih section' }}</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}" @selected(old('section_id', $plan?->section_id) == $section->id)>{{ $section->name }}</option>
                                @endforeach
                            </select>
                            @error('section_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="description">Deskripsi <span class="text-danger">*</span></label>
                        <textarea id="description" name="description" rows="5" maxlength="2000" required @input="len = $event.target.value.length"
                                  placeholder="Apa yang harus dikerjakan, lokasi, dan hasil yang diharapkan"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $plan?->description) }}</textarea>
                        <div class="d-flex justify-content-between gap-2">
                            <div class="invalid-feedback d-block flex-grow-1 w-auto">@error('description'){{ $message }}@enderror</div>
                            <span class="small text-muted sg-num flex-none mt-1" x-text="len + ' / 2000'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold" for="remark">Catatan tambahan</label>
                        <textarea id="remark" name="remark" rows="2" maxlength="1000" placeholder="Opsional"
                                  class="form-control @error('remark') is-invalid @enderror">{{ old('remark', $plan?->remark) }}</textarea>
                        @error('remark')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>
        </div>

        <aside class="col-lg-4 d-none d-lg-block">
            <section class="card p-3 sg-create-aside" aria-labelledby="plan-flow">
                <h2 id="plan-flow" class="h6 fw-bold mb-3">Alur rencana kerja</h2>
                <x-sg.timeline :items="[
                    ['title' => 'Anda', 'meta' => 'Menugaskan pekerjaan', 'tone' => 'approved', 'icon' => 'bi-send'],
                    ['title' => 'Penerima tugas', 'meta' => 'Mendapat notifikasi, lalu mengonversi menjadi laporan', 'tone' => 'pending', 'icon' => 'bi-arrow-repeat'],
                    ['title' => 'Anda', 'meta' => 'Menyetujui laporan hasil pekerjaan', 'tone' => 'pending', 'icon' => 'bi-check2-square'],
                ]" />
            </section>
        </aside>
    </div>

    <x-sg.sticky-actions>
        <a href="{{ $back }}" class="btn btn-outline-secondary flex-fill">Batal</a>
        <button type="submit" class="btn btn-primary flex-fill" :disabled="busy" x-text="busy ? 'Menyimpan…' : '{{ $editing ? 'Simpan' : 'Tugaskan' }}'">{{ $editing ? 'Simpan' : 'Tugaskan' }}</button>
    </x-sg.sticky-actions>

    @unless ($editing)
        <x-sg.sheet id="assignee-sheet" title="Pilih anggota tim">
            <div class="sg-search mb-3">
                <label for="assignee-search" class="visually-hidden">Cari anggota tim</label>
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" id="assignee-search" class="form-control" placeholder="Cari nama" x-model="q" autocomplete="off">
            </div>
            <div class="list-group">
                <template x-for="p in filtered" :key="p.id">
                    <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2"
                            :class="{ active: assignee === p.id }" @click="choose(p.id)">
                        <span class="sg-avatar" style="width:36px;height:36px;font-size:13px" x-text="p.name.split(' ').map(w => w[0]).slice(0, 2).join('')"></span>
                        <span class="min-w-0 text-start">
                            <span class="d-block fw-semibold" x-text="p.name"></span>
                            <span class="d-block small" :class="assignee === p.id ? '' : 'text-muted'" x-text="p.role"></span>
                        </span>
                        <i class="bi bi-check2 ms-auto" x-show="assignee === p.id" aria-hidden="true"></i>
                    </button>
                </template>
            </div>
            <p class="small text-muted mt-3 mb-0" x-show="!filtered.length">Tidak ada anggota tim dengan nama itu.</p>
        </x-sg.sheet>
    @endunless
</form>

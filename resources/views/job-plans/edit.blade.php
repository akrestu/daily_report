<x-app-layout>
    <x-slot name="header">Edit Job Plan</x-slot>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6 col-sm-12 mb-2 mb-md-0">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-clipboard-check text-primary me-2"></i>Edit Job Plan
                    </h5>
                </div>
                <div class="col-md-6 col-sm-12 text-md-end mt-2 mt-md-0">
                    <a href="{{ route('job-plans.show', $plan) }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-3 p-lg-4">
            <form action="{{ route('job-plans.update', $plan) }}" method="POST" id="jobPlanForm">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="alert alert-danger mb-4 border-0 shadow-sm">
                        <div class="d-flex">
                            <i class="fas fa-exclamation-circle text-danger fa-2x me-3"></i>
                            <div>
                                <h5 class="alert-heading mb-1">Terdapat kesalahan</h5>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="p-3 bg-light rounded-3 mb-4">
                    <!-- Job Name -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('job_name') is-invalid @enderror"
                                    id="job_name"
                                    name="job_name"
                                    placeholder="Nama pekerjaan"
                                    value="{{ old('job_name', $plan->job_name) }}"
                                    required>
                                <label for="job_name">Nama Pekerjaan <span class="text-danger">*</span></label>
                                @error('job_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-medium">Deskripsi Pekerjaan <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                            id="description" name="description" rows="5" required
                            placeholder="Jelaskan rencana pekerjaan secara detail"
                            style="border-radius:0.375rem;">{{ old('description', $plan->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="text-end mt-1"><small class="text-muted" id="descCounter">0 / 2000</small></div>
                    </div>
                </div>

                <div class="row mb-4">
                    <!-- Department (read-only) -->
                    <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                        <label class="form-label fw-medium">Departemen</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-building text-primary"></i></span>
                            <input type="text" class="form-control bg-light"
                                value="{{ $plan->department->name ?? '-' }}" readonly>
                            <input type="hidden" name="department_id" value="{{ $plan->department_id }}">
                        </div>
                    </div>

                    <!-- Job Site -->
                    <div class="col-lg-6 col-md-12">
                        <label for="job_site_id" class="form-label fw-medium">Job Site</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-map-marker-alt text-primary"></i></span>
                            <select class="form-select @error('job_site_id') is-invalid @enderror" id="job_site_id" name="job_site_id">
                                <option value="">Pilih job site (opsional)</option>
                                @foreach ($jobSites as $site)
                                    <option value="{{ $site->id }}" {{ old('job_site_id', $plan->job_site_id) == $site->id ? 'selected' : '' }}>
                                        {{ $site->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('job_site_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <!-- Section -->
                    <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                        <label for="section_id" class="form-label fw-medium">Section</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-layer-group text-primary"></i></span>
                            <select class="form-select @error('section_id') is-invalid @enderror" id="section_id" name="section_id">
                                <option value="">Pilih section (opsional)</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}" {{ old('section_id', $plan->section_id) == $section->id ? 'selected' : '' }}>
                                        {{ $section->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('section_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Assignee (read-only in edit — cannot re-assign) -->
                    <div class="col-lg-6 col-md-12">
                        <label class="form-label fw-medium">Ditugaskan Kepada</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-user-check text-primary"></i></span>
                            <input type="text" class="form-control bg-light"
                                value="{{ $plan->assignee->name ?? '-' }} — {{ $plan->assignee->role->name ?? '' }}" readonly>
                            <input type="hidden" name="assignee_id" value="{{ $plan->assignee_id }}">
                        </div>
                        <small class="text-muted">Assignee tidak dapat diubah setelah plan dibuat.</small>
                    </div>
                </div>

                <div class="row mb-4">
                    <!-- Planned Date -->
                    <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                        <label for="planned_date" class="form-label fw-medium">Tanggal Rencana <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-calendar-day text-primary"></i></span>
                            <input type="date" class="form-control @error('planned_date') is-invalid @enderror"
                                id="planned_date" name="planned_date"
                                value="{{ old('planned_date', $plan->planned_date?->format('Y-m-d')) }}" required>
                        </div>
                        @error('planned_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Due Date -->
                    <div class="col-lg-6 col-md-12">
                        <label for="due_date" class="form-label fw-medium">Batas Waktu <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-primary"></i></span>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                                id="due_date" name="due_date"
                                value="{{ old('due_date', $plan->due_date?->format('Y-m-d')) }}" required>
                        </div>
                        @error('due_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Remark -->
                <div class="mb-4">
                    <label for="remark" class="form-label fw-medium"><i class="fas fa-comment-dots me-1 text-primary"></i> Catatan Tambahan</label>
                    <textarea class="form-control @error('remark') is-invalid @enderror"
                        id="remark" name="remark" rows="3"
                        placeholder="Catatan atau instruksi tambahan (opsional)"
                        style="border-radius:0.375rem;">{{ old('remark', $plan->remark) }}</textarea>
                    @error('remark')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit -->
                <div class="d-flex gap-3 justify-content-end pt-2">
                    <a href="{{ route('job-plans.show', $plan) }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-times me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Character counter for description
        const descArea = document.getElementById('description');
        const descCounter = document.getElementById('descCounter');
        function updateDescCounter() {
            descCounter.textContent = descArea.value.length + ' / 2000';
        }
        descArea.addEventListener('input', updateDescCounter);
        updateDescCounter();

        // Due date must be >= planned date
        const plannedDate = document.getElementById('planned_date');
        const dueDate     = document.getElementById('due_date');
        plannedDate.addEventListener('change', function () {
            if (dueDate.value && dueDate.value < this.value) {
                dueDate.value = this.value;
            }
            dueDate.min = this.value;
        });
        dueDate.min = plannedDate.value;
    </script>
    @endpush
</x-app-layout>

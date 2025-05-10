<x-app-layout>
    <x-slot name="header">
        Create Daily Report
    </x-slot>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6 col-sm-12 mb-2 mb-md-0">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-plus-circle text-primary me-2"></i>New Daily Report
                    </h5>
                </div>
                <div class="col-md-6 col-sm-12 text-md-end mt-2 mt-md-0">
                    <a href="{{ route('daily-reports.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-1"></i> Back to Reports
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-3 p-lg-4">
            <form action="{{ route('daily-reports.store-multiple') }}" method="POST" enctype="multipart/form-data" id="multipleReportForm">
                @csrf
                
                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="alert alert-danger mb-4 border-0 shadow-sm">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-exclamation-circle text-danger fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading mb-1">Please fix the following errors</h5>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div id="report-container">
                    <!-- First Report Form (Initial) -->
                    <div class="report-form mb-4 pb-4 border-bottom" data-index="0">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="report-title mb-0 fw-bold text-primary">
                                <i class="fas fa-clipboard-list me-2"></i>Report #1
                            </h5>
                            <div class="form-actions">
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill remove-form d-none">
                                    <i class="fas fa-trash me-1"></i> Remove
                                </button>
                            </div>
                        </div>
                        
                        <div class="p-3 bg-light rounded-3 mb-4">
                            <!-- Job Name -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-floating">
                                        <input 
                                            type="text" 
                                            class="form-control @error('reports.0.job_name') is-invalid @enderror" 
                                            id="job_name_0" 
                                            name="reports[0][job_name]" 
                                            placeholder="Enter job name" 
                                            value="{{ old('reports.0.job_name') }}" 
                                            required
                                        >
                                        <label for="job_name_0">Job Name <span class="text-danger">*</span></label>
                                        @error('reports.0.job_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Job Description -->
                            <div class="mb-4">
                                <label for="description_0" class="form-label fw-medium">Job Description <span class="text-danger">*</span></label>
                                <textarea 
                                    class="form-control @error('reports.0.description') is-invalid @enderror" 
                                    id="description_0" 
                                    name="reports[0][description]" 
                                    rows="5" 
                                    required
                                    placeholder="Enter detailed job description"
                                    style="border-radius: 0.375rem;"
                                >{{ old('reports.0.description') }}</textarea>
                                @error('reports.0.description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <!-- Job Department -->
                            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                                <label for="department_id_0" class="form-label fw-medium">Department <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-building text-primary"></i></span>
                                    <select 
                                        class="form-select @error('reports.0.department_id') is-invalid @enderror" 
                                        id="department_id_0" 
                                        name="reports[0][department_id]" 
                                        required
                                    >
                                        <option value="">Select department</option>
                                        @foreach ($departments ?? [] as $id => $name)
                                            <option value="{{ $id }}" {{ old('reports.0.department_id', auth()->user()->department_id ?? '') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('reports.0.department_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Job PIC -->
                            <div class="col-lg-6 col-md-12">
                                <label for="job_pic_0" class="form-label fw-medium">Person In Charge <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-user text-primary"></i></span>
                                    <select 
                                        class="form-select @error('reports.0.job_pic') is-invalid @enderror" 
                                        id="job_pic_0" 
                                        name="reports[0][job_pic]" 
                                        required
                                    >
                                        <option value="">Select person in charge</option>
                                        @foreach ($eligiblePics ?? [] as $id => $name)
                                            <option value="{{ $id }}" {{ old('reports.0.job_pic') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('reports.0.job_pic')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <!-- Job Date -->
                            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                                <label for="report_date_0" class="form-label fw-medium">Job Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-calendar-day text-primary"></i></span>
                                    <input 
                                        type="date" 
                                        class="form-control @error('reports.0.report_date') is-invalid @enderror" 
                                        id="report_date_0" 
                                        name="reports[0][report_date]" 
                                        value="{{ old('reports.0.report_date', date('Y-m-d')) }}" 
                                        required
                                    >
                                </div>
                                @error('reports.0.report_date')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Due Date -->
                            <div class="col-lg-6 col-md-12">
                                <label for="due_date_0" class="form-label fw-medium">Due Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-primary"></i></span>
                                    <input 
                                        type="date" 
                                        class="form-control @error('reports.0.due_date') is-invalid @enderror" 
                                        id="due_date_0" 
                                        name="reports[0][due_date]" 
                                        value="{{ old('reports.0.due_date') }}" 
                                        required
                                    >
                                </div>
                                @error('reports.0.due_date')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Job Status -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-medium">Job Status <span class="text-danger">*</span></label>
                                <div class="d-flex flex-column flex-md-row gap-2 gap-md-3">
                                    <div class="flex-grow-1">
                                        <input type="radio" class="btn-check" name="reports[0][status]" id="status_pending_0" value="pending" {{ old('reports.0.status', 'pending') == 'pending' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-warning w-100 d-flex align-items-center justify-content-center gap-2 py-2 py-md-3" for="status_pending_0">
                                            <i class="fas fa-clock"></i>
                                            <span>Pending</span>
                                        </label>
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="radio" class="btn-check" name="reports[0][status]" id="status_in_progress_0" value="in_progress" {{ old('reports.0.status') == 'in_progress' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-info w-100 d-flex align-items-center justify-content-center gap-2 py-2 py-md-3" for="status_in_progress_0">
                                            <i class="fas fa-spinner"></i>
                                            <span>In Progress</span>
                                        </label>
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="radio" class="btn-check" name="reports[0][status]" id="status_completed_0" value="completed" {{ old('reports.0.status') == 'completed' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center gap-2 py-2 py-md-3" for="status_completed_0">
                                            <i class="fas fa-check"></i>
                                            <span>Completed</span>
                                        </label>
                                    </div>
                                </div>
                                @error('reports.0.status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Remark -->
                        <div class="mb-4">
                            <label for="remark_0" class="form-label fw-medium">
                                <i class="fas fa-comment-dots me-1 text-primary"></i> Remark
                            </label>
                            <textarea 
                                class="form-control @error('reports.0.remark') is-invalid @enderror" 
                                id="remark_0" 
                                name="reports[0][remark]" 
                                rows="3"
                                placeholder="Additional information or notes about the job"
                                style="border-radius: 0.375rem;"
                            >{{ old('reports.0.remark') }}</textarea>
                            @error('reports.0.remark')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Attachment -->
                        <div class="mb-3">
                            <label for="attachment_0" class="form-label fw-medium">
                                <i class="fas fa-paperclip me-1 text-primary"></i> Attachment
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-file"></i></span>
                                <input 
                                    type="file" 
                                    class="form-control @error('reports.0.attachment') is-invalid @enderror" 
                                    id="attachment_0" 
                                    name="reports[0][attachment]"
                                >
                            </div>
                            <div class="form-text small mt-1">
                                <i class="fas fa-info-circle me-1"></i> Supported formats: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX. Max size: 5MB
                            </div>
                            @error('reports.0.attachment')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Add More Button -->
                <div class="d-grid mb-4">
                    <button type="button" class="btn btn-outline-primary rounded-pill py-2" id="add-report">
                        <i class="fas fa-plus-circle me-1"></i> Add Another Report
                    </button>
                </div>
                
                <div class="d-flex flex-column flex-md-row justify-content-md-end gap-3 mt-4">
                    <button type="reset" class="btn btn-light px-4 rounded-pill mb-2 mb-md-0">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm" id="saveReportsBtn" style="min-width: 140px;">
                        <span id="saveButtonText"><i class="fas fa-save me-1"></i> Save Reports</span>
                        <span class="spinner-border spinner-border-sm ms-1 d-none" role="status" id="saveSpinner" style="width: 1rem; height: 1rem;">
                            <span class="visually-hidden">Loading...</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Report Modal -->
    <div class="modal fade" id="deleteReportModal" tabindex="-1" aria-labelledby="deleteReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="deleteReportModalLabel">
                        <i class="fas fa-trash text-danger me-2"></i>Remove Report
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-1">Are you sure you want to remove this report?</p>
                    <p class="text-danger mb-0"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmRemoveReport">
                        <i class="fas fa-trash me-1"></i> Remove Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media (max-width: 767.98px) {
            /* General form controls */
            .form-control, .form-select, .input-group {
                font-size: 15px;
            }
            
            /* File input specific styling */
            input[type="file"].form-control {
                padding: 0.375rem 0.5rem;
                font-size: 14px;
            }
            
            /* Adjust padding for status buttons */
            .btn-outline-warning, .btn-outline-info, .btn-outline-success {
                padding: 0.5rem 0.25rem;
                font-size: 14px;
            }
            
            /* Make modals better on mobile */
            .modal-dialog {
                margin: 0.5rem;
            }
            
            /* Improve spacing on mobile */
            .mb-4 {
                margin-bottom: 1rem !important;
            }
            
            /* Adjust padding for action buttons */
            button[type="submit"], button[type="reset"] {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            
            /* Add more spacing for attachments form text */
            .form-text.small {
                display: block;
                margin-top: 0.5rem;
                line-height: 1.4;
            }
            
            /* Make report container have less padding */
            .report-form {
                margin-bottom: 1rem !important;
                padding-bottom: 1rem !important;
            }
            
            /* Field labels on mobile */
            .form-label {
                margin-bottom: 0.25rem;
                font-size: 14px;
            }
            
            /* Adjust card styles */
            .card-body.p-3 {
                padding: 1rem !important;
            }
        }
    </style>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let reportIndex = 0;
            const container = document.getElementById('report-container');
            const addButton = document.getElementById('add-report');
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteReportModal'));
            let formToDelete = null;
            
            // Form submission with spinner
            const form = document.getElementById('multipleReportForm');
            const saveButton = document.getElementById('saveReportsBtn');
            const spinner = document.getElementById('saveSpinner');
            
            form.addEventListener('submit', function() {
                // Show spinner
                spinner.classList.remove('d-none');
                
                // Disable button to prevent double submission
                saveButton.disabled = true;
            });
            
            // Add new report form
            addButton.addEventListener('click', function() {
                reportIndex++;
                const newIndex = reportIndex;
                
                // Clone the first form as a template
                const template = document.querySelector('.report-form').cloneNode(true);
                
                // Update form index and titles
                template.setAttribute('data-index', newIndex);
                template.querySelector('.report-title').textContent = `Report #${newIndex + 1}`;
                
                // Enable remove button for all but the first form
                const removeButton = template.querySelector('.remove-form');
                removeButton.classList.remove('d-none');
                
                // Update all input names and ids
                template.querySelectorAll('input, select, textarea').forEach(function(input) {
                    const name = input.getAttribute('name');
                    if (name) {
                        input.setAttribute('name', name.replace(/\[0\]/g, `[${newIndex}]`));
                    }
                    
                    const id = input.getAttribute('id');
                    if (id) {
                        const newId = id.replace(/_0$/, `_${newIndex}`);
                        input.setAttribute('id', newId);
                        
                        // Update associated labels
                        const label = template.querySelector(`label[for="${id}"]`);
                        if (label) {
                            label.setAttribute('for', newId);
                        }
                    }
                    
                    // Clear values except for status
                    if (!input.getAttribute('name').includes('[status]')) {
                        if (input.tagName === 'SELECT') {
                            input.selectedIndex = 0;
                        } else if (input.type === 'radio') {
                            if (input.value === 'pending') {
                                input.checked = true;
                            } else {
                                input.checked = false;
                            }
                        } else if (input.type !== 'hidden' && input.type !== 'radio') {
                            input.value = '';
                        }
                    }
                });
                
                // Set default report date
                const reportDateInput = template.querySelector(`input[name="reports[${newIndex}][report_date]"]`);
                if (reportDateInput) {
                    reportDateInput.value = new Date().toISOString().split('T')[0];
                }
                
                // Make sure status buttons have the same classes for responsiveness
                template.querySelectorAll('.btn-outline-warning, .btn-outline-info, .btn-outline-success').forEach(function(btn) {
                    if (btn.classList.contains('py-3')) {
                        btn.classList.remove('py-3');
                        btn.classList.add('py-2', 'py-md-3');
                    }
                });
                
                // Ensure the job status container has responsive classes
                const statusContainer = template.querySelector('.d-flex:not(.align-items-center):not(.justify-content-between)');
                if (statusContainer && !statusContainer.classList.contains('flex-column')) {
                    statusContainer.classList.remove('gap-3');
                    statusContainer.classList.add('flex-column', 'flex-md-row', 'gap-2', 'gap-md-3');
                }
                
                // Add to container
                container.appendChild(template);
                
                // Scroll to the new form
                template.scrollIntoView({ behavior: 'smooth', block: 'start' });
                
                // Setup remove button
                setupRemoveButton(template.querySelector('.remove-form'));
            });
            
            // Setup remove buttons function
            function setupRemoveButton(button) {
                button.addEventListener('click', function() {
                    formToDelete = this.closest('.report-form');
                    deleteModal.show();
                });
            }
            
            // Handle confirm delete
            document.getElementById('confirmRemoveReport').addEventListener('click', function() {
                if (formToDelete) {
                    formToDelete.remove();
                    formToDelete = null;
                    deleteModal.hide();
                }
            });
            
            // Setup existing remove buttons
            document.querySelectorAll('.remove-form').forEach(setupRemoveButton);
        });
    </script>
    @endpush
</x-app-layout>
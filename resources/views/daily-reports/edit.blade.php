<x-app-layout>
    <x-slot name="header">
        Edit Daily Report
    </x-slot>

    <div class="card">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Edit Daily Report</h5>
                </div>
                <div class="col text-end">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('daily-reports.update', $report) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                
                <div class="row">
                    <div class="col-md-6">
                        <x-input
                            label="Job Name"
                            name="job_name"
                            required="true"
                            placeholder="Enter job name"
                            :value="old('job_name', $report->job_name)"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-select
                            label="Department"
                            name="department_id"
                            required="true"
                            :options="$departments ?? []"
                            :selected="old('department_id', $report->department_id)"
                        />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="job_site_id" class="form-label">Job Site</label>
                        <select class="form-select @error('job_site_id') is-invalid @enderror" id="job_site_id" name="job_site_id">
                            <option value="">Select job site (optional)</option>
                            @foreach ($jobSites ?? [] as $jobSite)
                                <option value="{{ $jobSite->id }}" {{ old('job_site_id', $report->job_site_id) == $jobSite->id ? 'selected' : '' }}>
                                    {{ $jobSite->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('job_site_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="section_id" class="form-label">Section</label>
                        <select class="form-select @error('section_id') is-invalid @enderror" id="section_id" name="section_id">
                            <option value="">Select section (optional)</option>
                            @foreach ($sections ?? [] as $section)
                                <option value="{{ $section->id }}" {{ old('section_id', $report->section_id) == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('section_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <x-input 
                            label="Report Date" 
                            name="report_date"
                            type="date"
                            required="true"
                            :value="old('report_date', $report->report_date->format('Y-m-d'))"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-input 
                            label="Due Date" 
                            name="due_date"
                            type="date"
                            required="true"
                            :value="old('due_date', $report->due_date->format('Y-m-d'))"
                        />
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <x-select
                            label="Person In Charge (PIC)"
                            name="job_pic"
                            required="true"
                            :options="$eligiblePics ?? []"
                            :selected="old('job_pic', $report->job_pic)"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-select
                            label="Status"
                            name="status"
                            required="true"
                            :options="[
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed'
                            ]"
                            :selected="old('status', $report->status)"
                        />
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea 
                        class="form-control @error('description') is-invalid @enderror" 
                        id="description" 
                        name="description" 
                        rows="5" 
                        required
                    >{{ old('description', $report->description) }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="remark" class="form-label">Remarks</label>
                    <textarea 
                        class="form-control @error('remark') is-invalid @enderror" 
                        id="remark" 
                        name="remark" 
                        rows="3"
                    >{{ old('remark', $report->remark) }}</textarea>
                    @error('remark')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="attachment" class="form-label">Attachment 1</label>
                    <input type="file" class="form-control @error('attachment') is-invalid @enderror" id="attachment" name="attachment">
                    <div class="form-text">
                        @if($report->attachment_path)
                        Current file: <a href="{{ route('attachments.show', basename($report->attachment_path)) }}" target="_blank">{{ $report->attachment_original_name }}</a>
                        @endif
                    </div>
                    @error('attachment')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="attachment_2" class="form-label">Attachment 2</label>
                    <input type="file" class="form-control @error('attachment_2') is-invalid @enderror" id="attachment_2" name="attachment_2">
                    <div class="form-text">
                        @if($report->attachment_path_2)
                        Current file: <a href="{{ route('attachments.show', basename($report->attachment_path_2)) }}" target="_blank">{{ $report->attachment_original_name_2 }}</a>
                        @endif
                    </div>
                    @error('attachment_2')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="attachment_3" class="form-label">Attachment 3</label>
                    <input type="file" class="form-control @error('attachment_3') is-invalid @enderror" id="attachment_3" name="attachment_3">
                    <div class="form-text">
                        @if($report->attachment_path_3)
                        Current file: <a href="{{ route('attachments.show', basename($report->attachment_path_3)) }}" target="_blank">{{ $report->attachment_original_name_3 }}</a>
                        @endif
                    </div>
                    @error('attachment_3')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-text small mb-4">
                    Supported formats: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX. Max size: 5MB per file. Maximum 3 attachments.
                </div>
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="javascript:history.back()" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout> 
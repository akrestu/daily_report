<x-app-layout>
    <x-slot name="header">
        Import Daily Reports
    </x-slot>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Import Daily Reports from Excel</h5>
                        <a href="{{ route('daily-reports.user-jobs') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to My Reports
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->has('import_errors'))
                        <div class="alert alert-danger">
                            <strong>Import Errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->get('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('daily-reports.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="file" class="form-label">Excel File</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" required>
                            @error('file')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">Supported formats: .xlsx, .xls, .csv</div>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Import Guidelines</h6>
                            <p>The Excel file should have the following columns with a header row:</p>
                            <ul class="mb-1">
                                <li><strong>job_name</strong> (required): Name of the job or task</li>
                                <li><strong>department</strong> (required): Department name (must exist in the system)</li>
                                <li><strong>status</strong> (required): Must be one of: pending, in_progress, completed</li>
                                <li><strong>report_date</strong> (required): Format: DD/MM/YYYY (e.g., 28/04/2025)</li>
                                <li><strong>due_date</strong> (required): Format: DD/MM/YYYY (e.g., 30/04/2025)</li>
                                <li><strong>description</strong> (required): Detailed description of the task</li>
                                <li><strong>remark</strong> (optional): Additional notes or remarks</li>
                                <li><strong>user_id</strong> (required): User ID of the Person In Charge</li>
                            </ul>
                            <a href="{{ route('daily-reports.export-template') }}" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-download me-1"></i> Download Template
                            </a>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-import me-1"></i> Import Reports
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
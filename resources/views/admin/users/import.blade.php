<x-app-layout>
    <x-slot name="header">
        Import Users
    </x-slot>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Import Users from Excel</h5>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Users
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

                    <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
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
                                <li><strong>name</strong> (required): User's full name</li>
                                <li><strong>email</strong> (required): User's email address</li>
                                <li><strong>user_id</strong> (optional): User ID</li>
                                <li><strong>role</strong> (optional): Role name (must exist in the system)</li>
                                <li><strong>department</strong> (optional): Department name (must exist in the system)</li>
                                <li><strong>password</strong> (optional): If not provided, a default password will be used</li>
                                <li><strong>email_verified</strong> (optional): Set to "Yes" for verified emails</li>
                            </ul>
                            <a href="{{ route('admin.users.export-template') }}" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-download me-1"></i> Download Template
                            </a>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-import me-1"></i> Import Users
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">System Settings</h2>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white p-3">
                    <h5 class="mb-0">Application Settings</h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Settings page is currently under development. This feature will be available soon.
                    </div>
                    
                    <form>
                        <div class="mb-3">
                            <label for="appName" class="form-label">Application Name</label>
                            <input type="text" class="form-control" id="appName" value="Daily Report System" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="emailNotifications" class="form-label">Email Notifications</label>
                            <select class="form-select" id="emailNotifications" disabled>
                                <option value="enabled" selected>Enabled</option>
                                <option value="disabled">Disabled</option>
                            </select>
                        </div>
                        
                        <button type="button" class="btn btn-primary" disabled>
                            <i class="fas fa-save me-2"></i>Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
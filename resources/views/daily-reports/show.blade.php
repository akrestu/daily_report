<x-app-layout>
    <x-slot name="header">
        Report Details
    </x-slot>

    <!-- Main Content Container -->
    <div class="container-fluid p-0">
        <!-- Report Header Card -->
        <div class="card border-0 shadow-sm mb-4 overflow-hidden">
            <!-- Status Indicator Bar at the top -->
            <div class="progress" style="height: 4px;">
                <div class="progress-bar bg-{{ $report->status === 'pending' ? 'warning' : ($report->status === 'in_progress' ? 'info' : 'success') }}" 
                     role="progressbar" 
                     style="width: {{ $report->status === 'pending' ? '33' : ($report->status === 'in_progress' ? '66' : '100') }}%"></div>
        </div>
        
            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- Left Column - Report Title and Core Info -->
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center mb-3">
                            <div class="report-icon rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                 style="width: 50px; height: 50px; flex-shrink: 0;">
                                <i class="fas fa-file-alt text-{{ $report->status === 'pending' ? 'warning' : ($report->status === 'in_progress' ? 'info' : 'success') }}" style="font-size: 22px;"></i>
                </div>
                            <div>
                                <h4 class="fw-bold text-dark mb-1">{{ $report->job_name }}</h4>
                                <div class="d-flex flex-wrap align-items-center text-muted small">
                                    <span class="me-3"><i class="fas fa-building me-1"></i> {{ $report->department->name ?? 'N/A' }}</span>
                                    <span class="me-3"><i class="fas fa-calendar-alt me-1"></i> {{ $report->report_date->format('d M Y') }}</span>
                                    <span class="me-3"><i class="fas fa-clock me-1"></i> {{ $report->created_at->diffForHumans() }}</span>
                </div>
        </div>
    </div>

                        <!-- Status Pills and Essential Information -->
                        <div class="d-flex flex-wrap mb-4">
                            <span class="badge rounded-pill bg-{{ $report->status === 'pending' ? 'warning' : ($report->status === 'in_progress' ? 'info' : 'success') }} me-2 mb-2 px-3 py-2">
                                    <i class="fas fa-{{ $report->status === 'pending' ? 'hourglass-start' : ($report->status === 'in_progress' ? 'sync-alt' : 'check-circle') }} me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                </span>
                            <span class="badge rounded-pill bg-{{ $report->approval_status === 'approved' ? 'success' : ($report->approval_status === 'rejected' ? 'danger' : 'secondary') }} me-2 mb-2 px-3 py-2">
                                <i class="fas fa-{{ $report->approval_status === 'approved' ? 'check-circle' : ($report->approval_status === 'rejected' ? 'times-circle' : 'clock') }} me-1"></i>
                                {{ ucfirst($report->approval_status) }}
                                            </span>
                            @if($report->due_date && $report->due_date->isPast() && $report->status !== 'completed')
                                <span class="badge rounded-pill bg-danger me-2 mb-2 px-3 py-2">
                                    <i class="fas fa-exclamation-circle me-1"></i> Overdue
                                </span>
                                        @endif
                            </div>

                        <!-- Info Grid -->
                        <div class="row g-3 mb-4">
                            <!-- PIC & Created By -->
                            <div class="col-md-6">
                                <div class="d-flex flex-column h-100 p-3 rounded-3 bg-light">
                                    <div class="d-flex justify-content-between mb-2">
                                <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2" style="width: 32px; height: 32px; flex-shrink: 0;">
                                                {{ substr($report->pic->name ?? 'N/A', 0, 1) }}
                                </div>
                                            <div>
                                                <small class="text-muted d-block">Person in Charge</small>
                                                <span class="fw-medium">{{ $report->pic->name ?? 'N/A' }}</span>
                            </div>
                                </div>
                            </div>
                                    <div class="d-flex justify-content-between">
                                    <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2" style="width: 32px; height: 32px; flex-shrink: 0;">
                                                {{ substr($report->user->name ?? 'N/A', 0, 1) }}
                                    </div>
                                            <div>
                                                <small class="text-muted d-block">Created By</small>
                                                <span class="fw-medium">{{ $report->user->name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                            </div>
                        </div>
                    </div>
                    
                            <!-- Dates Info -->
                            <div class="col-md-6">
                                <div class="d-flex flex-column h-100 p-3 rounded-3 bg-light">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div>
                                            <small class="text-muted d-block">Report Date</small>
                                            <span class="fw-medium">{{ $report->report_date->format('d M Y') }}</span>
                            </div>
                                        <div>
                                            <small class="text-muted d-block">Due Date</small>
                                            <span class="fw-medium {{ ($report->due_date && $report->due_date->isPast() && $report->status !== 'completed') ? 'text-danger' : '' }}">
                                                {{ $report->due_date ? $report->due_date->format('d M Y') : 'Not set' }}
                                            </span>
                        </div>
                            </div>
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <small class="text-muted d-block">Created</small>
                                            <span class="fw-medium">{{ $report->created_at->format('d M Y') }}</span>
                        </div>
                                        <div>
                                            <small class="text-muted d-block">Updated</small>
                                            <span class="fw-medium">{{ $report->updated_at->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
                    <!-- Right Column - Actions and Attachments -->
                    <div class="col-lg-4">
                        <div class="d-flex flex-column h-100">
                            <!-- Actions Panel -->
                            <div class="card h-100 border-0 shadow-sm mb-4">
                                <div class="card-header bg-white py-3 border-0">
                                    <h6 class="mb-0"><i class="fas fa-cog text-primary me-2"></i>Actions</h6>
                </div>
                <div class="card-body">
                                    <div class="d-flex flex-column gap-2">
                                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm w-100">
                                            <i class="fas fa-arrow-left me-1"></i> Back
                                        </a>
                                        @if(auth()->user()->id === $report->user_id && !$report->approved_by)
                                            <a href="{{ route('daily-reports.edit', $report) }}" class="btn btn-primary btn-sm w-100">
                                                <i class="fas fa-edit me-1"></i> Edit Report
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="confirmDelete()">
                                                <i class="fas fa-trash me-1"></i> Delete Report
                                            </button>
                                        @endif
                                        
                                        @if($report->approval_status === 'pending' && auth()->user()->can('approve-reports'))
                                            <div class="dropdown mt-2">
                                                <button class="btn btn-success btn-sm w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-check-circle me-1"></i> Approve/Reject
                                                </button>
                                                <ul class="dropdown-menu w-100 shadow-sm">
                                                    <li>
                                                        <a href="#" class="dropdown-item text-success" onclick="event.preventDefault(); document.getElementById('approval-form-approved').submit();">
                                                            <i class="fas fa-check me-2"></i> Approve Report
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                                            <i class="fas fa-times me-2"></i> Reject Report
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endif
                                        
                                        <!-- PIC Approval Actions - For PIC users -->
                                        @if($report->approval_status === 'pending' && auth()->user()->id === $report->job_pic && !auth()->user()->can('approve-reports'))
                                            <div class="border-top pt-3 mt-2">
                                                <p class="mb-2 small text-muted"><i class="fas fa-info-circle me-1"></i> As the Person in Charge, you can approve or reject this report:</p>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-success btn-sm flex-grow-1" 
                                                            onclick="event.preventDefault(); document.getElementById('pic-approval-form-approved').submit();">
                                                        <i class="fas fa-check me-1"></i> Approve
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm flex-grow-1" 
                                                            data-bs-toggle="modal" data-bs-target="#picRejectModal">
                                                        <i class="fas fa-times me-1"></i> Reject
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Attachment Card (if exists) -->
                        @if($report->attachment_path)
                                        <div class="attachment-card border rounded-3 p-3 bg-light mt-4">
                                <div class="d-flex align-items-center">
                                    <div class="attachment-icon me-3">
                                        <i class="fas fa-file-alt fa-2x text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="mb-1 text-truncate">{{ $report->attachment_original_name ?? 'Document' }}</h6>
                                                    <p class="mb-0 text-muted small">Added {{ \Carbon\Carbon::parse($report->created_at)->diffForHumans() }}</p>
                                    </div>
                                        <a href="{{ route('attachments.show', basename($report->attachment_path)) }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                            </div>
                        @endif
                                    
                                    @if($report->approver)
                                        <div class="mt-4 pt-3 border-top">
                                            <small class="text-muted d-block mb-2">Approved/Rejected By</small>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-2" style="width: 32px; height: 32px;">
                                                    {{ substr($report->approver->name, 0, 1) }}
                            </div>
                                                <div>
                                                    <span class="fw-medium">{{ $report->approver->name }}</span>
                                                    <small class="text-muted d-block">{{ $report->updated_at->format('d M Y, H:i') }}</small>
                            </div>
                        </div>
                    </div>
                                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
                        </div>
                    </div>

        <!-- Content Tabs Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 p-0">
                <ul class="nav nav-tabs nav-justified w-100 d-flex" id="contentTabs" role="tablist">
                    <li class="nav-item flex-grow-1" role="presentation">
                        <button class="nav-link active px-2 py-3 w-100" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">
                            <i class="fas fa-align-left d-none d-md-inline-block me-1"></i> <span class="tab-text">Desc</span>
                        </button>
                    </li>
                    <li class="nav-item flex-grow-1" role="presentation">
                        <button class="nav-link px-2 py-3 w-100" id="comments-tab" data-bs-toggle="tab" data-bs-target="#comments" type="button" role="tab" aria-controls="comments" aria-selected="false">
                            <i class="fas fa-comments d-none d-md-inline-block me-1"></i> <span class="tab-text">Comments</span>
                        </button>
                    </li>
                    @if($report->remark)
                    <li class="nav-item flex-grow-1" role="presentation">
                        <button class="nav-link px-2 py-3 w-100" id="remarks-tab" data-bs-toggle="tab" data-bs-target="#remarks" type="button" role="tab" aria-controls="remarks" aria-selected="false">
                            <i class="fas fa-comment-alt d-none d-md-inline-block me-1"></i> <span class="tab-text">Remarks</span>
                        </button>
                    </li>
                    @endif
                    @if($report->approval_status === 'rejected' && $report->rejection_reason)
                    <li class="nav-item flex-grow-1" role="presentation">
                        <button class="nav-link px-2 py-3 w-100" id="rejection-tab" data-bs-toggle="tab" data-bs-target="#rejection" type="button" role="tab" aria-controls="rejection" aria-selected="false">
                            <i class="fas fa-times-circle d-none d-md-inline-block me-1"></i> <span class="tab-text">Reject</span>
                        </button>
                    </li>
                    @endif
                </ul>
                                </div>
            <div class="card-body p-md-4 p-3">
                <div class="tab-content" id="contentTabsContent">
                    <!-- Description Tab -->
                    <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                        @if(trim($report->description))
                            <div class="description-content overflow-auto bg-light p-md-4 p-3 rounded-3 border">
                                {!! nl2br(e($report->description)) !!}
                                </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-file-alt mb-3" style="font-size: 2.5rem;"></i>
                                <p class="mb-0">No description provided.</p>
                        </div>
                    @endif
    </div>

                    <!-- Comments Tab -->
                    <div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab">
            <!-- Comment Form -->
            <form id="commentForm" class="mb-4">
                @csrf
                            <div class="d-flex flex-column flex-md-row">
                                <div class="avatar-circle bg-primary text-white me-md-3 me-0 mb-3 mb-md-0 align-self-start align-self-md-start" style="flex: 0 0 40px; height: 40px;">
                        @if(auth()->user()->profile_picture)
                            <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" alt="{{ auth()->user()->name }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                        @else
                            {{ substr(auth()->user()->name, 0, 1) }}
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="commentText" rows="2" placeholder="Add a comment..." style="height: 100px"></textarea>
                            <label for="commentText">Add your thoughts...</label>
                        </div>
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                        <div class="mb-2 mb-md-0">
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Comments are visible to all users</small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Post Comment
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Comments List -->
                        <div id="commentsList" class="pt-4">
                <div class="text-center py-4" id="commentsLoading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading comments...</p>
                </div>
                <div id="commentsContainer"></div>
                <div class="text-center py-5 d-none" id="noComments">
                    <div class="empty-state mb-3">
                        <i class="fas fa-comments text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">No comments yet</h5>
                    <p class="text-muted">Be the first to share your thoughts!</p>
            </div>
        </div>
    </div>

                    <!-- Remarks Tab -->
                    @if($report->remark)
                    <div class="tab-pane fade" id="remarks" role="tabpanel" aria-labelledby="remarks-tab">
                        <div class="bg-light p-md-4 p-3 rounded-3 border">
                            {!! nl2br(e($report->remark)) !!}
        </div>
                                                    </div>
                    @endif
                    
                    <!-- Rejection Reason Tab -->
                    @if($report->approval_status === 'rejected' && $report->rejection_reason)
                    <div class="tab-pane fade" id="rejection" role="tabpanel" aria-labelledby="rejection-tab">
                        <div class="bg-danger bg-opacity-10 p-md-4 p-3 rounded-3 border border-danger">
                            <div class="d-flex flex-column flex-md-row">
                                <div class="me-md-3 me-0 mb-3 mb-md-0 text-center text-md-start">
                                    <i class="fas fa-exclamation-circle text-danger fa-2x"></i>
                                                </div>
                                <div>
                                    <h6 class="fw-bold text-danger">Rejection Reason</h6>
                                    <p class="mb-0">{!! nl2br(e($report->rejection_reason)) !!}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                    @endif
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                            </div>
                        </div>
                        
    @if($report->approval_status === 'pending' && auth()->user()->can('approve-reports'))
    <!-- Hidden Approval Forms -->
    <form id="approval-form-approved" action="{{ route('daily-reports.approval', $report) }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="status" value="approved">
        <input type="hidden" name="redirect_back" value="1">
            </form>
    
    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <form action="{{ route('daily-reports.approval', $report) }}" method="POST">
                @csrf
                    <input type="hidden" name="status" value="rejected">
                    <input type="hidden" name="redirect_back" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reject Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Reason for Rejection</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required placeholder="Please provide a reason for rejecting this report..."></textarea>
                                    </div>
                                </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Report</button>
                            </div>
                </form>
                        </div>
                                    </div>
                                    </div>
    @endif
    
    <!-- PIC Approval Forms -->
    @if($report->approval_status === 'pending' && auth()->user()->id === $report->job_pic && !auth()->user()->can('approve-reports'))
    <!-- Hidden PIC Approval Form -->
    <form id="pic-approval-form-approved" action="{{ route('daily-reports.approval', $report) }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="status" value="approved">
        <input type="hidden" name="redirect_back" value="1">
    </form>

    <!-- PIC Rejection Modal -->
    <div class="modal fade" id="picRejectModal" tabindex="-1" aria-labelledby="picRejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('daily-reports.approval', $report) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="rejected">
                    <input type="hidden" name="redirect_back" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title" id="picRejectModalLabel">Reject Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="pic_rejection_reason" class="form-label">Reason for Rejection</label>
                            <textarea class="form-control" id="pic_rejection_reason" name="rejection_reason" rows="4" required placeholder="Please provide a reason for rejecting this report..."></textarea>
                    </div>
                </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Report</button>
                </div>
            </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Form -->
    <form id="deleteForm" action="{{ route('daily-reports.destroy', $report) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="deleteConfirmationMessage">
                    Are you sure you want to delete this report?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteButton">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Comment Delete Confirmation Modal -->
    <div class="modal fade" id="commentDeleteModal" tabindex="-1" aria-labelledby="commentDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commentDeleteModalLabel">Delete Comment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this comment?</p>
                    <p class="text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmCommentDelete">Delete Comment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Comment Error Modal -->
    <div class="modal fade" id="commentErrorModal" tabindex="-1" aria-labelledby="commentErrorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="commentErrorModalLabel">Error</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle text-danger me-3" style="font-size: 1.5rem;"></i>
                        <p class="mb-0" id="commentErrorMessage">An error occurred. Please try again.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Status bar styling */
        .status-bar div {
            flex: 1;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        /* Avatar circle styling */
        .avatar-circle {
            width: 40px;
            height: 40px;
            background-color: #e9ecef;
            color: #495057;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        /* For tabs that fit in container */
        .nav-tabs {
            width: 100%;
        }
        
        .nav-tabs .nav-item {
            flex: 1 1 0;
            text-align: center;
                    }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            text-align: center;
            white-space: nowrap;
            padding: 0.75rem 0.25rem;
            font-size: 0.95rem;
                }
                
        .nav-tabs .nav-link.active {
            border-bottom: 3px solid #0d6efd;
            color: #0d6efd;
            background-color: transparent;
                }
                
        .nav-tabs .nav-link:hover:not(.active) {
            border-bottom: 3px solid #dee2e6;
                    }
                    
        /* Tab text responsive handling */
        .tab-text {
            font-size: 0.95rem;
        }
        
        /* For comment thread */
        .comment-item {
            margin-bottom: 1.5rem;
        }
        
        .comment-item:last-child {
            margin-bottom: 0;
                        }
        
        /* For description and remarks - prevent overflow */
        .description-content, .remarks-content {
            white-space: pre-line;
            max-height: 500px;
            overflow-y: auto;
            overflow-x: hidden;
            word-break: break-word;
        }
        
        /* Timeline styling */
        .timeline {
            position: relative;
            padding-left: 1.5rem;
        }
        
        .timeline:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 2px;
            background: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-marker {
            position: absolute;
            left: -1.5rem;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            transform: translateX(-50%);
        }

        /* Mobile optimizations */
        @media (max-width: 767.98px) {
            /* Prevent horizontal scrolling for content */
            .container-fluid {
                padding-left: 12px !important;
                padding-right: 12px !important;
                max-width: 100%;
                overflow-x: hidden;
            }
            
            .card {
                max-width: 100%;
            }
            
            /* Make tabs fit on small screens */
            .nav-tabs .nav-link {
                font-size: 0.8rem;
                padding: 0.75rem 0.1rem !important;
        }
        
            .tab-text {
                font-size: 0.8rem;
        }
        
            /* Content adjustments */
            .description-content, .remarks-content {
                max-height: 400px;
                padding: 0.75rem !important;
        }
        
            .comment-content {
                font-size: 0.95rem;
        }
        
            /* Better spacing for mobile */
            .card-body {
                padding: 0.75rem !important;
        }
        
            /* Optimize comment form on mobile */
            #commentForm .btn {
                width: 100%;
                margin-top: 0.5rem;
        }

            /* Better mobile spacing for comment items */
        .comment-item {
                padding-bottom: 1rem;
                margin-bottom: 1rem;
                border-bottom: 1px solid rgba(0,0,0,0.05);
            }
            
            .comment-item:last-child {
                border-bottom: none;
        }
        
            /* Attachment card more compact */
            .attachment-card {
                padding: 0.75rem !important;
        }
        
            .attachment-icon {
                font-size: 0.875rem !important;
            }
        }
        
        /* Extra small devices */
        @media (max-width: 575.98px) {
            .tab-text {
                font-size: 0.75rem;
            }
            
            .nav-tabs .nav-link {
                padding: 0.5rem 0.1rem !important;
            }
        }
    </style>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete Confirmation
            window.showDeleteConfirmation = function(message, callback) {
                const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
                document.getElementById('deleteConfirmationMessage').textContent = message;
                document.getElementById('confirmDeleteButton').onclick = callback;
                modal.show();
            };
            
            window.confirmDelete = function() {
                window.showDeleteConfirmation(
                    'Are you sure you want to delete this report?', 
                    function() {
                        document.getElementById('deleteForm').submit();
                    }
                );
            };

            // Comments Functionality
            const reportId = {{ $report->id }};
            const commentForm = document.getElementById('commentForm');
            const commentText = document.getElementById('commentText');
            const commentsContainer = document.getElementById('commentsContainer');
            const commentsLoading = document.getElementById('commentsLoading');
            const noComments = document.getElementById('noComments');
            
            // Bootstrap modals
            const commentDeleteModal = new bootstrap.Modal(document.getElementById('commentDeleteModal'));
            const commentErrorModal = new bootstrap.Modal(document.getElementById('commentErrorModal'));
            let commentIdToDelete = null;
            
            // Handle comment delete confirmation
            document.getElementById('confirmCommentDelete').addEventListener('click', function() {
                if (commentIdToDelete) {
                    deleteCommentAction(commentIdToDelete);
                    commentDeleteModal.hide();
                }
            });
            
            // Load comments when tab is clicked
            document.getElementById('comments-tab').addEventListener('click', function() {
                loadComments();
            });
            
            // Submit comment form
            commentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (commentText.value.trim() === '') {
                    return;
                }
                
                const formData = new FormData();
                formData.append('comment', commentText.value);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                fetch(`/daily-reports/${reportId}/comments`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Clear the form
                        commentText.value = '';
                        
                        // Add the new comment to the top of the list
                        if (data.comment) {
                            // Remove the "no comments" message if it's showing
                            noComments.classList.add('d-none');
                            
                            // Insert the new comment at the top
                            addCommentToList(data.comment, true);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error posting comment:', error);
                    document.getElementById('commentErrorMessage').textContent = 'Error posting comment. Please try again.';
                    commentErrorModal.show();
                });
            });
            
            // Function to add a comment to the list
            function addCommentToList(comment, prepend = false) {
                const commentEl = document.createElement('div');
                commentEl.className = 'comment-item';
                commentEl.innerHTML = `
                    <div class="d-flex flex-column flex-md-row">
                        <div class="avatar-circle me-md-3 me-0 mb-3 mb-md-0 align-self-start" style="flex: 0 0 40px; height: 40px; ${comment.user.profile_picture ? 'padding: 0;' : ''}">
                            ${comment.user.profile_picture 
                                ? `<img src="${comment.user.profile_picture}" alt="${comment.user.name}" class="rounded-circle w-100 h-100" style="object-fit: cover;">` 
                                : comment.user.name.charAt(0)
                            }
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0 fw-bold">${comment.user.name}</h6>
                                    <small class="text-muted">${comment.formatted_date}</small>
                                </div>
                                ${comment.is_owner ? `
                                <button type="button" class="btn btn-sm text-danger" onclick="deleteComment(${comment.id})">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                ` : ''}
                            </div>
                            <div class="comment-content p-3 bg-light rounded">
                                <p class="mb-0">${comment.comment}</p>
                            </div>
                        </div>
                    </div>
                `;
                
                if (prepend) {
                    commentsContainer.insertBefore(commentEl, commentsContainer.firstChild);
                } else {
                    commentsContainer.appendChild(commentEl);
                }
            }
            
            // Function to trigger delete comment modal
            window.deleteComment = function(commentId) {
                commentIdToDelete = commentId;
                commentDeleteModal.show();
            };
            
            // Function to actually delete the comment
            function deleteCommentAction(commentId) {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('_method', 'DELETE');
                
                fetch(`/comments/${commentId}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Reload comments to update the list
                        loadComments();
                    }
                })
                .catch(error => {
                    console.error('Error deleting comment:', error);
                    document.getElementById('commentErrorMessage').textContent = 'Error deleting comment. Please try again.';
                    commentErrorModal.show();
                });
            }
            
            // Function to load comments
            function loadComments() {
                commentsLoading.classList.remove('d-none');
                commentsContainer.innerHTML = '';
                noComments.classList.add('d-none');
                
                fetch(`/daily-reports/${reportId}/comments`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    commentsLoading.classList.add('d-none');
                    
                    if (data.success && data.comments && data.comments.length > 0) {
                        data.comments.forEach(comment => {
                            addCommentToList(comment);
                        });
                    } else {
                        // Show the "no comments" message
                        noComments.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    commentsLoading.classList.add('d-none');
                    console.error('Error loading comments:', error);
                    document.getElementById('commentErrorMessage').textContent = 'Error loading comments. Please refresh the page and try again.';
                    commentErrorModal.show();
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
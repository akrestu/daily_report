<x-app-layout>
    <x-slot name="header">
        Notifications
    </x-slot>

    <style>
        /* Notification list item styles */
        .notification-item {
            transition: all 0.2s ease-in-out;
            border-left: 3px solid transparent;
        }
        
        .notification-item:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
            transform: translateY(-1px);
        }
        
        .notification-item.unread {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
            border-left: 3px solid var(--bs-primary);
        }
        
        .notification-icon {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }
        
        .notification-item:hover .notification-icon {
            transform: scale(1.05);
        }
        
        /* Mobile optimizations */
        @media (max-width: 576px) {
            .notification-item {
                padding: 1rem 0.75rem !important;
            }
            
            .notification-icon {
                width: 36px;
                height: 36px;
            }
            
            .notification-text {
                font-size: 0.9rem;
            }
        }
    </style>

    <div class="row">
        <div class="col-lg-10 col-xl-8 mx-auto">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">All Notifications</h5>
                    
                    <div class="d-flex align-items-center">
                        @if($unreadCount > 0)
                            <a href="{{ route('notifications.view-all', ['mark_read' => 1]) }}" class="btn btn-primary btn-sm rounded-pill">
                                <i class="fas fa-check-double me-1"></i> Mark all as read
                            </a>
                        @endif
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                <div class="list-group-item list-group-item-action notification-item p-3 {{ $notification->is_read ? '' : 'unread' }}" 
                                     data-id="{{ $notification->id }}">
                                    <div class="d-flex w-100">
                                        <div class="notification-icon me-3">
                                            @php
                                                $iconClass = 'fas fa-info-circle';
                                                switch($notification->type) {
                                                    case 'job_approved':
                                                        $iconClass = 'fas fa-check-circle';
                                                        break;
                                                    case 'job_rejected':
                                                        $iconClass = 'fas fa-times-circle';
                                                        break;
                                                    case 'pending_approval':
                                                        $iconClass = 'fas fa-clock';
                                                        break;
                                                    case 'new_comment':
                                                        $iconClass = 'fas fa-comment';
                                                        break;
                                                    case 'welcome':
                                                        $iconClass = 'fas fa-user-plus';
                                                        break;
                                                }
                                            @endphp
                                            <i class="{{ $iconClass }}"></i>
                                        </div>
                                        
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                                <p class="mb-1 notification-text">{{ $notification->message }}</p>
                                                <small class="text-muted ms-2 notification-time">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <small>
                                                    @if($notification->daily_report_id)
                                                        <a href="/daily-reports/{{ $notification->daily_report_id }}" class="text-decoration-none">
                                                            <i class="fas fa-file-alt me-1"></i> View Report
                                                        </a>
                                                    @endif
                                                    
                                                    @if($notification->type === 'pending_approval')
                                                        <a href="/dashboard/pending-approvals" class="text-decoration-none">
                                                            <i class="fas fa-tasks me-1"></i> View Approval Queue
                                                        </a>
                                                    @endif
                                                </small>
                                                
                                                @if(!$notification->is_read)
                                                    <span class="ms-2 badge bg-primary rounded-pill">New</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="p-3">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="p-3 mb-3 rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="fas fa-bell-slash" style="font-size: 3rem; opacity: 0.4;"></i>
                            </div>
                            <h5>No notifications yet</h5>
                            <p class="text-muted">We'll notify you when something arrives</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CSRF token for AJAX requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Add click event to notification items
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    const notificationId = this.getAttribute('data-id');
                    const isLink = e.target.closest('a');
                    
                    // If clicking on a link, don't interfere with the link click
                    if (isLink) return;
                    
                    // If not read, mark as read
                    if (this.classList.contains('unread')) {
                        markNotificationAsRead(notificationId, this);
                    } else {
                        // If already read and not clicking a link, navigate based on type
                        navigateToNotificationContent(notificationId, this);
                    }
                });
            });
            
            // Mark notification as read
            function markNotificationAsRead(notificationId, element) {
                fetch('{{ route("notifications.mark-read") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ notification_id: notificationId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update UI - mark this notification as read
                        element.classList.remove('unread');
                        const newBadge = element.querySelector('.badge');
                        if (newBadge) {
                            newBadge.remove();
                        }
                        
                        // Navigate to the notification content
                        navigateToNotificationContent(notificationId, element);
                    }
                })
                .catch(error => console.error('Error marking notification as read:', error));
            }
            
            // Navigate to notification content
            function navigateToNotificationContent(notificationId, element) {
                // Extract notification type from the icon
                const iconElement = element.querySelector('.notification-icon i');
                const type = iconElement ? getNotificationType(iconElement.className) : null;
                
                // Get links
                const reportLink = element.querySelector('a[href^="/daily-reports/"]');
                const approvalLink = element.querySelector('a[href="/dashboard/pending-approvals"]');
                
                // Navigate based on available links
                if (reportLink) {
                    window.location.href = reportLink.getAttribute('href');
                } else if (approvalLink) {
                    window.location.href = approvalLink.getAttribute('href');
                }
            }
            
            // Helper to determine notification type from icon class
            function getNotificationType(iconClass) {
                if (iconClass.includes('check-circle')) return 'job_approved';
                if (iconClass.includes('times-circle')) return 'job_rejected';
                if (iconClass.includes('clock')) return 'pending_approval';
                if (iconClass.includes('comment')) return 'new_comment';
                if (iconClass.includes('user-plus')) return 'welcome';
                return 'default';
            }
        });
    </script>
    @endpush
</x-app-layout> 
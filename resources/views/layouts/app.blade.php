<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Daily Job Report System</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <link rel="manifest" href="{{ asset('web/site.webmanifest') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    
    <!-- App Styles -->
    @if(app()->environment('local') && file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        @php
            // Use Vite's manifest file to get the correct asset paths
            $manifest = null;
            try {
                if(file_exists(public_path('build/.vite/manifest.json'))) {
                    $manifest = json_decode(file_get_contents(public_path('build/.vite/manifest.json')), true);
                }
            } catch (\Exception $e) {
                // Silently fail if manifest can't be read
            }
            
            $cssFile = $manifest && isset($manifest['resources/css/app.css']) 
                ? 'build/' . $manifest['resources/css/app.css']['file'] 
                : (file_exists(public_path('build/assets/app-PYGI7GKI.css')) 
                    ? 'build/assets/app-PYGI7GKI.css' 
                    : 'css/app.css');
                    
            $jsFile = $manifest && isset($manifest['resources/js/app.js']) 
                ? 'build/' . $manifest['resources/js/app.js']['file'] 
                : (file_exists(public_path('build/assets/app-BW-0XrbD.js')) 
                    ? 'build/assets/app-BW-0XrbD.js' 
                    : 'js/app.js');
        @endphp
        <link rel="stylesheet" href="{{ asset($cssFile) }}">
        <script src="{{ asset($jsFile) }}" defer></script>
    @endif

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        
        .content-wrapper {
            padding-left: 280px;
            transition: all 0.3s ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        @media (max-width: 991.98px) {
            .content-wrapper {
                padding-left: 0;
            }
        }
        
        /* When overlay is active on mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1020;
        }
        
        body.sidebar-open .sidebar-overlay {
            display: block;
        }
        
        /* Sticky Header */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 1020;
            transition: box-shadow 0.3s ease;
        }
        
        .shadow-scroll {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        /* Icon Circle Fix */
        .rounded-circle {
            aspect-ratio: 1/1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .rounded-circle i {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Footer Branding */
        .brand-logo-sm {
            width: 32px;
            height: 32px;
            flex-shrink: 0;
        }
        
        .footer .text-primary {
            color: #0d6efd !important;
        }
        
        /* Notification Styles */
        .notification-badge {
            position: absolute;
            top: -4px;
            right: -8px;
            font-size: 0.7rem;
            padding: 0;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #fff;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
            animation: pulse 2s infinite;
            line-height: 1;
            transform: translateY(0); /* Reset any transform */
        }
        
        /* For numbers wider than the default width */
        .notification-badge.multi-digit {
            width: auto;
            min-width: 18px;
            padding: 0 4px;
        }
        
        /* Ensure proper number alignment */
        .notification-badge span {
            display: inline-block;
            transform: translateY(0);
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(var(--bs-danger-rgb), 0.7);
            }
            70% {
                box-shadow: 0 0 0 6px rgba(var(--bs-danger-rgb), 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(var(--bs-danger-rgb), 0);
            }
        }
        
        .notification-dropdown {
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
            padding: 0;
            border: 0;
            border-radius: 0.5rem;
            z-index: 1050;  /* Ensure dropdown appears above other elements */
        }
        
        .notification-header {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: rgba(var(--bs-primary-rgb), 0.03);
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
        
        .notification-footer {
            padding: 0.5rem 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            text-align: center;
            background-color: rgba(var(--bs-primary-rgb), 0.03);
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }
        
        .notification-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            transition: all 0.2s ease-in-out;
        }
        
        .notification-item:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
            transform: translateY(-1px);
        }
        
        .notification-item.unread {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
            position: relative;
        }
        
        .notification-item.unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: var(--bs-primary);
            border-radius: 0 2px 2px 0;
        }
        
        .notification-item .notification-time {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .notification-item .notification-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .notification-item .notification-text {
            font-size: 0.875rem;
            margin-bottom: 0;
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
            margin-right: 0.75rem;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }
        
        .notification-item:hover .notification-icon {
            transform: scale(1.05);
        }
        
        .empty-notifications {
            padding: 2rem 1rem;
            text-align: center;
            color: #6c757d;
        }
        
        .fs-smallest {
            font-size: 0.65rem;
        }
        
        /* Notification action buttons */
        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-icon i {
            font-size: 0.875rem;
        }
        
        #notificationDropdown:hover i {
            transform: scale(1.1);
        }
        
        .notification-container {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Mobile-specific adjustments for notification dropdown */
        @media (max-width: 767.98px) {
            .notification-dropdown {
                width: calc(100% - 30px) !important;
                max-width: 100% !important;
                position: fixed !important;
                top: 70px !important;
                left: 15px !important;
                right: 15px !important;
                transform: none !important;
                max-height: 75vh !important;
                margin-top: 0 !important;
                z-index: 1051 !important;
                -webkit-overflow-scrolling: touch;
                touch-action: pan-y;
                box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2) !important;
                border-radius: 16px !important;
                animation: mobileDropdownIn 0.3s ease;
            }
            
            @keyframes mobileDropdownIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .notification-header, .notification-footer {
                padding: 15px;
                position: sticky;
                background-color: #fff;
            }
            
            .notification-header {
                top: 0;
                border-bottom: 1px solid rgba(0, 0, 0, 0.08);
                z-index: 2;
            }
            
            .notification-footer {
                bottom: 0;
                border-top: 1px solid rgba(0, 0, 0, 0.08);
                z-index: 2;
            }
            
            #notificationList {
                max-height: calc(75vh - 110px);
                overflow-y: auto;
            }
            
            .notification-item {
                padding: 15px;
                min-height: 80px;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            }
            
            /* Improved touch targets for mobile */
            #notificationDropdown {
                padding: 12px;
                margin: -12px;
            }
            
            .notification-icon {
                width: 48px;
                height: 48px;
            }
        }
    </style>

    <!-- Prevent Alpine.js multiple instances -->
    <script>
        window.alpineInitialized = false;
    </script>
    @livewireStyles
</head>
<body>
    <!-- Sidebar Navigation -->
    @include('layouts.navigation')
    
    <!-- Main Content Wrapper -->
    <div class="content-wrapper">
        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow-sm sticky-header" id="mainHeader">
                <div class="container-fluid py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="fs-4 text-dark mb-0">
                            {{ $header }}
                        </h2>
                        
                        @auth
                        <div class="d-flex align-items-center">
                            <!-- Notification Bell -->
                            <div class="dropdown me-3">
                                <button class="btn" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="notification-container">
                                        <i class="fas fa-bell fs-4" id="notificationIcon"></i>
                                        <span class="notification-badge badge rounded-pill bg-danger d-flex align-items-center justify-content-center" id="notificationBadge" style="display: none;">0</span>
                                    </div>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end notification-dropdown shadow-sm p-0" aria-labelledby="notificationDropdown">
                                    <div class="notification-header">
                                        <span class="fw-bold">Notifications</span>
                                        <div class="d-flex">
                                            <button class="btn btn-sm btn-icon btn-primary rounded-circle me-1" id="markAllReadBtn" data-bs-toggle="tooltip" title="Mark all as read">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                            <button class="btn btn-sm btn-icon btn-outline-danger rounded-circle" id="clearAllBtn" data-bs-toggle="tooltip" title="Clear all notifications">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="notificationList">
                                        <div class="empty-notifications">
                                            <i class="fas fa-bell-slash mb-2" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                            <p>No notifications yet</p>
                                        </div>
                                    </div>
                                    <div class="notification-footer">
                                        <a href="{{ route('notifications.view-all') }}" class="text-decoration-none small d-flex align-items-center justify-content-center">
                                            <i class="fas fa-history me-1"></i> View all notifications
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- User profile can be added here if needed -->
                        </div>
                        @endauth
                    </div>
                </div>
            </header>
        @endif

        <!-- Main Content -->
        <main class="flex-grow-1 py-4">
            <div class="container-fluid px-4">
                <!-- Flash Messages - Now handled by modal component -->
                {{ $slot }}
            </div>
        </main>

        <!-- Footer -->
        <footer class="footer py-3 bg-white border-top mt-auto">
            <div class="container-fluid px-4">
                <div class="row align-items-center">
                    <div class="col-md-12 text-center">
                        <span class="text-muted small">Created with <span class="text-danger">♥</span> by ak.restu</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Sidebar overlay for mobile -->
    <div class="sidebar-overlay"></div>
    
    <!-- Include modal dialogs component -->
    <x-modal-dialogs />

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>
    
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Global Bootstrap Initialization Script -->
    <script>
        // Ensure Bootstrap is fully loaded
        window.addEventListener('load', function() {
            try {
                // Initialize all dropdowns
                document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(dropdownToggleEl) {
                    new bootstrap.Dropdown(dropdownToggleEl);
                });
                
                // Initialize all tooltips
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(tooltipTriggerEl) {
                    new bootstrap.Tooltip(tooltipTriggerEl);
                });
                
                // Initialize all popovers
                document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(popoverTriggerEl) {
                    new bootstrap.Popover(popoverTriggerEl);
                });
                
                console.log('Bootstrap components initialized globally');
            } catch (error) {
                console.error('Error initializing Bootstrap components:', error);
            }
        });
    </script>
    
    <!-- Notification Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CSRF token for AJAX requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Elements
            const notificationBadge = document.getElementById('notificationBadge');
            const notificationList = document.getElementById('notificationList');
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            const clearAllBtn = document.getElementById('clearAllBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationIcon = document.getElementById('notificationIcon');
            
            // Store notifications data
            let notificationsData = [];
            
            // Mobile detection
            const isMobile = window.innerWidth < 768;
            
            // Handle window resize
            window.addEventListener('resize', function() {
                // Reset dropdown position when window is resized
                const dropdownMenu = document.querySelector('.notification-dropdown');
                if (dropdownMenu) {
                    if (window.innerWidth >= 768) {
                        // Reset to default styles for desktop
                        dropdownMenu.style.position = '';
                        dropdownMenu.style.bottom = '';
                        dropdownMenu.style.top = '';
                        dropdownMenu.style.left = '';
                        dropdownMenu.style.right = '';
                        dropdownMenu.style.transform = '';
                    }
                }
            });
            
            // Initialize the notification dropdown
            if (notificationDropdown) {
                // Create a Bootstrap dropdown instance manually
                const dropdownInstance = new bootstrap.Dropdown(notificationDropdown, {
                    display: 'dynamic'
                });
                
                // Add click event listener
                notificationDropdown.addEventListener('click', function(e) {
                    dropdownInstance.toggle();
                    
                    // On mobile, ensure proper positioning after dropdown is shown
                    if (isMobile) {
                        setTimeout(() => {
                            const dropdownMenu = document.querySelector('.notification-dropdown');
                            if (dropdownMenu && dropdownMenu.classList.contains('show')) {
                                // Ensure scroll doesn't interfere with dropdown
                                const topPosition = window.scrollY > 0 ? (60 + window.scrollY) : 70;
                                if (window.scrollY > 0) {
                                    dropdownMenu.style.top = `${topPosition}px`;
                                }
                            }
                        }, 10);
                    }
                });
                
                // Add touch event listener for mobile
                notificationDropdown.addEventListener('touchend', function(e) {
                    e.preventDefault();
                    dropdownInstance.toggle();
                });
                
                // Prevent dropdown from closing when clicking inside
                const notificationDropdownMenu = document.querySelector('.notification-dropdown');
                if (notificationDropdownMenu) {
                    notificationDropdownMenu.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                    
                    // Add touch event for mobile
                    notificationDropdownMenu.addEventListener('touchend', function(e) {
                        e.stopPropagation();
                    });
                }
            }
            
            // Function to load notifications
            function loadNotifications() {
                fetch('{{ route("notifications.get") }}')
                    .then(response => response.json())
                    .then(data => {
                        // Store the notifications data for later use
                        notificationsData = data.notifications;
                        updateNotificationUI(data.notifications, data.unread_count);
                    })
                    .catch(error => console.error('Error fetching notifications:', error));
            }
            
            // Update notification UI
            function updateNotificationUI(notifications, unreadCount) {
                // Update badge and icon
                if (unreadCount > 0) {
                    notificationBadge.innerHTML = unreadCount > 99 ? '99+' : unreadCount;
                    notificationBadge.style.display = 'flex';
                    notificationIcon.classList.add('text-warning');
                    
                    // Add multi-digit class if needed
                    if (unreadCount > 9) {
                        notificationBadge.classList.add('multi-digit');
                    } else {
                        notificationBadge.classList.remove('multi-digit');
                    }
                } else {
                    notificationBadge.style.display = 'none';
                    notificationIcon.classList.remove('text-warning');
                }
                
                // Update notification list
                if (notifications && notifications.length > 0) {
                    let notificationHTML = '';
                    
                    notifications.forEach(notification => {
                        let iconClass = 'fas fa-info-circle';
                        
                        // Set icon based on notification type
                        switch(notification.type) {
                            case 'job_approved':
                                iconClass = 'fas fa-check-circle';
                                break;
                            case 'job_rejected':
                                iconClass = 'fas fa-times-circle';
                                break;
                            case 'pending_approval':
                                iconClass = 'fas fa-clock';
                                break;
                            case 'new_comment':
                                iconClass = 'fas fa-comment';
                                break;
                            case 'welcome':
                                iconClass = 'fas fa-user-plus';
                                break;
                        }
                        
                        const unreadClass = notification.is_read ? '' : 'unread';
                        const createdAt = new Date(notification.created_at);
                        const timeAgo = getTimeAgo(createdAt);
                        
                        notificationHTML += `
                            <div class="notification-item d-flex align-items-start ${unreadClass}" data-id="${notification.id}">
                                <div class="notification-icon">
                                    <i class="${iconClass}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="notification-text mb-1">${notification.message}</p>
                                    <div class="d-flex align-items-center">
                                        <span class="notification-time"><i class="fas fa-clock me-1 opacity-75" style="font-size: 0.7rem;"></i>${timeAgo}</span>
                                        ${notification.is_read ? '' : '<span class="ms-2 badge bg-primary rounded-pill fs-smallest">New</span>'}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    notificationList.innerHTML = notificationHTML;
                    
                    // Add click event to notification items
                    document.querySelectorAll('.notification-item').forEach(item => {
                        // Handle click events on notification items
                        item.addEventListener('click', function(e) {
                            const notificationId = this.getAttribute('data-id');
                            markNotificationAsRead(notificationId);
                        });
                        
                        // Handle touch events on mobile
                        if (isMobile) {
                            item.addEventListener('touchend', function(e) {
                                e.preventDefault();
                                const notificationId = this.getAttribute('data-id');
                                markNotificationAsRead(notificationId);
                            });
                        }
                    });
                } else {
                    notificationList.innerHTML = `
                        <div class="empty-notifications">
                            <div class="p-2 mb-2 rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                <i class="fas fa-bell-slash" style="font-size: 2rem; opacity: 0.4;"></i>
                            </div>
                            <p class="mb-1">No notifications yet</p>
                            <p class="small text-muted">We'll notify you when something arrives</p>
                        </div>
                    `;
                }
            }
            
            // Mark notification as read
            function markNotificationAsRead(notificationId) {
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
                        const notificationItem = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
                        if (notificationItem) {
                            notificationItem.classList.remove('unread');
                            const newBadge = notificationItem.querySelector('.badge');
                            if (newBadge) {
                                newBadge.remove();
                            }
                        }
                        
                        // Update badge counter
                        if (data.unread_count !== undefined) {
                            if (data.unread_count > 0) {
                                notificationBadge.innerHTML = data.unread_count > 99 ? '99+' : data.unread_count;
                                notificationBadge.style.display = 'flex';
                                
                                if (data.unread_count > 9) {
                                    notificationBadge.classList.add('multi-digit');
                                } else {
                                    notificationBadge.classList.remove('multi-digit');
                                }
                            } else {
                                notificationBadge.style.display = 'none';
                                notificationIcon.classList.remove('text-warning');
                            }
                        }
                        
                        // Get the notification object from the stored notifications data
                        const notification = notificationsData.find(n => n.id == notificationId);
                        if (notification) {
                            // Add a small delay before navigating to allow the read status to update visually
                            setTimeout(() => {
                                // Navigate based on notification type
                                if (notification.comment_id && notification.daily_report_id) {
                                    // If there's both a comment and daily report, navigate to the specific comment section
                                    window.location.href = `/daily-reports/${notification.daily_report_id}#comment-${notification.comment_id}`;
                                } else if (notification.daily_report_id) {
                                    window.location.href = `/daily-reports/${notification.daily_report_id}`;
                                } else if (notification.type === 'pending_approval') {
                                    // For pending approval notifications, go to the approval queue
                                    window.location.href = '/dashboard/pending-approvals';
                                } else if (notification.type === 'job_approved' || notification.type === 'job_rejected') {
                                    // For job status notifications, navigate to the daily report
                                    if (notification.daily_report_id) {
                                        window.location.href = `/daily-reports/${notification.daily_report_id}`;
                                    }
                                } else if (notification.type === 'welcome') {
                                    // For welcome notifications, don't navigate
                                    // Just mark as read and close the dropdown
                                    const notificationDropdown = document.querySelector('#notificationDropdown');
                                    if (notificationDropdown) {
                                        const dropdownToggle = notificationDropdown.querySelector('.dropdown-toggle');
                                        if (dropdownToggle) {
                                            dropdownToggle.click();
                                        }
                                    }
                                }
                            }, 100);
                        }
                    }
                })
                .catch(error => console.error('Error marking notification as read:', error));
            }
            
            // Mark all notifications as read
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    fetch('{{ route("notifications.mark-all-read") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update UI - all notifications are now read
                            document.querySelectorAll('.notification-item.unread').forEach(item => {
                                item.classList.remove('unread');
                                const newBadge = item.querySelector('.badge');
                                if (newBadge) {
                                    newBadge.remove();
                                }
                            });
                            
                            // Update badge counter
                            notificationBadge.style.display = 'none';
                            notificationIcon.classList.remove('text-warning');
                            
                            // Reload the notifications to refresh the UI
                            loadNotifications();
                        }
                    })
                    .catch(error => console.error('Error marking all notifications as read:', error));
                });
            }

            // Clear all notifications
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    showDeleteConfirmation('Are you sure you want to clear all notifications? This action cannot be undone.', function() {
                        fetch('{{ route("notifications.clear-all") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Clear the notifications data
                                notificationsData = [];
                                
                                // Update the UI
                                notificationList.innerHTML = `
                                    <div class="empty-notifications">
                                        <div class="p-2 mb-2 rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                            <i class="fas fa-bell-slash" style="font-size: 2rem; opacity: 0.4;"></i>
                                        </div>
                                        <p class="mb-1">No notifications yet</p>
                                        <p class="small text-muted">We'll notify you when something arrives</p>
                                    </div>
                                `;
                                
                                // Hide the badge
                                notificationBadge.style.display = 'none';
                                notificationIcon.classList.remove('text-warning');
                            }
                        })
                        .catch(error => console.error('Error clearing all notifications:', error));
                    });
                });
            }
            
            // Format time ago
            function getTimeAgo(date) {
                const seconds = Math.floor((new Date() - date) / 1000);
                
                let interval = Math.floor(seconds / 31536000);
                if (interval >= 1) {
                    return interval + ' year' + (interval === 1 ? '' : 's') + ' ago';
                }
                
                interval = Math.floor(seconds / 2592000);
                if (interval >= 1) {
                    return interval + ' month' + (interval === 1 ? '' : 's') + ' ago';
                }
                
                interval = Math.floor(seconds / 86400);
                if (interval >= 1) {
                    return interval + ' day' + (interval === 1 ? '' : 's') + ' ago';
                }
                
                interval = Math.floor(seconds / 3600);
                if (interval >= 1) {
                    return interval + ' hour' + (interval === 1 ? '' : 's') + ' ago';
                }
                
                interval = Math.floor(seconds / 60);
                if (interval >= 1) {
                    return interval + ' minute' + (interval === 1 ? '' : 's') + ' ago';
                }
                
                return 'just now';
            }
            
            // Load notifications on page load
            if (notificationBadge && notificationList) {
                loadNotifications();
                
                // Refresh notifications every 60 seconds
                setInterval(loadNotifications, 60000);
            }
            
            // Sticky header shadow on scroll
            const header = document.getElementById('mainHeader');
            if (header) {
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 10) {
                        header.classList.add('shadow-scroll');
                    } else {
                        header.classList.remove('shadow-scroll');
                    }
                });
            }
            
            // Always set light theme
            document.documentElement.setAttribute('data-bs-theme', 'light');
            // Set light theme in local storage to ensure consistency
            localStorage.setItem('theme', 'light');
            
            // Make sure sidebar has gradient background
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.remove('sidebar-dark');
                sidebar.style.background = 'linear-gradient(135deg, #6610f2, #0d6efd)';
            }
        });
    </script>

    @livewireScripts
    
    <!-- Fix Alpine.js multiple instances issue -->
    <script>
    (function() {
        // Execute after Livewire scripts have loaded
        setTimeout(function() {
            // Check if Alpine was initialized multiple times
            if (typeof window.__alpine_was_already_initialized !== 'undefined' && window.__alpine_was_already_initialized) {
                console.log('Alpine.js was already initialized, cleaning up duplicate...');
                
                // If Alpine was already initialized, try to clean up the duplicate
                if (window.Alpine) {
                    // Try to clean up observers
                    if (window.Alpine.stopObservingMutations) {
                        window.Alpine.stopObservingMutations();
                    }
                    
                    console.log('Alpine.js duplicate instance cleaned up');
                }
            } else {
                window.__alpine_was_already_initialized = true;
                console.log('Alpine.js first initialization marked');
            }
        }, 100); // Small delay to ensure Livewire scripts have loaded
    })();
    </script>
    
    @stack('scripts')
</body>
</html>
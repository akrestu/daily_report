<!-- Sidebar Navigation -->
<div class="sidebar border-end shadow-sm sidebar-mobile" id="sidebar" style="background: linear-gradient(135deg, #6610f2, #0d6efd);">
    <!-- Sidebar Header -->
    <div class="sidebar-header p-3 border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important;">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none">
            <div class="logo-bg rounded-circle bg-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; flex-shrink: 0;">
                <i class="fas fa-clipboard-list text-primary" style="font-size: 16px;"></i>
            </div>
            <span class="logo-text fw-bold text-white fs-5">Daily Job Report</span>
        </a>
    </div>
    
    <!-- Mobile User Info - Only visible on mobile -->
    @auth
    <div class="mobile-user-info d-lg-none border-bottom p-3" style="border-color: rgba(255, 255, 255, 0.1) !important;">
        <div class="d-flex align-items-center mb-2">
            <div class="avatar bg-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                @if(Auth::user()->profile_picture)
                    <img src="{{ Auth::user()->profile_picture_url }}" alt="Profile" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    <i class="fas fa-user text-primary" style="font-size: 16px;"></i>
                @endif
            </div>
            <div class="user-info-text">
                <div class="fw-medium text-white">{{ Auth::user()->name }}</div>
                <div class="text-white-50 small">{{ Auth::user()->department->name ?? 'No Department' }}</div>
            </div>
        </div>
        <div class="d-flex mt-2">
            <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-light text-primary me-2 flex-grow-1">
                <i class="fas fa-user-edit me-1"></i> Profile
            </a>
            <form method="POST" action="{{ route('logout') }}" class="flex-grow-1">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light w-100">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </button>
            </form>
        </div>
    </div>
    @endauth
    
    <!-- Sidebar Menu -->
    <div class="sidebar-menu p-3">
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a class="nav-link rounded-pill {{ request()->routeIs('dashboard') ? 'active bg-white text-primary' : 'text-white' }}" 
                   href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item mb-3">
                <span class="sidebar-heading px-3 py-2 text-uppercase fw-bold text-white-50 small d-block">Reports</span>
            </li>
            
            <li class="nav-item mb-2">
                <a class="nav-link rounded-pill {{ request()->routeIs('daily-reports.create') ? 'active bg-white text-primary' : 'text-white' }}" 
                   href="{{ route('daily-reports.create') }}">
                    <i class="fas fa-plus me-2"></i>
                    <span>Create Report</span>
                </a>
            </li>
            
            <li class="nav-item mb-2">
                <a class="nav-link rounded-pill {{ request()->routeIs('daily-reports.user-jobs') ? 'active bg-white text-primary' : 'text-white' }}" 
                   href="{{ route('daily-reports.user-jobs') }}">
                    <i class="fas fa-tasks me-2"></i>
                    <span>My Reports</span>
                </a>
            </li>
            
            @if(auth()->user()->isAdmin() || auth()->user()->isDepartmentHead() || auth()->user()->isLeader())
            <li class="nav-item mb-2">
                <a class="nav-link rounded-pill {{ request()->routeIs('daily-reports.assigned-jobs') ? 'active bg-white text-primary' : 'text-white' }}" 
                   href="{{ route('daily-reports.assigned-jobs') }}">
                    <i class="fas fa-user-check me-2"></i>
                    <span>Assigned Reports</span>
                </a>
            </li>
            @endif
            
            <li class="nav-item mb-2">
                <a class="nav-link rounded-pill {{ request()->routeIs('daily-reports.index') ? 'active bg-white text-primary' : 'text-white' }}" 
                   href="{{ route('daily-reports.index') }}">
                    <i class="fas fa-list me-2"></i>
                    <span>All Reports</span>
                </a>
            </li>
            
            @if(auth()->user()->isAdmin() || auth()->user()->isDepartmentHead() || auth()->user()->isLeader())
            <li class="nav-item mb-2">
                <a class="nav-link rounded-pill {{ request()->routeIs('daily-reports.pending') ? 'active bg-white text-primary' : 'text-white' }}" 
                   href="{{ route('daily-reports.pending') }}">
                    <i class="fas fa-clock me-2"></i>
                    <span>Pending Reports</span>
                </a>
            </li>
            @endif
            
            <li class="nav-item mb-3">
                <span class="sidebar-heading px-3 py-2 text-uppercase fw-bold text-white-50 small d-block">Organization</span>
            </li>
            
            <li class="nav-item mb-2">
                <a class="nav-link rounded-pill {{ request()->routeIs('organization.chart') ? 'active bg-white text-primary' : 'text-white' }}" 
                   href="{{ route('organization.chart') }}">
                    <i class="fas fa-sitemap me-2"></i>
                    <span>Team Structure</span>
                </a>
            </li>
            
            @if(auth()->user()->role_id == 1 || auth()->check() && auth()->user()->isAdmin())
            <li class="nav-item mb-3">
                <span class="sidebar-heading px-3 py-2 text-uppercase fw-bold text-white-50 small d-block">Administration</span>
            </li>
            
            <li class="nav-item mb-2">
                <a class="nav-link rounded-pill {{ request()->routeIs('admin.users.*') ? 'active bg-white text-primary' : 'text-white' }}" 
                   href="{{ route('admin.users.index') }}">
                    <i class="fas fa-users me-2"></i>
                    <span>User Management</span>
                </a>
            </li>
            
            <li class="nav-item mb-2">
                <a class="nav-link rounded-pill {{ request()->routeIs('admin.departments.*') ? 'active bg-white text-primary' : 'text-white' }}" 
                   href="{{ route('admin.departments.index') }}">
                    <i class="fas fa-building me-2"></i>
                    <span>Department Management</span>
                </a>
            </li>
            @endif
        </ul>
    </div>
    
    <!-- Sidebar Footer - Only visible on desktop -->
    <div class="sidebar-footer mt-auto border-top p-3 d-none d-lg-block" style="border-color: rgba(255, 255, 255, 0.1) !important;">
        @auth
        <div class="user-profile d-flex align-items-center">
            <div class="avatar bg-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                @if(Auth::user()->profile_picture)
                    <img src="{{ Auth::user()->profile_picture_url }}" alt="Profile" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    <i class="fas fa-user text-primary" style="font-size: 16px;"></i>
                @endif
            </div>
            <div class="dropdown w-100">
                <a class="dropdown-toggle text-decoration-none text-white w-100 d-flex align-items-center justify-content-between" href="#" role="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="fw-medium text-truncate" style="max-width: 140px;">{{ Auth::user()->name }}</span>
                    <i class="fas fa-chevron-down small ms-1"></i>
                </a>
                <ul class="dropdown-menu w-100 shadow-sm" aria-labelledby="userMenuDropdown">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user-edit me-2"></i>Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        @else
        <div class="d-grid">
            <a class="btn btn-light text-primary" href="{{ route('login') }}">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </a>
        </div>
        @endauth
    </div>
</div>

<!-- Mobile Nav Toggle -->
<div class="mobile-nav-toggle d-lg-none">
    <button class="btn btn-primary rounded-circle shadow" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
</div>

<style>
    /* Sidebar Styles */
    .sidebar {
        width: 280px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        z-index: 1030;
    }
    
    .sidebar-menu {
        flex: 1;
        overflow-y: auto;
    }
    
    .sidebar .nav-link {
        padding: 0.8rem 1.2rem;
        transition: all 0.2s ease;
    }
    
    .sidebar .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.15);
        transform: translateX(3px);
    }
    
    .sidebar .nav-link.active {
        font-weight: 500;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    /* Mobile-specific styles */
    .mobile-user-info {
        display: none;
    }
    
    @media (max-width: 991.98px) {
        .mobile-user-info {
            display: block;
        }
        
        .mobile-user-info .btn {
            transition: all 0.2s ease;
        }
        
        .mobile-user-info .btn:hover {
            transform: translateY(-2px);
        }
    }
    
    /* Style scrollbar for webkit browsers */
    .sidebar-menu::-webkit-scrollbar {
        width: 6px;
    }
    
    .sidebar-menu::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }
    
    .sidebar-menu::-webkit-scrollbar-thumb:hover {
        background-color: rgba(255, 255, 255, 0.3);
    }
    
    .sidebar-menu::-webkit-scrollbar-track {
        background-color: transparent;
    }
    
    /* Mobile Toggle Button */
    .mobile-nav-toggle {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 1040;
    }
    
    .mobile-nav-toggle .btn {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Responsive styles */
    @media (max-width: 991.98px) {
        .sidebar {
            transform: translateX(-100%);
            height: 100%;
            overflow-y: auto;
            border-right: none !important; /* Remove border for mobile */
            box-shadow: none !important; /* Remove default shadow */
        }
        
        .sidebar.show {
            transform: translateX(0);
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.25) !important; /* Enhanced shadow when visible */
        }
        
        .sidebar-mobile {
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
        }
        
        body.sidebar-open {
            overflow: hidden;
        }
    }
    
    /* User dropdown styles */
    .user-profile .dropdown-toggle::after {
        display: none; /* Hide Bootstrap's default caret */
    }
    
    .user-profile .dropdown-menu {
        min-width: 100%;
        margin-top: 0.5rem;
    }
    
    .user-profile .dropdown-item {
        padding: 0.6rem 1rem;
    }
    
    .user-profile .dropdown-item:active,
    .user-profile .dropdown-item:focus {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                document.body.classList.toggle('sidebar-open');
                
                // Change icon based on state
                const icon = this.querySelector('i');
                if (sidebar.classList.contains('show')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth < 992 && 
                sidebar.classList.contains('show') && 
                !sidebar.contains(event.target) && 
                !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
                
                const icon = sidebarToggle.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
        
        // Initialize user dropdown menu
        const userMenuDropdown = document.getElementById('userMenuDropdown');
        if (userMenuDropdown) {
            // Make sure Bootstrap is fully loaded before initializing the dropdown
            setTimeout(() => {
                try {
                    // Check if Bootstrap dropdown functionality is available
                    if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                        const dropdown = new bootstrap.Dropdown(userMenuDropdown);
                        
                        // Add click handler for manual toggling
                        userMenuDropdown.addEventListener('click', function(e) {
                            e.preventDefault();
                            dropdown.toggle();
                        });
                        
                        console.log('Dropdown initialized successfully');
                    } else {
                        console.error('Bootstrap dropdown functionality not available');
                    }
                } catch (error) {
                    console.error('Error initializing dropdown:', error);
                }
            }, 100);
        }
    });
</script>
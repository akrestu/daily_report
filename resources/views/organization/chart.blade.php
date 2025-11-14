<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-xl text-gray-800 leading-tight">
                Organization Chart
            </h2>
        </div>
    </x-slot>
    
    <div class="container mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h4 class="card-title mb-0">
                    <i class="fas fa-sitemap me-2 text-primary"></i>
                    {{ $department->name }} Organization Chart
                </h4>
            </div>
            <div class="card-body p-0">
                <div class="bg-light p-3 border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <p class="mb-2 mb-md-0"><strong>Department Code:</strong> {{ $department->code }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-0"><strong>Total Members:</strong> {{ $department->users->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-3 p-md-4">
                    <div class="row">
                        <div class="col-12 text-center mb-5">
                            <!-- Organization Tree Visualization -->
                            <!-- Note: Admin role excluded - it's for system management, not operational hierarchy -->
                            <div class="org-chart">
                                <!-- Level 5 (Highest Operational Level) -->
                                @if($organizationTree['level5']->count() > 0)
                                <div class="org-chart-level-1 mb-4 mb-md-5">
                                    <div class="row justify-content-center">
                                        @foreach($organizationTree['level5'] as $user)
                                        <div class="col-6 col-sm-4 col-md-4 col-lg-3 mb-3">
                                            <div class="org-chart-node level5-node">
                                                <div class="avatar bg-primary mx-auto mb-2">
                                                    @if($user->profile_picture)
                                                        <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                                                    @else
                                                        <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                    @endif
                                                </div>
                                                <div class="node-content">
                                                    <h5 class="mb-0 node-name">{{ $user->name }}</h5>
                                                    <p class="role-badge level5 mb-0">Level 5</p>
                                                </div>
                                            </div>
                                            @if($organizationTree['level4']->count() > 0)
                                            <div class="vertical-line small"></div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Level 4 -->
                                @if($organizationTree['level4']->count() > 0)
                                <div class="org-chart-level-3 mb-4 mb-md-5">
                                    <div class="horizontal-line"></div>
                                    <div class="row justify-content-center">
                                        @foreach($organizationTree['level4'] as $user)
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                                            <div class="org-chart-node level4-node">
                                                <div class="avatar bg-info mx-auto mb-2">
                                                    @if($user->profile_picture)
                                                        <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                                                    @else
                                                        <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                    @endif
                                                </div>
                                                <div class="node-content">
                                                    <h6 class="mb-0 node-name">{{ $user->name }}</h6>
                                                    <p class="role-badge level4 mb-0">Level 4</p>
                                                </div>
                                            </div>
                                            @if($organizationTree['level3']->count() > 0)
                                            <div class="vertical-line small"></div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Level 3 -->
                                @if($organizationTree['level3']->count() > 0)
                                <div class="org-chart-level-4 mb-4 mb-md-5">
                                    <div class="horizontal-line"></div>
                                    <div class="row justify-content-center">
                                        @foreach($organizationTree['level3'] as $user)
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                                            <div class="org-chart-node level3-node">
                                                <div class="avatar bg-warning mx-auto mb-2">
                                                    @if($user->profile_picture)
                                                        <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                                                    @else
                                                        <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                    @endif
                                                </div>
                                                <div class="node-content">
                                                    <h6 class="mb-0 node-name">{{ $user->name }}</h6>
                                                    <p class="role-badge level3 mb-0">Level 3</p>
                                                </div>
                                            </div>
                                            @if($organizationTree['level2']->count() > 0)
                                            <div class="vertical-line small"></div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Level 2 -->
                                @if($organizationTree['level2']->count() > 0)
                                <div class="org-chart-level-5 mb-4 mb-md-5">
                                    <div class="horizontal-line"></div>
                                    <div class="row justify-content-center">
                                        @foreach($organizationTree['level2'] as $user)
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                                            <div class="org-chart-node level2-node">
                                                <div class="avatar bg-success mx-auto mb-2">
                                                    @if($user->profile_picture)
                                                        <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                                                    @else
                                                        <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                    @endif
                                                </div>
                                                <div class="node-content">
                                                    <h6 class="mb-0 node-name">{{ $user->name }}</h6>
                                                    <p class="role-badge level2 mb-0">Level 2</p>
                                                </div>
                                            </div>
                                            @if($organizationTree['level1']->count() > 0)
                                            <div class="vertical-line small"></div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Level 1 (Lowest) -->
                                @if($organizationTree['level1']->count() > 0)
                                <div class="org-chart-level-6">
                                    <div class="horizontal-line"></div>
                                    <div class="row justify-content-center">
                                        @foreach($organizationTree['level1'] as $user)
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                                            <div class="org-chart-node level1-node">
                                                <div class="avatar bg-secondary mx-auto mb-2">
                                                    @if($user->profile_picture)
                                                        <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                                                    @else
                                                        <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                    @endif
                                                </div>
                                                <div class="node-content">
                                                    <h6 class="mb-0 node-name">{{ $user->name }}</h6>
                                                    <p class="role-badge level1 mb-0">Level 1</p>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Legend -->
                    <div class="row mt-4 mt-md-5">
                        <div class="col-12">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Role Legend</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap">
                                        <div class="me-4 mb-2">
                                            <span class="badge bg-primary me-2">L5</span>
                                            <span>Level 5 (Highest Approval)</span>
                                        </div>
                                        <div class="me-4 mb-2">
                                            <span class="badge bg-info me-2">L4</span>
                                            <span>Level 4</span>
                                        </div>
                                        <div class="me-4 mb-2">
                                            <span class="badge bg-warning me-2">L3</span>
                                            <span>Level 3</span>
                                        </div>
                                        <div class="me-4 mb-2">
                                            <span class="badge bg-success me-2">L2</span>
                                            <span>Level 2</span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="badge bg-secondary me-2">L1</span>
                                            <span>Level 1 (Entry Level)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        /* Organization Chart Styles */
        .org-chart {
            position: relative;
            padding: 20px 0;
            overflow-x: hidden; /* Prevent horizontal scrolling on mobile */
        }
        
        .org-chart-node {
            padding: 15px;
            border-radius: 8px;
            background: white;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            max-width: 180px;
            margin: 0 auto;
        }
        
        .org-chart-node:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .level5-node {
            border-color: #0d6efd;
            border-width: 2px;
        }

        .level4-node {
            border-color: #0dcaf0;
            border-width: 1px;
        }

        .level3-node {
            border-color: #ffc107;
            border-width: 1px;
        }

        .level2-node {
            border-color: #20c997;
            border-width: 1px;
        }

        .level1-node {
            border-color: #6c757d;
        }

        .empty-node {
            background-color: #f8f9fa;
            border-style: dashed;
        }

        /* Legacy nodes */
        .head-node {
            border-color: #0d6efd;
            border-width: 2px;
        }

        .leader-node {
            border-color: #0dcaf0;
            border-width: 1px;
        }

        .staff-node {
            border-color: #20c997;
        }
        
        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
        }
        
        .role-badge {
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 5px;
        }

        .level5 {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        .level4 {
            background-color: rgba(13, 202, 240, 0.1);
            color: #0dcaf0;
        }

        .level3 {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .level2 {
            background-color: rgba(32, 201, 151, 0.1);
            color: #20c997;
        }

        .level1 {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        /* Legacy badges */
        .department-head {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        .leader {
            background-color: rgba(13, 202, 240, 0.1);
            color: #0dcaf0;
        }

        .staff {
            background-color: rgba(32, 201, 151, 0.1);
            color: #20c997;
        }
        
        /* Connector Lines */
        .vertical-line {
            height: 30px;
            width: 2px;
            background: #dee2e6;
            margin: 0 auto 20px;
        }
        
        .vertical-line.small {
            height: 20px;
            margin-bottom: 10px;
        }
        
        .horizontal-line {
            height: 2px;
            background: #dee2e6;
            width: 80%;
            margin: 0 auto 20px;
        }
        
        /* Mobile Optimization */
        @media (max-width: 767.98px) {
            .org-chart-node {
                padding: 10px;
                max-width: 140px;
            }
            
            .avatar {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
            
            .node-name {
                font-size: 14px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                max-width: 120px;
            }
            
            .role-badge {
                font-size: 10px;
                padding: 2px 6px;
            }
            
            .horizontal-line {
                width: 95%;
            }
            
            .vertical-line {
                height: 20px;
                margin-bottom: 15px;
            }
            
            .vertical-line.small {
                height: 15px;
                margin-bottom: 8px;
            }
        }
        
        /* Extra small devices */
        @media (max-width: 575.98px) {
            .org-chart-node {
                padding: 8px;
                max-width: 120px;
            }
            
            .avatar {
                width: 36px;
                height: 36px;
                font-size: 14px;
                margin-bottom: 8px !important;
            }
            
            .node-name {
                font-size: 12px;
                max-width: 100px;
            }
            
            .horizontal-line {
                width: 100%;
            }
        }
    </style>
</x-app-layout>
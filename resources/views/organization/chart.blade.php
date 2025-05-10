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
                            <div class="org-chart">
                                <!-- Department Head -->
                                @if($organizationTree['head'])
                                <div class="org-chart-level-1 mb-4 mb-md-5">
                                    <div class="org-chart-node head-node mx-auto">
                                        <div class="avatar bg-primary mx-auto mb-2">
                                            @if($organizationTree['head']->profile_picture)
                                                <img src="{{ $organizationTree['head']->profile_picture_url }}" alt="{{ $organizationTree['head']->name }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                                            @else
                                                <span>{{ strtoupper(substr($organizationTree['head']->name, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                        <div class="node-content">
                                            <h5 class="mb-0 node-name">{{ $organizationTree['head']->name }}</h5>
                                            <p class="role-badge department-head mb-0">Department Head</p>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="org-chart-level-1 mb-4 mb-md-5">
                                    <div class="org-chart-node empty-node mx-auto">
                                        <div class="avatar bg-secondary mx-auto mb-2">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <div class="node-content">
                                            <h5 class="mb-0 node-name">Not Assigned</h5>
                                            <p class="role-badge department-head mb-0">Department Head</p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Vertical Line -->
                                @if($organizationTree['leaders']->count() > 0)
                                <div class="vertical-line"></div>
                                @endif
                                
                                <!-- Leaders Level -->
                                @if($organizationTree['leaders']->count() > 0)
                                <div class="org-chart-level-2 mb-4 mb-md-5">
                                    <div class="horizontal-line"></div>
                                    <div class="row justify-content-center">
                                        @foreach($organizationTree['leaders'] as $leader)
                                        <div class="col-6 col-sm-4 col-md-4 col-lg-3 mb-3">
                                            <div class="org-chart-node leader-node">
                                                <div class="avatar bg-info mx-auto mb-2">
                                                    @if($leader->profile_picture)
                                                        <img src="{{ $leader->profile_picture_url }}" alt="{{ $leader->name }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                                                    @else
                                                        <span>{{ strtoupper(substr($leader->name, 0, 1)) }}</span>
                                                    @endif
                                                </div>
                                                <div class="node-content">
                                                    <h5 class="mb-0 node-name">{{ $leader->name }}</h5>
                                                    <p class="role-badge leader mb-0">Team Leader</p>
                                                </div>
                                            </div>
                                            <!-- Vertical Lines to Staff -->
                                            @if($organizationTree['staff']->count() > 0)
                                            <div class="vertical-line small"></div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Staff Level -->
                                @if($organizationTree['staff']->count() > 0)
                                <div class="org-chart-level-3">
                                    @if($organizationTree['leaders']->count() == 0)
                                    <div class="vertical-line"></div>
                                    @endif
                                    <div class="horizontal-line"></div>
                                    <div class="row justify-content-center">
                                        @foreach($organizationTree['staff'] as $staffMember)
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                                            <div class="org-chart-node staff-node">
                                                <div class="avatar bg-success mx-auto mb-2">
                                                    @if($staffMember->profile_picture)
                                                        <img src="{{ $staffMember->profile_picture_url }}" alt="{{ $staffMember->name }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                                                    @else
                                                        <span>{{ strtoupper(substr($staffMember->name, 0, 1)) }}</span>
                                                    @endif
                                                </div>
                                                <div class="node-content">
                                                    <h6 class="mb-0 node-name">{{ $staffMember->name }}</h6>
                                                    <p class="role-badge staff mb-0">Staff</p>
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
                                            <span class="badge bg-primary me-2">DH</span>
                                            <span>Department Head</span>
                                        </div>
                                        <div class="me-4 mb-2">
                                            <span class="badge bg-info me-2">TL</span>
                                            <span>Team Leader</span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="badge bg-success me-2">S</span>
                                            <span>Staff</span>
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
        
        .empty-node {
            background-color: #f8f9fa;
            border-style: dashed;
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
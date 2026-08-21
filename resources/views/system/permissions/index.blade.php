@extends('layouts.app')

@section('title', 'Permissions Management')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header mb-4">
            <h4 class="fw-bold mb-1">
                <i class="feather-key text-primary me-2"></i>
                Permissions Management
            </h4>
            <p class="text-muted mb-0">
                System permission catalog grouped by functional modules
            </p>
        </div>

        {{-- ================= SECURITY INFO ================= --}}
        <div class="alert alert-info d-flex align-items-start mb-4">
            <i class="feather-shield me-2 mt-1"></i>
            <div>
                <strong>Security & Access Control</strong>
                <p class="mb-0 small">
                    This page displays system permissions using human-readable descriptions.
                    Internal permission keys are hidden to prevent guessing or misuse.
                </p>
            </div>
        </div>

        @php
            $groupedPermissions = $permissions->groupBy(fn($p) => $p->module ?? 'general');

            $moduleDescriptions = [
                'users' => 'Manage system users, accounts, and access control.',
                'roles' => 'Create, update, and manage user roles.',
                'permissions' => 'Control which permissions are available and assignable.',
                'dashboard' => 'Access dashboards, insights, and overview pages.',
                'finance' => 'Manage financial governance, budgets, and execution.',
                'budget' => 'Plan and structure budget hierarchies.',
                'reports' => 'View, export, and analyze reports.',
                'hr' => 'Human resource management and recruitment.',
                'settings' => 'System-wide configuration and preferences.',
                'general' => 'General system permissions.',
            ];
        @endphp

        {{-- ================= PERMISSIONS CATALOG ================= --}}
        @foreach ($groupedPermissions as $module => $modulePermissions)
            <div class="card shadow-sm border-0 mb-4">

                {{-- Module Header --}}
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-primary">
                            <i class="feather-layers me-1"></i>
                            {{ strtoupper($module) }} MODULE
                        </h6>
                        <small class="text-muted">
                            {{ $moduleDescriptions[$module] ?? 'Controls access to this module features.' }}
                        </small>
                    </div>

                    <span class="badge bg-secondary">
                        {{ $modulePermissions->count() }} permissions
                    </span>
                </div>

                {{-- Module Body --}}
                <div class="card-body">
                    <div class="row g-2">

                        @foreach ($modulePermissions as $permission)
                            <div class="col-xl-4 col-lg-6 col-md-6">
                                <div class="border rounded px-3 py-2 d-flex align-items-start gap-2 bg-light-subtle">

                                    <i class="feather-check-circle text-success mt-1"></i>

                                    <span class="small text-dark" title="Internal key hidden for security">
                                        {{ $permission->description ?? 'No description provided' }}
                                    </span>

                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>

            </div>
        @endforeach

    </div>
@endsection

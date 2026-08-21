@extends('layouts.app')

@section('title', 'HR Employees')

@section('content')
    <div class="nxl-container">
        <div class="page-header mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="feather-users text-primary me-2"></i>Employee Directory
                </h4>
                <p class="text-muted mb-0">Employees created through the recruitment and hiring workflow.</p>
            </div>
            <div class="d-flex gap-2">
                @can('hrm.positions.view')
                    <a href="{{ route('hr.positions.index') }}" class="btn btn-light">Positions</a>
                @endcan
                @can('hr.analytics.view')
                    <a href="{{ route('hr.analytics') }}" class="btn btn-outline-primary">
                        <i class="feather-bar-chart-2 me-1"></i>Analytics
                    </a>
                @endcan
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Employees</div>
                        <h3 class="fw-bold mb-0">{{ $employees->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Active</div>
                        <h3 class="fw-bold text-success mb-0">{{ $employees->where('status', 'active')->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Governance Nodes</div>
                        <h3 class="fw-bold mb-0">{{ $employees->pluck('governance_node_id')->filter()->unique()->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <x-data-table id="employeesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Employee Code</th>
                            <th>Position</th>
                            <th>Governance Node</th>
                            <th>Contract</th>
                            <th>Start Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $employee->applicant?->full_name ?? 'Employee record' }}</div>
                                    @if ($employee->applicant?->email)
                                        <div class="small text-muted">{{ $employee->applicant->email }}</div>
                                    @endif
                                </td>
                                <td>{{ $employee->employee_code ?: 'Not assigned' }}</td>
                                <td>
                                    <div>{{ $employee->position?->title ?? 'Not assigned' }}</div>
                                    @if ($employee->position?->grade_level)
                                        <div class="small text-muted">Grade {{ $employee->position->grade_level }}</div>
                                    @endif
                                </td>
                                <td>{{ $employee->governanceNode?->name ?? 'Not assigned' }}</td>
                                <td>{{ \Illuminate\Support\Str::headline($employee->contract_type ?? 'Not specified') }}</td>
                                <td data-order="{{ $employee->employment_start_date?->timestamp }}">
                                    {{ $employee->employment_start_date?->format('d M Y') ?? 'Not recorded' }}
                                </td>
                                <td>
                                    @php
                                        $statusClass = match ($employee->status) {
                                            'active' => 'bg-success',
                                            'inactive' => 'bg-secondary',
                                            'terminated' => 'bg-danger',
                                            default => 'bg-info',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ \Illuminate\Support\Str::headline($employee->status ?? 'unknown') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No employee records are available in your governance scope.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-data-table>
            </div>
        </div>
    </div>
@endsection

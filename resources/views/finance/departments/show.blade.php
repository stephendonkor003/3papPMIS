@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">{{ $department->name }}</h4>
                <p class="text-muted mb-0">
                    Department overview, programs, and funding governance
                </p>
            </div>

            <div class="d-flex gap-2">
                {{-- <a href="{{ route('finance.departments.edit', $department) }}" class="btn btn-warning">
                    <i class="feather-edit me-1"></i> Edit
                </a> --}}

                <a href="{{ route('finance.departments.index') }}" class="btn btn-light">
                    <i class="feather-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        {{-- ================= SUMMARY CARDS ================= --}}
        <div class="row mt-3">

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="text-muted small">Department Code</div>
                        <h5 class="fw-bold mb-0">{{ $department->code }}</h5>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="text-muted small">Status</div>
                        <span class="badge bg-{{ $department->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($department->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="text-muted small">Programs</div>
                        <h5 class="fw-bold mb-0">
                            {{ $department->programs->count() }}
                        </h5>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="text-muted small">Funding Records</div>
                        <h5 class="fw-bold mb-0">
                            {{ $department->programFundings->count() }}
                        </h5>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= DESCRIPTION ================= --}}
        @if ($department->description)
            <div class="card shadow-sm border-0 mt-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">
                        <i class="feather-info me-1"></i> Description
                    </h6>
                    <p class="mb-0 text-muted">
                        {{ $department->description }}
                    </p>
                </div>
            </div>
        @endif

        {{-- ================= PROGRAMS SECTION ================= --}}
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white fw-bold">
                <i class="feather-grid me-1"></i> Programs Under This Department
            </div>

            <div class="card-body p-0">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Program Name</th>
                            <th width="160">Projects</th>
                            <th width="160">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($department->programs as $program)
                            <tr>
                                <td>
                                    {{ $program->name }}
                                    <div class="text-muted small">
                                        {{ Str::limit($program->description, 80) }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">
                                        {{ $program->projects->count() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    No programs assigned to this department.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================= FUNDING SECTION ================= --}}
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white fw-bold">
                <i class="feather-credit-card me-1"></i> Program Funding (Governance)
            </div>

            <div class="card-body p-0">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Program</th>
                            <th>Funder</th>
                            <th>Amount</th>
                            <th>Period</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($department->programFundings as $funding)
                            <tr>
                                <td>{{ $funding->program->name }}</td>
                                <td>{{ $funding->funder->name }}</td>
                                <td>
                                    {{ number_format($funding->approved_amount, 2) }}
                                    {{ $funding->currency }}
                                </td>
                                <td>
                                    {{ $funding->start_year }} – {{ $funding->end_year }}
                                </td>
                                <td>
                                    <span
                                        class="badge bg-
                                @if ($funding->status === 'approved') success
                                @elseif($funding->status === 'submitted') warning
                                @elseif($funding->status === 'rejected') danger
                                @else secondary @endif">
                                        {{ ucfirst($funding->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No funding records found for this department.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">

            {{-- PAGE HEADER --}}
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Projects Management</h4>
                    <p class="text-muted m-0">Manage all projects and their activities under each program.</p>
                </div>

                {{-- CREATE PROJECT --}}
                @can('project.create')
                    <a href="{{ route('budget.projects.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> New Project
                    </a>
                @endcan
            </div>

            {{-- ALERTS --}}
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- PROGRAM SUMMARY CARDS --}}
            @if (!empty($programSummaries) && $programSummaries->count())
                <div class="row g-3 mb-4">
                    @foreach ($programSummaries as $summary)
                        <div class="col-md-4">
                            @php
                                $total = (float) $summary->total_budget;
                                $used = (float) $summary->used_budget;
                                $remaining = (float) $summary->remaining_budget;
                                $percent = $total > 0 ? min(($used / $total) * 100, 100) : 0;
                            @endphp
                            <div class="card shadow-sm border-0 h-100 program-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="text-muted small">Program</div>
                                            <div class="fw-semibold">{{ $summary->program_id }} — {{ $summary->name }}</div>
                                        </div>
                                        <span class="badge bg-light text-dark">{{ $summary->currency ?? 'N/A' }}</span>
                                    </div>
                                    <div class="mt-3">
                                        <div class="text-muted small">Total Budget</div>
                                        <div class="fw-bold">{{ number_format($summary->total_budget, 2) }}</div>
                                    </div>
                                    <div class="mt-2">
                                        <div class="text-muted small">Used / Remaining</div>
                                        <div>
                                            {{ number_format($summary->used_budget, 2) }}
                                            <span class="text-muted">/</span>
                                            <span class="text-success">{{ number_format($summary->remaining_budget, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between small text-muted mb-1">
                                            <span>Utilized</span>
                                            <span>{{ number_format($percent, 1) }}%</span>
                                        </div>
                                        <div class="progress program-progress" role="progressbar"
                                            aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar" style="width: {{ $percent }}%"></div>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            {{ number_format(max($total - $used, 0), 2) }} remaining
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- PROJECT LIST TABLE --}}
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <x-data-table
                        id="projectsTable"
                    >
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Project ID</th>
                                <th>Name</th>
                                <th>Program</th>
                                <th>Total Budget</th>
                                <th>Years</th>
                                <th>Activities</th>
                                <th width="200" class="text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($projects as $p)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td class="fw-bold text-primary">
                                        {{ $p->project_id }}
                                    </td>

                                    <td>{{ $p->name }}</td>

                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $p->program->program_id }}
                                        </span>
                                        <br>
                                        <small>{{ $p->program->name }}</small>
                                    </td>

                                    <td>
                                        {{ number_format($p->total_budget, 2) }}
                                        <small class="text-muted">{{ $p->currency }}</small>
                                    </td>

                                    <td>
                                        {{ $p->start_year }} → {{ $p->end_year }}
                                        <br>
                                        <small class="text-muted">{{ $p->total_years }} years</small>
                                    </td>

                                    {{-- ACTIVITIES COUNT --}}
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $p->activities->count() }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        @can('activities.create')
                                            {{-- ADD ACTIVITY --}}
                                            <a href="{{ route('budget.activities.create', $p->id) }}"
                                                class="btn btn-sm btn-success" title="Add New Activity">
                                                <i class="feather-plus-circle"></i>
                                            </a>
                                        @endcan

                                        {{-- VIEW PROJECT --}}
                                        <a href="{{ route('budget.projects.show', $p->id) }}"
                                            class="btn btn-sm btn-info" title="View Details">
                                            <i class="feather-eye"></i>
                                        </a>

                                        {{-- EDIT PROJECT --}}
                                        @can('project.edit')
                                            <a href="{{ route('budget.projects.edit', $p->id) }}"
                                                class="btn btn-sm btn-warning" title="Edit Project">
                                                <i class="feather-edit"></i>
                                            </a>
                                        @endcan

                                        {{-- DELETE --}}
                                        @can('project.delete')
                                            <form action="{{ route('budget.projects.destroy', $p->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this project?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-danger" title="Delete Project">
                                                    <i class="feather-trash-2"></i>
                                                </button>
                                            </form>
                                        @endcan

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-data-table>

                </div>
            </div>

        </div>
    </main>

    <style>
        .program-card {
            background: linear-gradient(135deg, #ffffff 0%, #f7f9fb 100%);
            border-radius: 14px;
        }

        .program-card .badge {
            border-radius: 999px;
        }

        .program-progress {
            height: 10px;
            border-radius: 999px;
            background-color: #eef2f6;
            overflow: hidden;
        }

        .program-progress .progress-bar {
            background: linear-gradient(90deg, #198754, #20c997);
        }
    </style>
@endsection

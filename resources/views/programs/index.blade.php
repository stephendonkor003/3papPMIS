@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-dark mb-1">Programs</h4>
                <p class="text-muted mb-0 small">
                    Manage budget programs and their associated projects.
                </p>
            </div>

            @can('program.create')
                <a href="{{ route('budget.programs.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> New Program
                </a>
            @endcan
        </div>

        {{-- ================= PROGRAMS TABLE ================= --}}
        <div class="card mt-3 shadow-sm">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th>Name</th>
                                <th>Sector</th>
                                <th>Currency</th>
                                <th>Duration</th>
                                <th width="220">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($programs as $program)
                                <tr>
                                    <td class="fw-semibold">{{ $program->name }}</td>
                                    <td>{{ $program->sector->name ?? '-' }}</td>
                                    <td>{{ $program->currency }}</td>
                                    <td>
                                        {{ $program->start_year }} – {{ $program->end_year }}
                                        <span class="badge bg-light text-dark ms-1">
                                            {{ $program->total_years }} yrs
                                        </span>
                                    </td>

                                    {{-- ================= ACTIONS ================= --}}
                                    <td class="text-center">

                                        {{-- VIEW --}}
                                        @can('program.view')
                                            <a href="{{ route('programs.show', $program->id) }}"
                                                class="btn btn-sm btn-outline-info me-1" title="View Program">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endcan

                                        {{-- EDIT --}}
                                        @can('program.edit')
                                            <a href="{{ route('programs.edit', $program->id) }}"
                                                class="btn btn-sm btn-outline-warning me-1" title="Edit Program">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        @endcan

                                        {{-- REPORT --}}
                                        @can('program.report')
                                            <a href="{{ url('/budget/reports/program/' . $program->id) }}"
                                                class="btn btn-sm btn-outline-secondary me-1" title="Program Report">
                                                <i class="bi bi-bar-chart"></i>
                                            </a>
                                        @endcan

                                        {{-- DELETE --}}
                                        @can('program.delete')
                                            <form action="{{ route('programs.destroy', $program->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Delete this program and all related data?');">
                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-sm btn-outline-danger" title="Delete Program">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No programs found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ================= PAGINATION ================= --}}
                <div class="mt-3">
                    {{ $programs->links() }}
                </div>

            </div>
        </div>

    </div>
@endsection

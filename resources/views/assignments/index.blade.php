@extends('layouts.app')
@section('title', 'Assignments')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <!-- Page Header -->
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Assignments</h4>
                    <p class="text-muted mb-0">Manage applicant-to-evaluator assignments.</p>
                </div>

                {{-- Only admin can create new assignments --}}
                @if (Auth::user()->user_type === 'admin')
                    <a href="{{ route('assignments.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> New Assignment
                    </a>
                @endif
            </div>

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <!-- Assignments Table -->
            <div class="card shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Applicant</th>
                                <th>Evaluator</th>
                                <th>Role</th>
                                <th>Assigned On</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $index => $assignment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $assignment->applicant->think_tank_name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $assignment->applicant->country ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $assignment->evaluator->name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $assignment->evaluator->email ?? '-' }}</small>
                                    </td>
                                    <td>{{ $assignment->role ?? '-' }}</td>
                                    <td>{{ $assignment->created_at->format('d M, Y') }}</td>
                                    <td class="text-center">
                                        <!-- Everyone can view -->
                                        <a href="{{ route('assignments.show', $assignment->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>

                                        {{-- Only admin can edit/delete --}}
                                        @if (Auth::user()->user_type === 'admin')
                                            <a href="{{ route('assignments.edit', $assignment->id) }}"
                                                class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <form action="{{ route('assignments.destroy', $assignment->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this assignment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No assignments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $assignments->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

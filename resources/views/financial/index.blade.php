@extends('layouts.app')
@section('title', 'My Financial Evaluations')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">

            {{-- Header --}}
            <div class="page-header d-flex justify-content-between align-items-center mb-3">
                <h5>My Financial Evaluations</h5>
            </div>

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Tabs --}}
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pending">Pending / In
                        Progress</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#completed">Completed</button>
                </li>
            </ul>

            <div class="tab-content">

                {{-- Pending --}}
                <div class="tab-pane fade show active" id="pending">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Country</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $drafts = $evaluations->filter(
                                            fn($e) => $e->status === 'draft' || $e->status === null,
                                        );
                                    @endphp

                                    {{-- Draft Evaluations --}}
                                    @forelse($drafts as $item)
                                        <tr>
                                            <td>{{ $item->applicant->think_tank_name }}</td>
                                            <td>{{ $item->applicant->country }}</td>
                                            <td><span class="badge bg-warning text-dark">Draft</span></td>
                                            <td class="text-center">
                                                <a href="{{ route('financial.edit', $item->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="feather-edit"></i> Edit Draft
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        {{-- No drafts --}}
                                    @endforelse

                                    {{-- Assigned but Not Started --}}
                                    @forelse($pendingApplicants as $applicant)
                                        <tr>
                                            <td>{{ $applicant->think_tank_name }}</td>
                                            <td>{{ $applicant->country }}</td>
                                            <td><span class="badge bg-secondary">Not Started</span></td>
                                            <td class="text-center">
                                                <a href="{{ route('financial.create', $applicant->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="feather-edit"></i> Evaluate
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        @if ($drafts->isEmpty())
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No pending evaluations.
                                                </td>
                                            </tr>
                                        @endif
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Completed --}}
                <div class="tab-pane fade" id="completed">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Country</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $completed = $evaluations->filter(fn($e) => $e->status === 'submitted');
                                    @endphp

                                    @forelse($completed as $item)
                                        <tr>
                                            <td>{{ $item->applicant->think_tank_name }}</td>
                                            <td>{{ $item->applicant->country }}</td>
                                            <td><span class="badge bg-success">Submitted</span></td>
                                            <td class="text-center">
                                                @if (Auth::user()->user_type === 'admin')
                                                    <a href="{{ route('financial.show', $item->id) }}"
                                                        class="btn btn-sm btn-secondary">
                                                        <i class="feather-eye"></i> View
                                                    </a>
                                                @else
                                                    <span class="text-muted">Locked</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No completed evaluations.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
@endsection

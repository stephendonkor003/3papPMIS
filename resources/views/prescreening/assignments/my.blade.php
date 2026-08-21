@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        <div class="page-header mb-4">
            <h4 class="fw-bold mb-1">
                <i class="feather-clipboard text-primary me-2"></i>
                My Prescreening Assignments
            </h4>
            <p class="text-muted mb-0">
                Procurements and submissions assigned to you for prescreening.
            </p>
        </div>

        {{-- ================= PROCUREMENT ASSIGNMENTS ================= --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Procurement Assignments (All Submissions)</h6>
                <span class="badge bg-primary">{{ $procurements->count() }} Procurements</span>
            </div>
            <div class="card-body">
                <x-data-table id="procurementsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Reference</th>
                            <th>Title</th>
                            <th class="text-center">Submissions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($procurements as $procurement)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $procurement->reference_no ?? '—' }}</td>
                                <td>{{ $procurement->title }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info px-3 py-1">{{ $procurement->submissions_count }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-data-table>
            </div>
        </div>

        {{-- ================= SUBMISSIONS FROM ASSIGNED PROCUREMENTS ================= --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Submissions From Assigned Procurements</h6>
                <span class="badge bg-primary">{{ $procurementSubmissions->count() }} Submissions</span>
            </div>
            <div class="card-body">
                <x-data-table id="procurementSubmissionsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Submission Code</th>
                            <th>Procurement</th>
                            <th class="text-center">Status</th>
                            <th width="140" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($procurementSubmissions as $submission)
                            @php
                                $statusColors = [
                                    'submitted' => 'secondary',
                                    'prescreen_passed' => 'success',
                                    'prescreen_failed' => 'danger',
                                    'under_review' => 'warning',
                                ];
                            @endphp
                            <tr>
                                <td class="ps-4 fw-semibold text-primary">{{ $submission->procurement_submission_code }}</td>
                                <td>{{ $submission->procurement->title ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $statusColors[$submission->status] ?? 'secondary' }} px-3 py-1">
                                        {{ ucfirst(str_replace('_', ' ', $submission->status ?? 'submitted')) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($submission->prescreeningResult)
                                        <span class="badge bg-success px-3 py-1">
                                            <i class="feather-check me-1"></i> Done
                                        </span>
                                    @else
                                        <a href="{{ route('prescreening.submissions.show', $submission) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="feather-play me-1"></i> Start
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-data-table>
            </div>
        </div>

        {{-- ================= SPECIFIC SUBMISSION ASSIGNMENTS ================= --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Specific Submission Assignments</h6>
                <span class="badge bg-primary">{{ $submissions->count() }} Submissions</span>
            </div>
            <div class="card-body">
                <x-data-table id="specificSubmissionsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Submission Code</th>
                            <th>Procurement</th>
                            <th class="text-center">Status</th>
                            <th width="140" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($submissions as $submission)
                            @php
                                $statusColors = [
                                    'submitted' => 'secondary',
                                    'prescreen_passed' => 'success',
                                    'prescreen_failed' => 'danger',
                                    'under_review' => 'warning',
                                ];
                            @endphp
                            <tr>
                                <td class="ps-4 fw-semibold text-primary">{{ $submission->procurement_submission_code }}</td>
                                <td>{{ $submission->procurement->title ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $statusColors[$submission->status] ?? 'secondary' }} px-3 py-1">
                                        {{ ucfirst(str_replace('_', ' ', $submission->status ?? 'submitted')) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($submission->prescreeningResult)
                                        <span class="badge bg-success px-3 py-1">
                                            <i class="feather-check me-1"></i> Done
                                        </span>
                                    @else
                                        <a href="{{ route('prescreening.submissions.show', $submission) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="feather-play me-1"></i> Start
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-data-table>
            </div>
        </div>

    </div>
@endsection

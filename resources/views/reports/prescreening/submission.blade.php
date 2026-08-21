@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Prescreening Submission Report</h4>
                <p class="text-muted mb-0">
                    Submission code: {{ $submission->procurement_submission_code }}
                </p>
            </div>
            <a href="{{ route('reports.prescreening.submission.pdf', $submission) }}" class="btn btn-success btn-sm">
                Download PDF
            </a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-semibold">Summary</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Procurement:</strong>
                        {{ $submission->procurement->title ?? '—' }}
                        ({{ $submission->procurement->reference_no ?? '—' }})
                    </div>
                    <div class="col-md-6">
                        <strong>Applicant:</strong>
                        {{ $submission->submitter->name ?? '—' }} ({{ $submission->submitter->email ?? '—' }})
                    </div>
                    <div class="col-md-6">
                        <strong>Template:</strong>
                        {{ $template->name ?? '—' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Evaluator:</strong>
                        {{ $submission->prescreeningResult?->evaluator?->name ?? '—' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Final Status:</strong>
                        {{ ucfirst(str_replace('_', ' ', $submission->status ?? 'pending')) }}
                    </div>
                    <div class="col-md-6">
                        <strong>Evaluated At:</strong>
                        {{ $submission->prescreeningResult?->evaluated_at?->format('Y-m-d H:i') ?? '—' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Submitted At:</strong>
                        {{ $submission->submitted_at?->format('Y-m-d H:i') ?? '—' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-semibold">Score Summary</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <strong>Total Criteria:</strong>
                        {{ $submission->prescreeningResult?->total_criteria ?? '—' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Passed:</strong>
                        {{ $submission->prescreeningResult?->passed_criteria ?? '—' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Failed:</strong>
                        {{ $submission->prescreeningResult?->failed_criteria ?? '—' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-semibold">Criteria Evaluation</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Criterion</th>
                            <th>Pass/Fail</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($criteria as $criterion)
                            @php
                                $evaluation = $evaluations[$criterion->id] ?? null;
                            @endphp
                            <tr>
                                <td>{{ $criterion->name }}</td>
                                <td>
                                    {{ $evaluation ? ($evaluation->is_passed ? 'Passed' : 'Failed') : '—' }}
                                </td>
                                <td>{{ $evaluation->remarks ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">
                                    No criteria available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-semibold">Applicant Submission Values</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Field</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submission->values as $value)
                            <tr>
                                <td>{{ $value->field_key }}</td>
                                <td>{{ $value->value }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-3">
                                    No submission values found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

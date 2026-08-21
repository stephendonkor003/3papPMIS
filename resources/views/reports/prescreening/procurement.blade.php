@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Prescreening Procurement Report</h4>
                <p class="text-muted mb-0">
                    {{ $procurement->title }} ({{ $procurement->reference_no ?? '—' }})
                </p>
            </div>
            <a href="{{ route('reports.prescreening.procurement.pdf', $procurement) }}" class="btn btn-success btn-sm">
                Download PDF
            </a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-semibold">Summary</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><strong>Total:</strong> {{ $summary['total'] }}</div>
                    <div class="col-md-3"><strong>Passed:</strong> {{ $summary['passed'] }}</div>
                    <div class="col-md-3"><strong>Failed:</strong> {{ $summary['failed'] }}</div>
                    <div class="col-md-3"><strong>Pending:</strong> {{ $summary['pending'] }}</div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-semibold">Evaluator Breakdown</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Evaluator</th>
                            <th>Total</th>
                            <th>Passed</th>
                            <th>Failed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($evaluatorBreakdown as $name => $stats)
                            <tr>
                                <td>{{ $name }}</td>
                                <td>{{ $stats['total'] }}</td>
                                <td>{{ $stats['passed'] }}</td>
                                <td>{{ $stats['failed'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No evaluations yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-semibold">Criteria Pass Rates</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Criterion</th>
                            <th>Total</th>
                            <th>Passed</th>
                            <th>Failed</th>
                            <th>Pass Rate (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($criteriaRates as $criterion)
                            <tr>
                                <td>{{ $criterion['name'] }}</td>
                                <td>{{ $criterion['total'] }}</td>
                                <td>{{ $criterion['passed'] }}</td>
                                <td>{{ $criterion['failed'] }}</td>
                                <td>{{ $criterion['rate'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    No criteria evaluations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-semibold">Submissions</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Submission Code</th>
                            <th>Applicant</th>
                            <th>Status</th>
                            <th>Evaluator</th>
                            <th>Evaluated At</th>
                            <th width="120" class="text-center">Report</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submissions as $submission)
                            <tr>
                                <td>{{ $submission->procurement_submission_code }}</td>
                                <td>{{ $submission->submitter->name ?? '—' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $submission->status ?? 'pending')) }}</td>
                                <td>{{ $submission->prescreeningResult?->evaluator?->name ?? '—' }}</td>
                                <td>{{ $submission->prescreeningResult?->evaluated_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('reports.prescreening.submission', $submission) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    No submissions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

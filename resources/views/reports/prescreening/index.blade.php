@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        <div class="page-header mb-4">
            <h4 class="fw-bold mb-1">Prescreening Reports</h4>
            <p class="text-muted mb-0">Detailed reports for submissions and procurements.</p>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Consolidated Report</h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('reports.prescreening.consolidated') }}" class="btn btn-sm btn-outline-primary">
                        View
                    </a>
                    <a href="{{ route('reports.prescreening.consolidated.pdf') }}" class="btn btn-sm btn-success">
                        Download PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                View a consolidated prescreening summary across all procurements.
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-semibold">Procurement Report</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="" onsubmit="return false;" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Select Procurement</label>
                        <select id="procurementSelect" class="form-control">
                            <option value="">-- Choose procurement --</option>
                            @foreach ($procurements as $procurement)
                                <option value="{{ $procurement->slug }}">{{ $procurement->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a id="procurementViewBtn" href="#" class="btn btn-outline-primary disabled">View Report</a>
                        <a id="procurementPdfBtn" href="#" class="btn btn-success disabled">Download PDF</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-semibold">Individual Submission Report</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="" onsubmit="return false;" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Select Submission</label>
                        <select id="submissionSelect" class="form-control">
                            <option value="">-- Choose submission --</option>
                            @foreach ($submissions as $submission)
                                <option value="{{ $submission->id }}">
                                    {{ $submission->procurement_submission_code }} — {{ $submission->procurement->title ?? '—' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a id="submissionViewBtn" href="#" class="btn btn-outline-primary disabled">View Report</a>
                        <a id="submissionPdfBtn" href="#" class="btn btn-success disabled">Download PDF</a>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const procSelect = document.getElementById('procurementSelect');
            const procView = document.getElementById('procurementViewBtn');
            const procPdf = document.getElementById('procurementPdfBtn');
            const subSelect = document.getElementById('submissionSelect');
            const subView = document.getElementById('submissionViewBtn');
            const subPdf = document.getElementById('submissionPdfBtn');

            procSelect.addEventListener('change', function() {
                const value = this.value;
                if (!value) {
                    procView.classList.add('disabled');
                    procPdf.classList.add('disabled');
                    procView.href = '#';
                    procPdf.href = '#';
                    return;
                }
                procView.classList.remove('disabled');
                procPdf.classList.remove('disabled');
                procView.href = `{{ url('reports/prescreening/procurement') }}/${value}`;
                procPdf.href = `{{ url('reports/prescreening/procurement') }}/${value}/pdf`;
            });

            subSelect.addEventListener('change', function() {
                const value = this.value;
                if (!value) {
                    subView.classList.add('disabled');
                    subPdf.classList.add('disabled');
                    subView.href = '#';
                    subPdf.href = '#';
                    return;
                }
                subView.classList.remove('disabled');
                subPdf.classList.remove('disabled');
                subView.href = `{{ url('reports/prescreening/submission') }}/${value}`;
                subPdf.href = `{{ url('reports/prescreening/submission') }}/${value}/pdf`;
            });
        });
    </script>
@endsection

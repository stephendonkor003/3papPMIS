@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        <div class="page-header d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold">Prescreening Criterion Details</h4>
                <p class="text-muted mb-0">
                    Template: <strong>{{ $template->name }}</strong>
                </p>
            </div>

            <a href="{{ route('prescreening.criteria.index', $template) }}" class="btn btn-outline-secondary btn-sm">
                <i class="feather-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                <div class="row g-4">

                    <div class="col-md-6">
                        <div class="text-muted small">Criterion Name</div>
                        <div class="fw-semibold">{{ $criterion->name }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Field Key</div>
                        <div><code>{{ $criterion->field_key }}</code></div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">Evaluation Type</div>
                        <div>
                            {{ ucfirst(str_replace('_', ' ', $criterion->evaluation_type)) }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">Mandatory</div>
                        <span class="badge {{ $criterion->is_mandatory ? 'bg-success' : 'bg-secondary' }}">
                            {{ $criterion->is_mandatory ? 'Yes' : 'No' }}
                        </span>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">Minimum Value</div>
                        <div>
                            {{ $criterion->min_value ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="text-muted small">Description</div>
                        <div class="border rounded p-3 bg-light">
                            {{ $criterion->description ?? 'No description provided.' }}
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold">Add Prescreening Criterion</h4>
                <p class="text-muted mb-0">
                    Template: <strong>{{ $template->name }}</strong>
                </p>
            </div>

            <a href="{{ route('prescreening.criteria.index', $template) }}" class="btn btn-outline-secondary btn-sm">
                <i class="feather-arrow-left me-1"></i> Back
            </a>
        </div>

        {{-- ================= FORM ================= --}}
        <div class="card shadow-sm">
            <div class="card-body">

                <form method="POST" action="{{ route('prescreening.criteria.store', $template) }}">
                    @csrf

                    <div class="row g-3">

                        {{-- NAME --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Criterion Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        {{-- SORT ORDER --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Sort Order
                            </label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>

                        {{-- FIELD KEY --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Form Field Key <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="field_key" class="form-control" placeholder="e.g. tax_clearance"
                                required>
                            <small class="text-muted">
                                Must match <code>dynamic_form_fields.field_key</code>
                            </small>
                        </div>

                        {{-- EVALUATION TYPE --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Evaluation Type <span class="text-danger">*</span>
                            </label>
                            <select name="evaluation_type" class="form-select" required>
                                <option value="">-- Select Type --</option>
                                <option value="yes_no">Yes / No</option>
                                <option value="exists">Exists (Document / Value)</option>
                                <option value="numeric">Numeric Threshold</option>
                            </select>
                        </div>

                        {{-- MIN VALUE --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Minimum Value (Numeric Only)
                            </label>
                            <input type="number" step="0.01" name="min_value" class="form-control"
                                placeholder="Optional">
                        </div>

                        {{-- MANDATORY --}}
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="is_mandatory" value="1" checked>
                                <label class="form-check-label">
                                    Mandatory Criterion
                                </label>
                            </div>
                        </div>

                        {{-- DESCRIPTION --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">
                                Description
                            </label>
                            <textarea name="description" rows="3" class="form-control" placeholder="Explain what this criterion checks"></textarea>
                        </div>

                    </div>

                    {{-- ACTIONS --}}
                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-success">
                            <i class="feather-save me-1"></i> Save Criterion
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        <div class="page-header d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold">Edit Prescreening Criterion</h4>
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

                <form method="POST" action="{{ route('prescreening.criteria.update', [$template, $criterion]) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Criterion Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $criterion->name }}"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control"
                                value="{{ $criterion->sort_order }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Form Field Key</label>
                            <input type="text" name="field_key" class="form-control" value="{{ $criterion->field_key }}"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Evaluation Type</label>
                            <select name="evaluation_type" class="form-select" required>
                                <option value="yes_no" @selected($criterion->evaluation_type === 'yes_no')>Yes / No</option>
                                <option value="exists" @selected($criterion->evaluation_type === 'exists')>Exists</option>
                                <option value="numeric" @selected($criterion->evaluation_type === 'numeric')>Numeric</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Minimum Value</label>
                            <input type="number" step="0.01" name="min_value" class="form-control"
                                value="{{ $criterion->min_value }}">
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="is_mandatory" value="1"
                                    @checked($criterion->is_mandatory)>
                                <label class="form-check-label">
                                    Mandatory Criterion
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" rows="3" class="form-control">{{ $criterion->description }}</textarea>
                        </div>

                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-success">
                            <i class="feather-save me-1"></i> Update Criterion
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
@endsection

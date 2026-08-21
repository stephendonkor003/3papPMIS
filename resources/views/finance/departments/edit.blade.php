@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">Edit Department</h4>
                <p class="text-muted mb-0">
                    Update department information and operational status
                </p>
            </div>

            <a href="{{ route('finance.departments.show', $department) }}" class="btn btn-light">
                <i class="feather-arrow-left me-1"></i> Back
            </a>
        </div>

        {{-- ================= VALIDATION ERRORS ================= --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following issues:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ================= FORM CARD ================= --}}
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">

                <form method="POST" action="{{ route('finance.departments.update', $department) }}">
                    @csrf
                    @method('PUT')

                    {{-- ================= BASIC INFORMATION ================= --}}
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="feather-info me-1"></i> Department Information
                    </h6>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Department Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="code" class="form-control"
                                value="{{ old('code', $department->code) }}" required>
                            <small class="text-muted">
                                Short unique identifier
                            </small>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">
                                Department Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $department->name) }}" required>
                            <small class="text-muted">
                                Full official department name
                            </small>
                        </div>
                    </div>

                    {{-- ================= DESCRIPTION ================= --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="4" class="form-control"
                            placeholder="Brief description of the department’s role and responsibilities">{{ old('description', $department->description) }}</textarea>
                    </div>

                    {{-- ================= STATUS ================= --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ old('status', $department->status) === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive"
                                {{ old('status', $department->status) === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                        <small class="text-muted">
                            Inactive departments cannot receive new funding
                        </small>
                    </div>

                    {{-- ================= ACTION BUTTONS ================= --}}
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('finance.departments.index') }}" class="btn btn-light">
                            Cancel
                        </a>

                        <button class="btn btn-primary">
                            <i class="feather-save me-1"></i> Update Department
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

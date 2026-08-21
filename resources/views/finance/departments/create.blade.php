@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">Create Department</h4>
                <p class="text-muted mb-0">
                    Define an institutional unit responsible for programs and funding
                </p>
            </div>

            <a href="{{ route('finance.departments.index') }}" class="btn btn-light">
                <i class="feather-arrow-left me-1"></i> Back to Departments
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

                <form method="POST" action="{{ route('finance.departments.store') }}">
                    @csrf

                    {{-- ================= BASIC INFORMATION ================= --}}
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="feather-info me-1"></i> Department Information
                    </h6>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Department Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. ICT, FIN, HR"
                                value="{{ old('code') }}" required>
                            <small class="text-muted">
                                Short unique identifier
                            </small>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">
                                Department Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control"
                                placeholder="e.g. Information & Communication Technology" value="{{ old('name') }}"
                                required>
                            <small class="text-muted">
                                Full official department name
                            </small>
                        </div>
                    </div>

                    {{-- ================= DESCRIPTION ================= --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="4" class="form-control"
                            placeholder="Brief description of the department’s role and responsibilities">{{ old('description') }}</textarea>
                    </div>

                    {{-- ================= STATUS ================= --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
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
                            <i class="feather-save me-1"></i> Save Department
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

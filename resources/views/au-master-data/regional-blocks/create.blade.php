@extends('layouts.app')

@section('title', 'Add Regional Block')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Add Regional Block</h4>
                    <p class="text-muted mb-0">Add a new Regional Economic Community (REC).</p>
                </div>
                <a href="{{ route('settings.au.regional-blocks.index') }}" class="btn btn-outline-secondary">
                    <i class="feather-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="{{ route('settings.au.regional-blocks.store') }}" method="POST">
                        @csrf

                        <div class="row g-4">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="e.g. Economic Community of West African States"
                                    required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Abbreviation</label>
                                <input type="text" name="abbreviation"
                                    class="form-control @error('abbreviation') is-invalid @enderror"
                                    value="{{ old('abbreviation') }}" placeholder="e.g. ECOWAS" maxlength="20">
                                @error('abbreviation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" rows="3"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Brief description of the regional block...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sort Order</label>
                                <input type="number" name="sort_order"
                                    class="form-control @error('sort_order') is-invalid @enderror"
                                    value="{{ old('sort_order', 0) }}" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status</label>
                                <div class="form-check mt-2">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                        id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('settings.au.regional-blocks.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="feather-check me-1"></i> Save Regional Block
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

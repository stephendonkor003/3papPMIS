@extends('layouts.app')

@section('title', 'Edit Aspiration')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Edit Aspiration</h4>
                    <p class="text-muted mb-0">Update Aspiration {{ $aspiration->number }}</p>
                </div>
                <a href="{{ route('settings.au.aspirations.index') }}" class="btn btn-outline-secondary">
                    <i class="feather-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="{{ route('settings.au.aspirations.update', $aspiration->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Aspiration Number <span
                                        class="text-danger">*</span></label>
                                <input type="number" name="number"
                                    class="form-control @error('number') is-invalid @enderror"
                                    value="{{ old('number', $aspiration->number) }}" min="1" required>
                                @error('number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-9">
                                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title"
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title', $aspiration->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $aspiration->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Status</label>
                                <div class="form-check mt-2">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                        id="is_active" {{ old('is_active', $aspiration->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('settings.au.aspirations.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save me-1"></i> Update Aspiration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

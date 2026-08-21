@extends('layouts.app')

@section('title', 'Add Goal')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Add Goal</h4>
                    <p class="text-muted mb-0">Add a new Agenda 2063 goal.</p>
                </div>
                <a href="{{ route('settings.au.goals.index') }}" class="btn btn-outline-secondary">
                    <i class="feather-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="{{ route('settings.au.goals.store') }}" method="POST">
                        @csrf

                        <div class="row g-4">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Goal Number <span class="text-danger">*</span></label>
                                <input type="number" name="number"
                                    class="form-control @error('number') is-invalid @enderror"
                                    value="{{ old('number', $nextNumber) }}" min="1" required>
                                @error('number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-9">
                                <label class="form-label fw-semibold">Parent Aspiration <span
                                        class="text-danger">*</span></label>
                                <select name="aspiration_id"
                                    class="form-select @error('aspiration_id') is-invalid @enderror" required>
                                    <option value="">-- Select Aspiration --</option>
                                    @foreach ($aspirations as $aspiration)
                                        <option value="{{ $aspiration->id }}"
                                            {{ old('aspiration_id') == $aspiration->id ? 'selected' : '' }}>
                                            Aspiration {{ $aspiration->number }}: {{ Str::limit($aspiration->title, 60) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('aspiration_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title"
                                    class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}"
                                    placeholder="e.g. A high standard of living, quality of life and well-being" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Detailed description of this goal...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Status</label>
                                <div class="form-check mt-2">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                        id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('settings.au.goals.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="feather-check me-1"></i> Save Goal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

@extends('layouts.app')

@section('title', 'Create Role')

@section('content')
    <div class="nxl-container">
        <div class="page-header mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="feather-shield me-2"></i>Create Role
                </h4>
                <p class="text-muted mb-0">Add a role before assigning its permissions.</p>
            </div>
            <a href="{{ route('system.roles.index') }}" class="btn btn-light">
                <i class="feather-arrow-left me-1"></i>Back to Roles
            </a>
        </div>

        <div class="row">
            <div class="col-xl-7 col-lg-8">
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <strong>Please correct the following:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('system.roles.store') }}">
                    @csrf

                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">
                                    Role Name <span class="text-danger">*</span>
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    class="form-control @error('name') is-invalid @enderror"
                                    maxlength="255"
                                    required
                                    autofocus
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label fw-semibold">Description</label>
                                <textarea
                                    id="description"
                                    name="description"
                                    class="form-control @error('description') is-invalid @enderror"
                                    rows="4"
                                >{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('system.roles.index') }}" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-save me-1"></i>Create Role
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

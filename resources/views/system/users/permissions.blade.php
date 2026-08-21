@extends('layouts.app')

@section('title', 'User Permissions')

@section('content')
    <div class="nxl-container">

        {{-- HEADER --}}
        <div class="page-header mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bi bi-lock me-1"></i>
                    Direct User Permissions
                </h4>
                <p class="text-muted mb-0">
                    User: <strong>{{ $user->name }}</strong> (Overrides role permissions)
                </p>
            </div>

            <a href="{{ route('system.users.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        {{-- INFO --}}
        <div class="alert alert-warning">
            <strong>Note:</strong>
            These permissions are applied <u>in addition</u> to the user’s role permissions.
            Use sparingly for exceptions.
        </div>

        {{-- FORM --}}
        <form method="POST" action="{{ route('system.users.permissions.sync', $user->id) }}">
            @csrf

            @foreach ($permissions as $module => $modulePermissions)
                <div class="card shadow-sm mb-3 border-0">

                    <div class="card-header bg-light">
                        <strong>{{ strtoupper($module) }}</strong>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            @foreach ($modulePermissions as $permission)
                                <div class="col-md-4 mb-2">
                                    <label class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="permissions[]"
                                            value="{{ $permission->id }}"
                                            {{ $user->permissions->contains('id', $permission->id) ? 'checked' : '' }}>
                                        <span class="form-check-label">
                                            {{ $permission->name }}
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            @endforeach

            <div class="text-end mt-4">
                <button class="btn btn-primary px-4">
                    <i class="bi bi-save"></i>
                    Save Permissions
                </button>
            </div>
        </form>

    </div>
@endsection

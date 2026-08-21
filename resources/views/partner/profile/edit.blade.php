@extends('layouts.partner')

@section('content')
<div class="nxl-container">
    <div class="page-header">
        <div>
            <h4 class="fw-bold mb-1">{{ __('partner.profile_settings') }}</h4>
            <p class="text-muted mb-0">{{ __('partner.manage_profile_description') }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="feather-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="feather-alert-triangle me-2"></i>
            <strong>{{ __('partner.validation_errors') }}</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mt-3">
        <!-- Profile Information Card -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="feather-user me-2"></i>{{ __('partner.personal_information') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('partner.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label fw-semibold">{{ __('partner.full_name') }} *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">{{ __('partner.email_address') }} *</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">{{ __('partner.email_used_for_login') }}</small>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold mb-3">
                            <i class="feather-lock me-2"></i>{{ __('partner.change_password') }}
                        </h6>
                        <p class="text-muted small mb-3">{{ __('partner.change_password_description') }}</p>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">{{ __('partner.current_password') }}</label>
                            <input type="password" name="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">{{ __('partner.new_password') }}</label>
                                <input type="password" name="new_password"
                                       class="form-control @error('new_password') is-invalid @enderror">
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('partner.password_requirements') }}</small>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">{{ __('partner.confirm_new_password') }}</label>
                                <input type="password" name="new_password_confirmation" class="form-control">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('partner.dashboard') }}" class="btn btn-light">
                                <i class="feather-arrow-left me-1"></i> {{ __('partner.back_to_dashboard') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save me-1"></i> {{ __('partner.save_changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Organization Information Card (Read-only) -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="feather-briefcase me-2"></i>{{ __('partner.organization_info') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">{{ __('partner.organization_name') }}</label>
                        <p class="fw-semibold mb-0">{{ $funder->name }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">{{ __('partner.funder_type') }}</label>
                        <p class="fw-semibold mb-0">
                            <span class="badge bg-primary">{{ ucfirst($funder->type) }}</span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">{{ __('partner.default_currency') }}</label>
                        <p class="fw-semibold mb-0">{{ $funder->currency }}</p>
                    </div>

                    @if($funder->contact_phone)
                        <div class="mb-3">
                            <label class="form-label text-muted small">{{ __('partner.contact_phone') }}</label>
                            <p class="fw-semibold mb-0">{{ $funder->contact_phone }}</p>
                        </div>
                    @endif

                    <div class="alert alert-info mb-0 mt-3">
                        <small>
                            <i class="feather-info me-1"></i>
                            {{ __('partner.organization_info_readonly') }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Account Security Card -->
            <div class="card shadow-sm mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="feather-shield me-2"></i>{{ __('partner.account_security') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <i class="feather-check-circle text-success me-2 mt-1"></i>
                        <div>
                            <p class="mb-0 fw-semibold">{{ __('partner.account_verified') }}</p>
                            <small class="text-muted">{{ __('partner.email_verified') }}</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-start">
                        <i class="feather-lock text-primary me-2 mt-1"></i>
                        <div>
                            <p class="mb-0 fw-semibold">{{ __('partner.secure_connection') }}</p>
                            <small class="text-muted">{{ __('partner.https_encryption') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Show/hide password change fields based on current password input
document.addEventListener('DOMContentLoaded', function() {
    const currentPassword = document.querySelector('input[name="current_password"]');
    const newPassword = document.querySelector('input[name="new_password"]');
    const confirmPassword = document.querySelector('input[name="new_password_confirmation"]');

    currentPassword.addEventListener('input', function() {
        if (this.value.length > 0) {
            newPassword.setAttribute('required', 'required');
            confirmPassword.setAttribute('required', 'required');
        } else {
            newPassword.removeAttribute('required');
            confirmPassword.removeAttribute('required');
        }
    });
});
</script>
@endpush

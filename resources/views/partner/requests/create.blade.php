@extends('layouts.partner')

@section('content')
<div class="nxl-container">
    <div class="page-header">
        <div>
            <h4 class="fw-bold mb-1">{{ __('partner.create_request') }}</h4>
            <p class="text-muted mb-0">{{ __('partner.create_request_description') }}</p>
        </div>
        <a href="{{ route('partner.requests.index') }}" class="btn btn-light">
            <i class="feather-arrow-left me-1"></i> {{ __('partner.back') }}
        </a>
    </div>

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
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="feather-message-circle me-2"></i>{{ __('partner.request_information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('partner.requests.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold">{{ __('partner.related_program') }}</label>
                            <select name="program_funding_id" class="form-select">
                                <option value="">{{ __('partner.general_inquiry') }}</option>
                                @foreach($fundings as $funding)
                                    <option value="{{ $funding->id }}" {{ old('program_funding_id') == $funding->id ? 'selected' : '' }}>
                                        {{ $funding->program_name ?? ($funding->program->name ?? '—') }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('partner.related_program_help') }}</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">{{ __('partner.request_type') }} *</label>
                            <select name="request_type" class="form-select @error('request_type') is-invalid @enderror" required>
                                <option value="">{{ __('partner.select_type') }}</option>
                                <option value="financial_report" {{ old('request_type') == 'financial_report' ? 'selected' : '' }}>
                                    {{ __('partner.financial_report') }}
                                </option>
                                <option value="progress_update" {{ old('request_type') == 'progress_update' ? 'selected' : '' }}>
                                    {{ __('partner.progress_update') }}
                                </option>
                                <option value="documentation" {{ old('request_type') == 'documentation' ? 'selected' : '' }}>
                                    {{ __('partner.documentation') }}
                                </option>
                                <option value="other" {{ old('request_type') == 'other' ? 'selected' : '' }}>
                                    {{ __('partner.other') }}
                                </option>
                            </select>
                            @error('request_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">{{ __('partner.priority') }} *</label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>
                                    {{ __('partner.normal') }}
                                </option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>
                                    {{ __('partner.low') }}
                                </option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                                    {{ __('partner.high') }}
                                </option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>
                                    {{ __('partner.urgent') }}
                                </option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">{{ __('partner.subject') }} *</label>
                            <input type="text" name="subject"
                                   class="form-control @error('subject') is-invalid @enderror"
                                   value="{{ old('subject') }}"
                                   placeholder="{{ __('partner.subject_placeholder') }}"
                                   required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">{{ __('partner.message') }} *</label>
                            <textarea name="message"
                                      class="form-control @error('message') is-invalid @enderror"
                                      rows="8"
                                      placeholder="{{ __('partner.message_placeholder') }}"
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">{{ __('partner.message_help') }}</small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('partner.requests.index') }}" class="btn btn-light">
                                {{ __('partner.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-send me-1"></i> {{ __('partner.submit_request') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="feather-info me-2"></i>{{ __('partner.request_guidelines') }}
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-2">{{ __('partner.request_types_info') }}</h6>
                    <ul class="mb-3 small">
                        <li><strong>{{ __('partner.financial_report') }}</strong>: {{ __('partner.financial_report_desc') }}</li>
                        <li><strong>{{ __('partner.progress_update') }}</strong>: {{ __('partner.progress_update_desc') }}</li>
                        <li><strong>{{ __('partner.documentation') }}</strong>: {{ __('partner.documentation_desc') }}</li>
                        <li><strong>{{ __('partner.other') }}</strong>: {{ __('partner.other_desc') }}</li>
                    </ul>

                    <h6 class="fw-bold mb-2">{{ __('partner.priority_levels') }}</h6>
                    <ul class="mb-3 small">
                        <li><strong>{{ __('partner.urgent') }}</strong>: {{ __('partner.urgent_desc') }}</li>
                        <li><strong>{{ __('partner.high') }}</strong>: {{ __('partner.high_desc') }}</li>
                        <li><strong>{{ __('partner.normal') }}</strong>: {{ __('partner.normal_desc') }}</li>
                        <li><strong>{{ __('partner.low') }}</strong>: {{ __('partner.low_desc') }}</li>
                    </ul>

                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="feather-clock me-1"></i>
                            {{ __('partner.response_time_info') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

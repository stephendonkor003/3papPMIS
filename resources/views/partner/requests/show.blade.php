@extends('layouts.partner')

@section('content')
<div class="nxl-container">
    <div class="page-header">
        <div>
            <h4 class="fw-bold mb-1">{{ __('partner.request_details') }}</h4>
            <p class="text-muted mb-0">{{ __('partner.request_details_description') }}</p>
        </div>
        <a href="{{ route('partner.requests.index') }}" class="btn btn-light">
            <i class="feather-arrow-left me-1"></i> {{ __('partner.back_to_requests') }}
        </a>
    </div>

    <div class="row mt-3">
        <div class="col-lg-8">
            <!-- Request Information -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="feather-message-circle me-2"></i>{{ $request->subject }}
                    </h5>
                    <span class="badge {{ $request->getStatusBadgeClass() }}">
                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                    </span>
                </div>
                <div class="card-body">
                    <!-- Request Details -->
                    <div class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ __('partner.request_type') }}</label>
                                <p class="fw-semibold mb-0">
                                    <span class="badge bg-info">
                                        {{ ucfirst(str_replace('_', ' ', $request->request_type)) }}
                                    </span>
                                </p>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ __('partner.priority') }}</label>
                                <p class="fw-semibold mb-0">
                                    @php
                                        $priorityClass = match($request->priority) {
                                            'urgent' => 'bg-danger',
                                            'high' => 'bg-warning text-dark',
                                            'normal' => 'bg-primary',
                                            'low' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $priorityClass }}">
                                        {{ ucfirst($request->priority) }}
                                    </span>
                                </p>
                            </div>

                            @if($request->programFunding)
                                <div class="col-md-12">
                                    <label class="form-label text-muted small">{{ __('partner.related_program') }}</label>
                                    <p class="fw-semibold mb-0">
                                        <i class="feather-folder me-1"></i>
                                        {{ $request->programFunding->program_name ?? ($request->programFunding->program->name ?? '—') }}
                                    </p>
                                </div>
                            @endif

                            <div class="col-md-12">
                                <label class="form-label text-muted small">{{ __('partner.submitted_date') }}</label>
                                <p class="fw-semibold mb-0">
                                    <i class="feather-calendar me-1"></i>
                                    {{ $request->created_at->format('F d, Y \a\t h:i A') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Request Message -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-2">{{ __('partner.your_message') }}</h6>
                        <div class="bg-light p-3 rounded">
                            {{ $request->message }}
                        </div>
                    </div>

                    @if($request->response)
                        <hr>

                        <!-- Admin Response -->
                        <div>
                            <h6 class="fw-bold mb-2">
                                <i class="feather-message-square me-1 text-success"></i>
                                {{ __('partner.admin_response') }}
                            </h6>
                            <div class="alert alert-success">
                                {{ $request->response }}
                            </div>
                            <div class="d-flex align-items-center text-muted small">
                                <i class="feather-user me-1"></i>
                                <span class="me-3">{{ __('partner.responded_by') }}: {{ $request->responder->name ?? 'Admin' }}</span>
                                <i class="feather-clock me-1"></i>
                                <span>{{ $request->responded_at->format('F d, Y \a\t h:i A') }}</span>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="feather-clock me-2"></i>
                            {{ __('partner.awaiting_response') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status Timeline -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="feather-activity me-2"></i>{{ __('partner.request_status') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Submitted -->
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ __('partner.submitted') }}</h6>
                                <p class="text-muted small mb-0">
                                    {{ $request->created_at->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        </div>

                        <!-- In Progress -->
                        @if(in_array($request->status, ['in_progress', 'completed']))
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">{{ __('partner.in_progress') }}</h6>
                                    <p class="text-muted small mb-0">
                                        {{ __('partner.being_reviewed') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        <!-- Completed -->
                        @if($request->status === 'completed')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">{{ __('partner.completed') }}</h6>
                                    <p class="text-muted small mb-0">
                                        {{ $request->responded_at->format('M d, Y h:i A') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        <!-- Rejected -->
                        @if($request->status === 'rejected')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">{{ __('partner.rejected') }}</h6>
                                    <p class="text-muted small mb-0">
                                        {{ $request->responded_at->format('M d, Y h:i A') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="feather-zap me-2"></i>{{ __('partner.quick_actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    @can('partner.requests.create')
                        <a href="{{ route('partner.requests.create') }}" class="btn btn-primary w-100 mb-2">
                            <i class="feather-plus me-1"></i> {{ __('partner.new_request') }}
                        </a>
                    @endcan

                    <a href="{{ route('partner.requests.index') }}" class="btn btn-outline-primary w-100">
                        <i class="feather-list me-1"></i> {{ __('partner.all_requests') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -26px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid #fff;
}

.timeline-content h6 {
    font-size: 14px;
    font-weight: 600;
}
</style>
@endsection

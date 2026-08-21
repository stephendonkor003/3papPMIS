@extends('layouts.partner')

@section('content')
<div class="nxl-container">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('partner.dashboard') }}">{{ __('partner.dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('partner.programs.index') }}">{{ __('partner.funded_programs') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('partner.programs.show', $funding->id) }}">{{ $activity->project->program->name }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('partner.projects.show', $activity->project->id) }}">{{ $activity->project->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $activity->name }}</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">{{ $activity->name }}</h4>
                <p class="text-muted mb-0">{{ __('partner.activity_details_description') }}</p>
            </div>
            <a href="{{ route('partner.projects.show', $activity->project->id) }}" class="btn btn-light">
                <i class="feather-arrow-left me-1"></i> {{ __('partner.back_to_project') }}
            </a>
        </div>
    </div>

    <div class="row mt-3">
        <!-- Main Activity Information -->
        <div class="col-lg-8">
            <!-- Activity Overview Card -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="feather-info me-2"></i>{{ __('partner.activity_overview') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('partner.activity_name') }}</label>
                            <p class="fw-semibold mb-0">{{ $activity->name }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('partner.project') }}</label>
                            <p class="fw-semibold mb-0">
                                <a href="{{ route('partner.projects.show', $activity->project->id) }}" class="text-decoration-none">
                                    {{ $activity->project->name }}
                                </a>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('partner.governance_node') }}</label>
                            <p class="fw-semibold mb-0">
                                {{ $activity->governanceNode->name ?? '-' }}
                                @if($activity->governanceNode)
                                    <br><small class="text-muted">{{ $activity->governanceNode->level->name ?? '' }}</small>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('partner.budget') }}</label>
                            <p class="fw-semibold mb-0 text-success">
                                {{ $activity->currency ?? $activity->project->currency ?? $funding->currency }} {{ number_format($activity->budget ?? 0, 2) }}
                            </p>
                        </div>

                        @if($activity->description)
                            <div class="col-md-12">
                                <label class="text-muted small">{{ __('partner.description') }}</label>
                                <p class="mb-0">{{ $activity->description }}</p>
                            </div>
                        @endif

                        @if($activity->start_date || $activity->end_date)
                            <div class="col-md-6">
                                <label class="text-muted small">{{ __('partner.start_date') }}</label>
                                <p class="fw-semibold mb-0">{{ $activity->start_date ? $activity->start_date->format('M d, Y') : '—' }}</p>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small">{{ __('partner.end_date') }}</label>
                                <p class="fw-semibold mb-0">{{ $activity->end_date ? $activity->end_date->format('M d, Y') : '—' }}</p>
                            </div>
                        @endif

                        @if($activity->responsible_person)
                            <div class="col-md-6">
                                <label class="text-muted small">{{ __('partner.responsible_person') }}</label>
                                <p class="fw-semibold mb-0">{{ $activity->responsible_person }}</p>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('partner.status') }}</label>
                            <p class="fw-semibold mb-0">
                                <span class="badge bg-{{ $activity->status === 'completed' ? 'success' : ($activity->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($activity->status ?? 'pending') }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sub-Activities Under This Activity -->
            @if($activity->subActivities && $activity->subActivities->count() > 0)
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="feather-layers me-2"></i>{{ __('partner.sub_activities') }} ({{ $activity->subActivities->count() }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @foreach($activity->subActivities as $subActivity)
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2 fw-bold text-dark">{{ $subActivity->name }}</h6>
                                            @if($subActivity->description)
                                                <p class="mb-2 text-muted small">{{ $subActivity->description }}</p>
                                            @endif
                                            <div class="d-flex gap-3 flex-wrap">
                                                @if($subActivity->governanceNode)
                                                    <small class="text-muted">
                                                        <i class="feather-map-pin me-1"></i>
                                                        <strong>{{ __('partner.node') }}:</strong> {{ $subActivity->governanceNode->name }}
                                                    </small>
                                                @endif
                                                @if($subActivity->budget)
                                                    <small class="text-success">
                                                        <i class="feather-dollar-sign me-1"></i>
                                                        <strong>{{ __('partner.budget') }}:</strong> {{ $subActivity->currency ?? $activity->currency }} {{ number_format($subActivity->budget, 2) }}
                                                    </small>
                                                @endif
                                                @if($subActivity->responsible_person)
                                                    <small class="text-muted">
                                                        <i class="feather-user me-1"></i>
                                                        <strong>{{ __('partner.responsible') }}:</strong> {{ $subActivity->responsible_person }}
                                                    </small>
                                                @endif
                                            </div>
                                            @if($subActivity->start_date || $subActivity->end_date)
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="feather-calendar me-1"></i>
                                                        @if($subActivity->start_date)
                                                            {{ $subActivity->start_date->format('M d, Y') }}
                                                        @endif
                                                        @if($subActivity->start_date && $subActivity->end_date)
                                                            →
                                                        @endif
                                                        @if($subActivity->end_date)
                                                            {{ $subActivity->end_date->format('M d, Y') }}
                                                        @endif
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ms-3">
                                            <span class="badge bg-{{ $subActivity->status === 'completed' ? 'success' : ($subActivity->status === 'in_progress' ? 'warning text-dark' : 'secondary') }}">
                                                {{ ucfirst($subActivity->status ?? 'pending') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="feather-info-circle me-2"></i>
                    {{ __('partner.no_sub_activities_found') }}
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Activity Summary Card -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="feather-bar-chart-2 me-2"></i>{{ __('partner.activity_summary') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('partner.total_sub_activities') }}</label>
                        <p class="fw-bold mb-0 fs-4 text-primary">{{ $activity->subActivities->count() }}</p>
                    </div>

                    @if($activity->subActivities->count() > 0)
                        <div class="mb-3">
                            <label class="text-muted small">{{ __('partner.completed_sub_activities') }}</label>
                            <p class="fw-bold mb-0 fs-4 text-success">
                                {{ $activity->subActivities->where('status', 'completed')->count() }}
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small">{{ __('partner.in_progress_sub_activities') }}</label>
                            <p class="fw-bold mb-0 fs-4 text-warning">
                                {{ $activity->subActivities->where('status', 'in_progress')->count() }}
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small">{{ __('partner.completion_rate') }}</label>
                            <div class="progress" style="height: 25px;">
                                @php
                                    $total = $activity->subActivities->count();
                                    $completed = $activity->subActivities->where('status', 'completed')->count();
                                    $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
                                @endphp
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%">
                                    {{ $percentage }}%
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($activity->budget)
                        <div class="mb-0">
                            <label class="text-muted small">{{ __('partner.total_budget') }}</label>
                            <p class="fw-bold mb-0 fs-5 text-success">
                                {{ $activity->currency ?? $funding->currency }} {{ number_format($activity->budget, 2) }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Navigation Card -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="feather-navigation me-2"></i>{{ __('partner.navigation') }}</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('partner.projects.show', $activity->project->id) }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="feather-arrow-left me-1"></i> {{ __('partner.back_to_project') }}
                    </a>

                    <a href="{{ route('partner.programs.show', $funding->id) }}" class="btn btn-outline-secondary w-100 mb-2">
                        <i class="feather-package me-1"></i> {{ __('partner.view_program') }}
                    </a>

                    <a href="{{ route('partner.programs.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="feather-list me-1"></i> {{ __('partner.all_programs') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

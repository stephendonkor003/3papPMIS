@extends('layouts.app')
@section('title', 'My Site Visit Assignments')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">

            <!-- ===== Header ===== -->
            <div class="page-header mb-4">
                <h4 class="text-success fw-bold">
                    <i class="bi bi-clipboard2-check me-2"></i>My Site Visit Assignments
                </h4>
                <p class="text-muted mb-0">Below are the consortia assigned to your team for evaluation and rework.</p>
            </div>

            @if ($team && $team->consortia->count())
                <div class="row g-3">
                    @foreach ($team->consortia as $assign)
                        @php
                            $evaluation = \App\Models\SiteVisitEvaluation::where(
                                'consortium_id',
                                $assign->consortium_id,
                            )
                                ->where('team_id', $team->id)
                                ->latest()
                                ->first();
                            $reworkStatus = $evaluation->rework_status ?? 'none';
                        @endphp

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100 position-relative">

                                <!-- ===== Card Header ===== -->
                                <div
                                    class="card-header bg-gradient bg-light fw-semibold text-dark d-flex justify-content-between align-items-center">
                                    <span>{{ $assign->consortium->think_tank_name ?? 'Unnamed Consortium' }}</span>
                                    @if ($reworkStatus === 'requested')
                                        <span class="badge bg-warning text-dark"><i
                                                class="bi bi-exclamation-triangle me-1"></i>Rework Requested</span>
                                    @elseif ($reworkStatus === 'completed')
                                        <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>Rework
                                            Done</span>
                                    @else
                                        <span class="badge bg-primary">{{ ucfirst($assign->status) }}</span>
                                    @endif
                                </div>

                                <!-- ===== Card Body ===== -->
                                <div class="card-body bg-light">
                                    <p class="text-muted mb-2 small">
                                        <strong>Country:</strong> {{ $assign->consortium->country ?? 'N/A' }} <br>
                                        <strong>Sub-region:</strong>
                                        {{ is_array(json_decode($assign->consortium->sub_region, true))
                                            ? implode(', ', json_decode($assign->consortium->sub_region, true))
                                            : $assign->consortium->sub_region ?? 'N/A' }}
                                    </p>

                                    <hr class="my-2">

                                    <!-- ===== Action Buttons ===== -->
                                    @if ($assign->status == 'pending' && $reworkStatus == 'none')
                                        <a href="{{ route('sitevisit.form', $assign->consortium_id) }}"
                                            class="btn btn-outline-success btn-sm">
                                            <i class="bi bi-journal-text me-1"></i> Conduct Site Visit
                                        </a>
                                    @elseif ($reworkStatus == 'requested')
                                        <div class="alert alert-warning small mb-2 p-2">
                                            <i class="bi bi-info-circle me-1"></i>Rework required. Please amend your
                                            previous evaluation.
                                        </div>
                                        <a href="{{ route('sitevisit.edit.rework', $evaluation->id) }}"
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-pencil-square me-1"></i> Amend Evaluation
                                        </a>
                                    @elseif ($assign->status == 'completed' && $reworkStatus == 'none')
                                        <span class="text-success fw-semibold"><i
                                                class="bi bi-check-circle me-1"></i>Completed</span>
                                    @elseif ($reworkStatus == 'completed')
                                        <span class="text-success fw-semibold"><i
                                                class="bi bi-check2-circle me-1"></i>Rework Completed</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning shadow-sm">
                    <i class="bi bi-info-circle me-2"></i>No site visit assignments yet.
                </div>
            @endif

        </div>
    </main>

    <!-- Optional Custom CSS -->

    <style>
        .card {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .badge {
            font-size: 0.8rem;
        }

        .alert-warning {
            background-color: #fff7da;
            border: 1px solid #ffe58a;
        }

        .btn-outline-primary:hover,
        .btn-outline-success:hover {
            color: #fff !important;
        }
    </style>
@endsection

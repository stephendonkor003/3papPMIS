@extends('layouts.app')
@section('title', 'Site Visit Evaluation Reports')

@push('styles')
    <style>
        /* === Fix Modals Display === */
        .modal {
            z-index: 1065 !important;
            display: none;
            overflow-y: auto;
            background-color: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(3px);
        }

        .modal-dialog {
            position: relative !important;
            margin: 5rem auto;
            z-index: 1070;
        }

        .modal-content {
            border-radius: 0.75rem;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.25);
            border: none;
            overflow: hidden;
            animation: fadeInUp 0.3s ease-out;
        }

        .modal-header.bg-warning {
            background: linear-gradient(90deg, #f9d71c, #ffcd39);
            border-bottom: none;
        }

        .modal-footer {
            border-top: none;
            background-color: #fffbea;
        }

        .modal textarea.form-control {
            background-color: #fff8e1;
            border-color: #f1c232;
        }

        .modal textarea.form-control:focus {
            border-color: #d6aa00;
            box-shadow: 0 0 0 0.2rem rgba(255, 214, 10, 0.3);
        }

        .modal .btn-close {
            filter: brightness(0) saturate(100%) invert(20%) sepia(94%) saturate(2217%) hue-rotate(12deg) brightness(105%) contrast(105%);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush

@section('content')

    <!-- ====== MODAL TEMPLATE (works outside main container) ====== -->
    @foreach ($evaluations as $eval)
        @if (Auth::user()->user_type === 'admin')
            <div class="modal fade" id="reworkModal{{ $eval->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('sitevisit.request.rework', $eval->id) }}" class="modal-content">
                        @csrf
                        <div class="modal-header bg-warning text-dark">
                            <h6 class="modal-title">
                                <i class="bi bi-arrow-repeat me-1"></i>Request Rework
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label fw-semibold">Reason / Instruction</label>
                            <textarea name="rework_comment" class="form-control" rows="3" required
                                placeholder="Explain what needs to be corrected or updated..."></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning fw-bold">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach

    <!-- ====== MAIN CONTENT ====== -->
    <main class="nxl-container">
        <div class="nxl-content">

            <!-- ===== Page Header ===== -->
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="text-primary fw-bold mb-1">
                        <i class="bi bi-bar-chart-line me-2"></i>Site Visit Evaluation Reports
                    </h4>
                    <p class="text-muted mb-0">
                        Overview of all completed site visit evaluations and performance summaries.
                    </p>
                </div>
                <a href="{{ route('sitevisit.report.pdf') }}" class="btn btn-danger btn-sm shadow-sm">
                    <i class="bi bi-filetype-pdf me-1"></i> Download All (PDF)
                </a>
            </div>

            <!-- ===== Summary Cards ===== -->
            <div class="row g-3 mb-4">
                @php
                    $total = $evaluations->count();
                    $avgScore = $evaluations->avg('total_score') ?? 0;
                    $highest = $evaluations->max('total_score') ?? 0;
                    $lowest = $evaluations->min('total_score') ?? 0;
                @endphp

                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100 text-center">
                        <div class="card-body">
                            <h6 class="text-muted">Total Evaluations</h6>
                            <h3 class="fw-bold text-primary">{{ $total }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100 text-center">
                        <div class="card-body">
                            <h6 class="text-muted">Average Score</h6>
                            <h3 class="fw-bold text-success">{{ number_format($avgScore, 1) }}/35</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100 text-center">
                        <div class="card-body">
                            <h6 class="text-muted">Highest Score</h6>
                            <h3 class="fw-bold text-info">{{ $highest }}/35</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100 text-center">
                        <div class="card-body">
                            <h6 class="text-muted">Lowest Score</h6>
                            <h3 class="fw-bold text-danger">{{ $lowest }}/35</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== Charts Section ===== -->
            <div class="row mb-5">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-gradient bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Scores by Consortium</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="scoreChart" height="120"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-gradient bg-success text-white">
                            <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Performance Distribution</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="performanceChart" height="120"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== Table Section ===== -->
            <div class="card border-0 shadow-sm">
                <div
                    class="card-header bg-gradient bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Detailed Evaluations</h6>
                    <small>Generated on {{ now()->format('F d, Y') }}</small>
                </div>

                <div class="card-body bg-light">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center" id="evaluationsTable">
                            <thead class="table-primary text-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Consortium</th>
                                    <th>Team</th>
                                    <th>Leader</th>
                                    <th>Total Score</th>
                                    <th>Evaluation Date</th>
                                    <th>General Observation</th>
                                    <th>Comments</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($evaluations as $index => $eval)
                                    @php
                                        $rowClass = match ($eval->rework_status) {
                                            'requested' => 'table-warning',
                                            'completed' => 'table-success',
                                            default => '',
                                        };
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td class="fw-semibold text-start">
                                            {{ $eval->consortium->think_tank_name ?? 'N/A' }}
                                        </td>
                                        <td>{{ $eval->team->name ?? '-' }}</td>
                                        <td>{{ $eval->leader->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $score = $eval->total_score;
                                                $color =
                                                    $score >= 28 ? 'success' : ($score >= 20 ? 'warning' : 'danger');
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ $score }}/35</span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($eval->evaluation_date)->format('M d, Y') }}</td>
                                        <td class="text-muted text-start">
                                            {{ Str::limit($eval->general_observations ?? '—', 50) }}
                                        </td>
                                        <td class="text-muted text-start">
                                            {{ Str::limit($eval->additional_comments ?? '—', 50) }}
                                        </td>
                                        <td class="text-center">
                                            {{-- PDF --}}
                                            <a href="{{ route('sitevisit.report.single.pdf', $eval->id) }}"
                                                class="btn btn-sm btn-outline-danger me-1" title="Download PDF">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>

                                            {{-- Rework Logic --}}
                                            @if (Auth::user()->user_type === 'admin')
                                                @if ($eval->rework_status === 'none' || $eval->rework_status === 'completed')
                                                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                                        data-bs-target="#reworkModal{{ $eval->id }}">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    </button>
                                                @else
                                                    <span class="badge bg-warning">Rework Requested</span>
                                                @endif
                                            @endif

                                            @if (Auth::user()->user_type === 'evaluator' || Auth::user()->user_type === 'admin')
                                                @if (
                                                    $eval->rework_status === 'requested' &&
                                                        ($eval->evaluator_id === Auth::id() || optional($eval->team)->leader_id === Auth::id()))
                                                    <a href="{{ route('sitevisit.edit.rework', $eval->id) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Amend Evaluation">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                @elseif($eval->rework_status === 'completed')
                                                    <span class="badge bg-success">Rework Done</span>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-muted py-3">No evaluations submitted yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- ===== Chart Logic ===== -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const consortia = @json($evaluations->pluck('consortium.think_tank_name'));
            const scores = @json($evaluations->pluck('total_score'));

            // Bar Chart
            const ctxBar = document.getElementById('scoreChart');
            if (ctxBar) {
                new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: consortia,
                        datasets: [{
                            label: 'Total Score (out of 35)',
                            data: scores,
                            backgroundColor: 'rgba(18,86,160,0.6)',
                            borderColor: 'rgba(18,86,160,1)',
                            borderWidth: 1,
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 35,
                                ticks: {
                                    stepSize: 5
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ctx.raw + '/35'
                                }
                            }
                        }
                    }
                });
            }

            // Pie Chart
            const high = scores.filter(s => s >= 28).length;
            const medium = scores.filter(s => s >= 20 && s < 28).length;
            const low = scores.filter(s => s < 20).length;

            const ctxPie = document.getElementById('performanceChart');
            if (ctxPie) {
                new Chart(ctxPie, {
                    type: 'pie',
                    data: {
                        labels: ['High (28–35)', 'Medium (20–27)', 'Low (<20)'],
                        datasets: [{
                            data: [high, medium, low],
                            backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // DataTables
            if (window.$ && $.fn.DataTable) {
                $('#evaluationsTable').DataTable({
                    pageLength: 10,
                    order: [
                        [4, 'desc']
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search evaluations..."
                    }
                });
            }
        });
    </script>
@endsection

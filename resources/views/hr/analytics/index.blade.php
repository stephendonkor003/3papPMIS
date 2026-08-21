@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="feather-bar-chart-2 text-primary me-2"></i>
                    Recruitment Analytics
                </h4>
                <p class="text-muted mb-0">
                    Overview of recruitment pipeline performance and hiring outcomes
                </p>
            </div>
        </div>

        {{-- ================= KPI CARDS ================= --}}
        <div class="row g-3 mb-4">
            @foreach ([['label' => 'Applicants', 'value' => $totalApplicants, 'icon' => 'users', 'color' => 'primary'], ['label' => 'Scored', 'value' => $scored, 'icon' => 'edit', 'color' => 'info'], ['label' => 'Shortlisted', 'value' => $shortlisted, 'icon' => 'star', 'color' => 'warning'], ['label' => 'Hired', 'value' => $hired, 'icon' => 'check-circle', 'color' => 'success'], ['label' => 'Rejected', 'value' => $rejected, 'icon' => 'x-circle', 'color' => 'danger']] as $stat)
                <div class="col-6 col-md">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="icon-box bg-{{ $stat['color'] }}-subtle text-{{ $stat['color'] }}">
                                <i class="feather-{{ $stat['icon'] }}"></i>
                            </div>
                            <div>
                                <small class="text-muted">{{ $stat['label'] }}</small>
                                <h4 class="fw-bold mb-0">{{ $stat['value'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ================= CHARTS ================= --}}
        <div class="row g-4 mb-4">

            {{-- STATUS DISTRIBUTION --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-1">Applicant Status Distribution</h6>
                        <p class="text-muted small mb-3">
                            Breakdown of applicants by recruitment stage
                        </p>
                        <canvas id="statusPieChart" height="180"></canvas>
                    </div>
                </div>
            </div>

            {{-- PIPELINE FUNNEL --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-1">Recruitment Pipeline Flow</h6>
                        <p class="text-muted small mb-3">
                            Candidate drop-off across recruitment stages
                        </p>
                        <canvas id="pipelineBarChart" height="180"></canvas>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= ANALYTICS TABLE ================= --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="fw-semibold mb-1">Recruitment Summary</h6>
                <p class="text-muted small mb-0">
                    Conversion rates across recruitment stages
                </p>
            </div>

            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Stage</th>
                                <th class="text-end">Candidates</th>
                                <th class="text-end">Conversion %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $pipeline = [
                                    'Applicants' => $totalApplicants,
                                    'Scored' => $scored,
                                    'Shortlisted' => $shortlisted,
                                    'Hired' => $hired,
                                ];
                                $prev = null;
                            @endphp

                            @foreach ($pipeline as $stage => $count)
                                @php
                                    $rate = $prev ? ($count / $prev) * 100 : 100;
                                    $prev = $count;
                                @endphp
                                <tr>
                                    <td>{{ $stage }}</td>
                                    <td class="text-end fw-semibold">{{ $count }}</td>
                                    <td class="text-end">
                                        <span
                                            class="badge bg-{{ $rate < 50 ? 'danger' : ($rate < 75 ? 'warning text-dark' : 'success') }}">
                                            {{ number_format($rate, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= AI INSIGHT PLACEHOLDER ================= --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold mb-2">AI Hiring Insights</h6>
                <p class="text-muted mb-0">
                    Based on current data, your strongest funnel performance occurs at the
                    <strong>shortlisting stage</strong>. Consider reviewing rejection criteria
                    earlier in the pipeline to reduce applicant drop-off.
                </p>
            </div>
        </div>

    </div>

@endsection

@push('styles')
<style>
    .icon-box {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        /* STATUS PIE */
        new Chart(document.getElementById('statusPieChart'), {
            type: 'doughnut',
            data: {
                labels: ['Scored', 'Shortlisted', 'Hired', 'Rejected'],
                datasets: [{
                    data: [
                        {{ $scored }},
                        {{ $shortlisted }},
                        {{ $hired }},
                        {{ $rejected }}
                    ],
                    backgroundColor: ['#0dcaf0', '#ffc107', '#198754', '#dc3545']
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '65%'
            }
        });

        /* PIPELINE BAR */
        new Chart(document.getElementById('pipelineBarChart'), {
            type: 'bar',
            data: {
                labels: ['Applicants', 'Scored', 'Shortlisted', 'Hired'],
                datasets: [{
                    label: 'Candidates',
                    data: [
                        {{ $totalApplicants }},
                        {{ $scored }},
                        {{ $shortlisted }},
                        {{ $hired }}
                    ],
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endpush

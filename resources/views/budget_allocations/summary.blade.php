@extends('layouts.app')

@section('content')
    <div class="nxl-container">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="fw-bold text-dark mb-0">Executive Summary</h4>
            <a href="{{ route('budget.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Budget Allocations
            </a>
        </div>

        {{-- SUMMARY CARDS --}}
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 text-center p-3">
                    <h6>Total Sectors</h6>
                    <h3 class="fw-bold text-primary">{{ $totals['sectors'] }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 text-center p-3">
                    <h6>Total Programs</h6>
                    <h3 class="fw-bold text-success">{{ $totals['programs'] }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 text-center p-3">
                    <h6>Total Projects</h6>
                    <h3 class="fw-bold text-info">{{ $totals['projects'] }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 text-center p-3">
                    <h6>Total Budget (USD)</h6>
                    <h3 class="fw-bold text-danger">USD {{ number_format($totals['budget'], 2) }}</h3>
                </div>
            </div>
        </div>

        {{-- CHARTS --}}
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 p-3">
                    <h5 class="fw-bold mb-3">Budget by Sector</h5>
                    <canvas id="sectorChart" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0 p-3">
                    <h5 class="fw-bold mb-3">Budget Distribution by Program</h5>
                    <canvas id="programChart" height="200"></canvas>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="card mt-4 shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Sector-wise Budget Summary</h5>
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Sector</th>
                            <th>Programs</th>
                            <th>Projects</th>
                            <th>Total Budget (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sectors as $index => $sector)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold">{{ $sector->name }}</td>
                                <td>{{ $sector->programs_count }}</td>
                                <td>{{ $sector->projects_count }}</td>
                                <td>{{ number_format($sector->programs_sum_total_budget, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">Grand Total</th>
                            <th>USD {{ number_format($totals['budget'], 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sectorLabels = @json($sectorNames);
        const sectorData = @json($sectorBudgets);
        const programLabels = @json($programNames);
        const programData = @json($programBudgets);

        // Chart 1: Sector
        new Chart(document.getElementById('sectorChart'), {
            type: 'pie',
            data: {
                labels: sectorLabels,
                datasets: [{
                    data: sectorData,
                    backgroundColor: ['#0d6efd', '#198754', '#dc3545', '#ffc107', '#20c997',
                        '#6f42c1'
                    ]
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

        // Chart 2: Programs
        new Chart(document.getElementById('programChart'), {
            type: 'bar',
            data: {
                labels: programLabels,
                datasets: [{
                    label: 'Budget (USD)',
                    data: programData,
                    backgroundColor: '#17a2b8'
                }]
            },
            options: {
                responsive: true,
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

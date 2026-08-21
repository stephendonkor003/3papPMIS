@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        <div class="page-header mb-4">
            <h4 class="fw-bold">Project Budget Analytics Overview</h4>
            <p class="text-muted">Financial distribution, trends & insights across all projects.</p>
        </div>

        {{-- KPI CARDS --}}
        <div class="row g-4 mb-4">

            <div class="col-md-3">
                <div class="card shadow-sm border-0 p-3">
                    <h6 class="text-muted">Total Programs</h6>
                    <h2 class="fw-bold">{{ $totalPrograms }}</h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 p-3">
                    <h6 class="text-muted">Total Projects</h6>
                    <h2 class="fw-bold">{{ $totalProjects }}</h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 p-3">
                    <h6 class="text-muted">Total Program Budget</h6>
                    <h2 class="fw-bold">{{ number_format($totalBudget, 2) }}</h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 p-3">
                    <h6 class="text-muted">Total Allocations Made</h6>
                    <h2 class="fw-bold">{{ number_format($totalAllocations, 2) }}</h2>
                </div>
            </div>

        </div>

        {{-- SECTOR DISTRIBUTION PIE CHART --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="fw-semibold">Budget Distribution by Sector</h5>
            </div>
            <div class="card-body">
                <canvas id="sectorPie"></canvas>
            </div>
        </div>

        {{-- YEARLY TREND CHART --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="fw-semibold">Yearly Allocation Growth Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="yearlyTrend"></canvas>
            </div>
        </div>

        {{-- TOP PROJECTS BAR CHART --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="fw-semibold">Top 5 Projects by Budget</h5>
            </div>
            <div class="card-body">
                <canvas id="topProjects"></canvas>
            </div>
        </div>

    </div>

    {{-- CHART.JS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // PIE CHART — Sector Distribution
        new Chart(document.getElementById('sectorPie'), {
            type: 'pie',
            data: {
                labels: {{ Illuminate\Support\Js::from($sectorDistribution->pluck('sector.name')) }},
                datasets: [{
                    data: {{ Illuminate\Support\Js::from($sectorDistribution->pluck('budget')) }},
                    backgroundColor: [
                        '#1a73e8', '#34a853', '#fbbc05', '#ea4335', '#9c27b0', '#ff9800'
                    ]
                }]
            }
        });

        // LINE CHART — Yearly Trend
        new Chart(document.getElementById('yearlyTrend'), {
            type: 'line',
            data: {
                labels: {{ Illuminate\Support\Js::from($yearlyTrend->pluck('year')) }},
                datasets: [{
                    label: 'Total Allocations',
                    data: {{ Illuminate\Support\Js::from($yearlyTrend->pluck('total')) }},
                    borderColor: '#1a73e8',
                    borderWidth: 3,
                    tension: 0.3
                }]
            }
        });

        // BAR CHART — Top Projects
        new Chart(document.getElementById('topProjects'), {
            type: 'bar',
            data: {
                labels: {{ Illuminate\Support\Js::from($topProjects->pluck('name')) }},
                datasets: [{
                    label: 'Budget',
                    data: {{ Illuminate\Support\Js::from($topProjects->pluck('total_budget')) }},
                    backgroundColor: '#34a853'
                }]
            }
        });
    </script>
@endsection

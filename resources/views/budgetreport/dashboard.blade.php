@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        <!-- PAGE HEADER -->
        <div class="page-header">
            <h4 class="fw-bold text-dark">Budget Dashboard</h4>
        </div>

        <!-- KPI CARDS -->
        <div class="row mt-4">

            <div class="col-md-4">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted">Total Sectors</h6>
                        <h3 class="fw-bold">{{ $sectors->count() }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted">Total Programs</h6>
                        <h3 class="fw-bold">
                            {{ $sectors->sum(fn($s) => $s->programs->count()) }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted">Total Budget</h6>
                        <h3 class="fw-bold text-primary">
                            {{ number_format(
                                $sectors->sum(fn($s) => $s->programs->sum(fn($p) => $p->projects->sum(fn($pr) => $pr->totalAllocation()))),
                                2,
                            ) }}
                        </h3>
                    </div>
                </div>
            </div>

        </div>


        <!-- SECTOR BAR CHART -->
        <div class="card mt-4 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold">Sector Budget Comparison</h5>
                <canvas id="sectorBarChart" height="150"></canvas>
            </div>
        </div>

        <!-- PROGRAM PIE CHART -->
        <div class="card mt-4 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold">Program Budget Distribution</h5>
                <canvas id="programPieChart" height="150"></canvas>
            </div>
        </div>

        <!-- YEAR TREND LINE CHART -->
        <div class="card mt-4 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold">Yearly Budget Trend</h5>
                <canvas id="yearTrendChart" height="150"></canvas>
            </div>
        </div>

    </div>
@endsection



@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        /* ===========================
           PREPARE DATA
           =========================== */

        const sectorNames = @json($sectors->pluck('name'));

        const sectorTotals = @json($sectors->map(fn($s) => $s->programs->sum(fn($p) => $p->projects->sum(fn($pr) => $pr->totalAllocation()))));

        const programNames = @json($sectors->flatMap->programs->pluck('name'));

        const programTotals = @json($sectors->flatMap->programs->map(fn($p) => $p->projects->sum(fn($pr) => $pr->totalAllocation())));

        // Year Trend (Sum of all allocations by year)
        const years = [];
        const yearTotals = [];

        @php
            $yearRange = collect();

            foreach ($sectors as $sector) {
                foreach ($sector->programs as $program) {
                    $yearRange = $yearRange->merge(range($program->start_year, $program->end_year));
                }
            }

            $yearRange = $yearRange->unique()->sort()->values();
        @endphp

        @foreach ($yearRange as $yr)
            years.push({{ $yr }});
            yearTotals.push(
                {{ $sectors->sum(
                    fn($s) => $s->programs->sum(
                        fn($p) => $p->projects->sum(fn($pr) => $pr->allocations->where('year', $yr)->sum('amount')),
                    ),
                ) }}
            );
        @endforeach



        /* ===========================
           CHARTS
           =========================== */

        // BAR CHART — Sector Budgets
        new Chart(document.getElementById('sectorBarChart'), {
            type: 'bar',
            data: {
                labels: sectorNames,
                datasets: [{
                    label: "Budget",
                    data: sectorTotals,
                    backgroundColor: '#0d6efd'
                }]
            }
        });

        // PIE CHART — Program Budgets
        new Chart(document.getElementById('programPieChart'), {
            type: 'pie',
            data: {
                labels: programNames,
                datasets: [{
                    data: programTotals,
                    backgroundColor: [
                        '#0d6efd', '#198754', '#dc3545',
                        '#ffc107', '#0dcaf0', '#6f42c1', '#6610f2'
                    ]
                }]
            }
        });

        // LINE CHART — Yearly Trend
        new Chart(document.getElementById('yearTrendChart'), {
            type: 'line',
            data: {
                labels: years,
                datasets: [{
                    label: "Budget Over Time",
                    data: yearTotals,
                    borderColor: '#0d6efd',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4
                }]
            }
        });
    </script>
@endsection

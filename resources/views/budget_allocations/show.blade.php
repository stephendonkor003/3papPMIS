@extends('layouts.app')

@section('content')
    <div class="nxl-container">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-dark mb-0">{{ $program->name }} – Budget Overview</h4>
                <small class="text-muted">
                    Sector: {{ $program->sector->name ?? 'N/A' }} | Program ID: {{ $program->program_id }}
                </small>
            </div>
            <a href="{{ route('budget.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        {{-- SUMMARY CARDS --}}
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 text-center p-3">
                    <h6>Total Program Budget</h6>
                    <h4 class="fw-bold text-success">USD {{ number_format($program->total_budget, 2) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 text-center p-3">
                    <h6>Total Projects</h6>
                    <h4 class="fw-bold">{{ $program->projects->count() }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 text-center p-3">
                    <h6>Total Activities</h6>
                    <h4 class="fw-bold">
                        {{ $program->projects->sum(fn($p) => $p->activities->count()) }}
                    </h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 text-center p-3">
                    <h6>Total Sub-Activities</h6>
                    <h4 class="fw-bold">
                        {{ $program->projects->sum(fn($p) => $p->activities->sum(fn($a) => $a->subActivities->count())) }}
                    </h4>
                </div>
            </div>
        </div>

        {{-- CHARTS --}}
        <div class="card mt-4 shadow-sm border-0">
            <div class="card-body">
                <h5 class="mb-3 fw-bold">Budget Allocation Chart</h5>
                <canvas id="budgetChart" height="120"></canvas>
            </div>
        </div>

        {{-- DETAILED TABLE --}}
        <div class="card mt-4 shadow-sm border-0">
            <div class="card-body">
                <h5 class="mb-3 fw-bold">Detailed Budget Report</h5>
                <table class="table table-bordered table-striped" id="fullReportTable">
                    <thead class="table-light">
                        <tr>
                            <th>Program ID</th>
                            <th>Project Name</th>
                            <th>Total Budget</th>
                            <th>CFF ({{ now()->year }})</th>
                            @php
                                $years = range(now()->year + 1, now()->year + ($program->years - 1));
                            @endphp
                            @foreach ($years as $y)
                                <th>Year {{ $y }}</th>
                            @endforeach
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($program->projects as $project)
                            <tr class="fw-bold bg-light">
                                <td>{{ $project->project_id }}</td>
                                <td>{{ $project->name }}</td>
                                <td>{{ number_format($project->total_budget, 2) }}</td>

                                {{-- Allocations by year --}}
                                @php
                                    $allocs = $project->allocations->pluck('allocated_amount', 'year');
                                    $first = $allocs[$program->sector->created_at->year ?? now()->year] ?? 0;
                                    $total = 0;
                                @endphp
                                <td>{{ number_format($allocs[now()->year] ?? 0, 2) }}</td>
                                @foreach ($years as $y)
                                    <td>{{ number_format($allocs[$y] ?? 0, 2) }}</td>
                                    @php $total += $allocs[$y] ?? 0; @endphp
                                @endforeach
                                <td>{{ number_format($total, 2) }}</td>
                            </tr>

                            {{-- Activities --}}
                            @foreach ($project->activities as $activity)
                                <tr>
                                    <td class="ps-4 text-muted">{{ $activity->activity_id }}</td>
                                    <td colspan="2">{{ $activity->name }}</td>
                                    <td>{{ number_format($activity->allocations->where('year', now()->year)->sum('allocated_amount'), 2) }}
                                    </td>
                                    @foreach ($years as $y)
                                        <td>{{ number_format($activity->allocations->where('year', $y)->sum('allocated_amount'), 2) }}
                                        </td>
                                    @endforeach
                                    <td>{{ number_format($activity->allocations->sum('allocated_amount'), 2) }}</td>
                                </tr>

                                {{-- Sub Activities --}}
                                @foreach ($activity->subActivities as $sub)
                                    <tr>
                                        <td class="ps-5 text-secondary">{{ $sub->sub_activity_id }}</td>
                                        <td colspan="2">{{ $sub->name }}</td>
                                        <td>{{ number_format($sub->allocations->where('year', now()->year)->sum('allocated_amount'), 2) }}
                                        </td>
                                        @foreach ($years as $y)
                                            <td>{{ number_format($sub->allocations->where('year', $y)->sum('allocated_amount'), 2) }}
                                            </td>
                                        @endforeach
                                        <td>{{ number_format($sub->allocations->sum('allocated_amount'), 2) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Prepare data for chart
        const ctx = document.getElementById('budgetChart');
        const data = {
            labels: [
                @foreach ($program->projects as $project)
                    "{{ $project->name }}",
                @endforeach
            ],
            datasets: [{
                label: 'Budget by Project (USD)',
                data: [
                    @foreach ($program->projects as $project)
                        {{ $project->total_budget }},
                    @endforeach
                ],
                backgroundColor: [
                    '#6f42c1', '#0d6efd', '#198754', '#dc3545', '#fd7e14', '#20c997', '#ffc107'
                ],
                borderWidth: 1
            }]
        };
        new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Program Budget Distribution'
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

@extends('layouts.app')
@section('title', 'Applicant Reports')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-left">
                    <h5 class="m-b-10">Applicant Reports & Charts</h5>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ request('country') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sub-Region</label>
                            <input type="text" name="sub_region" class="form-control"
                                value="{{ request('sub_region') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-12 text-end">
                            <button class="btn btn-primary">Filter</button>
                            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Charts -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><strong>Applications Over Time</strong></div>
                        <div class="card-body">
                            <canvas id="applicationsChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><strong>Applications by Country</strong></div>
                        <div class="card-body">
                            <canvas id="countryPieChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><strong>Applications by Sub-region</strong></div>
                        <div class="card-body">
                            <canvas id="regionBarChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><strong>Covered Countries Histogram</strong></div>
                        <div class="card-body">
                            <canvas id="coveredCountriesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table View -->
            <div class="card">
                <div class="card-header"><strong>Filtered Applicants</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-hover" style="width:100%" id="proposalList1">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Think Tank</th>
                                <th>Country</th>
                                <th>Sub-Region</th>
                                <th>Consortium</th>
                                <th>Submitted On</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($applicants as $i => $a)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $a->think_tank_name }}</td>
                                    <td>{{ $a->country }}</td>
                                    <td>{{ $a->sub_region }}</td>
                                    <td>{{ $a->consortium_name ?? '-' }}</td>
                                    <td>{{ $a->created_at->format('d M, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No results found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Applications Over Time
        new Chart(document.getElementById('applicationsChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($applicationsByDate->pluck('app_date')) !!},
                datasets: [{
                    label: 'Applications',
                    data: {!! json_encode($applicationsByDate->pluck('count')) !!},
                    backgroundColor: 'rgba(0, 123, 255, 0.2)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    fill: true,
                    tension: 0.4
                }]
            }
        });

        // Applications by Country
        new Chart(document.getElementById('countryPieChart'), {
            type: 'pie',
            data: {
                labels: {!! json_encode($applicationsByCountry->pluck('country')) !!},
                datasets: [{
                    data: {!! json_encode($applicationsByCountry->pluck('count')) !!},
                    backgroundColor: ['#007bff', '#ffc107', '#28a745', '#dc3545', '#17a2b8', '#6f42c1'],
                }]
            }
        });

        // Sub-region Bar Chart
        new Chart(document.getElementById('regionBarChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($applicationsByRegion->pluck('sub_region')) !!},
                datasets: [{
                    label: 'Applicants',
                    data: {!! json_encode($applicationsByRegion->pluck('count')) !!},
                    backgroundColor: '#17a2b8'
                }]
            }
        });

        // Covered Countries Histogram
        new Chart(document.getElementById('coveredCountriesChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($coveredCountryCounts)) !!},
                datasets: [{
                    label: 'Covered Country Count',
                    data: {!! json_encode(array_values($coveredCountryCounts)) !!},
                    backgroundColor: '#6f42c1'
                }]
            }
        });
    </script>
@endsection

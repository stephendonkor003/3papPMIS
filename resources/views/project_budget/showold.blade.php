@extends('layouts.app')

@section('content')
    <style>
        .budget-report-modal .modal-dialog {
            max-width: 95% !important;
        }

        .budget-report-modal .modal-content {
            border-radius: 12px;
            border: 1px solid #dee2e6;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.15);
            background-color: #fff;
        }

        .budget-report-modal .modal-header {
            background: linear-gradient(90deg, #004085, #007bff);
            color: #fff;
            border-bottom: 3px solid #004085;
        }

        .budget-report-modal .modal-header h5 {
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .budget-report-modal .modal-body {
            background: #fafafa;
            padding: 1.5rem;
        }

        .budget-report-table {
            border-collapse: collapse;
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
        }

        .budget-report-table th {
            background-color: #004085;
            color: #fff;
            font-weight: 600;
            font-size: 13px;
            text-align: center;
            padding: 6px;
        }

        .budget-report-table td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 13px;
            text-align: center;
        }

        .budget-report-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .budget-report-table tr.main-project-row {
            background: #dce6f7;
            font-weight: bold;
        }

        .budget-report-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 15px;
        }

        .budget-report-actions button {
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 14px;
        }
    </style>

    @php
        $years = range($project->start_year, $project->end_year);
        $allocations = $project->yearly_allocations ?? [];
        $subActivities = $project->subActivities;
        $usedPerYear = [];

        foreach ($subActivities as $sub) {
            $usedPerYear[$sub->year] = ($usedPerYear[$sub->year] ?? 0) + $sub->budget_allocation;
        }
    @endphp

    <div class="nxl-container">
        <div class="page-header mb-4">
            <h4 class="fw-bold text-primary"><i class="bi bi-eye me-2"></i> Project Budgetary Breakdown</h4>
        </div>

        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-body bg-light">
                <h5 class="fw-bold text-dark">{{ $project->project_name }}</h5>
                <p class="mb-1"><strong>Project ID:</strong> {{ $project->project_id }}</p>
                <p class="mb-1"><strong>Total Budget:</strong> USD {{ number_format($project->total_budget, 2) }}</p>
                <p class="mb-1"><strong>Duration:</strong> {{ $project->start_year }} – {{ $project->end_year }}</p>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#fullReportModal">
                <i class="bi bi-file-earmark-text me-1"></i> View Full Project Report
            </button>
        </div>

        {{-- ===================== YEARLY BREAKDOWN ===================== --}}
        @foreach ($years as $index => $year)
            @php
                $percent = $allocations[$year] ?? 100 / count($years);
                $yearCap = ($project->total_budget * $percent) / 100;
                $used = $usedPerYear[$year] ?? 0;
                $remaining = $yearCap - $used;
                $usagePercent = $yearCap > 0 ? ($used / $yearCap) * 100 : 0;
                $yearSubs = $subActivities->where('year', $year);
            @endphp

            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-primary text-white">
                    <strong>Year {{ $index + 1 }} ({{ $year }})</strong> — Allocation: {{ $percent }}%
                    (USD {{ number_format($yearCap, 2) }})
                </div>
                <div class="card-body">
                    <div class="progress mb-3" style="height: 18px;">
                        <div class="progress-bar {{ $usagePercent >= 100 ? 'bg-danger' : ($usagePercent >= 80 ? 'bg-warning' : 'bg-success') }}"
                            role="progressbar" style="width: {{ min($usagePercent, 100) }}%">
                            {{ number_format($usagePercent, 1) }}%
                        </div>
                    </div>

                    <table class="table table-sm table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Sub-Activity ID</th>
                                <th>Name</th>
                                <th>Budget (USD)</th>
                                <th>% of Year</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($yearSubs as $i => $sub)
                                @php
                                    $percentOfYear = $yearCap > 0 ? ($sub->budget_allocation / $yearCap) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $sub->sub_activity_id }}</td>
                                    <td>{{ $sub->sub_activity_name }}</td>
                                    <td>{{ number_format($sub->budget_allocation, 2) }}</td>
                                    <td>{{ number_format($percentOfYear, 2) }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted">No sub-activities for this year</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    <!-- ===================== FULL PROJECT REPORT MODAL ===================== -->
    <div class="modal fade budget-report-modal" id="fullReportModal" tabindex="-1" aria-labelledby="fullReportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-bar-chart-line me-2"></i> Full Project Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="budget-report-actions">
                        <button class="btn btn-success btn-sm" onclick="exportFullReportExcel()">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="printFullReport()">
                            <i class="bi bi-file-earmark-pdf"></i> Export PDF
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="budget-report-table" id="fullReportTable">
                            <thead>
                                <tr>
                                    <th>Program_ID</th>
                                    <th>Project_name</th>
                                    <th>Budget</th>
                                    <th>CFF ({{ $years[0] }})</th>
                                    @foreach (array_slice($years, 1) as $y)
                                        <th>Year {{ $y }}</th>
                                    @endforeach
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="main-project-row">
                                    <td>{{ $project->project_id }}</td>
                                    <td>{{ $project->project_name }}</td>
                                    <td>{{ number_format($project->total_budget, 2) }}</td>
                                    @foreach ($years as $y)
                                        <td>
                                            {{ number_format((($allocations[$y] ?? 100 / count($years)) * $project->total_budget) / 100, 2) }}
                                        </td>
                                    @endforeach
                                    <td>{{ number_format($project->total_budget, 2) }}</td>
                                </tr>

                                @foreach ($subActivities as $sub)
                                    <tr>
                                        <td>{{ $sub->sub_activity_id }}</td>
                                        <td>{{ $sub->sub_activity_name }}</td>
                                        <td>{{ number_format($sub->budget_allocation, 2) }}</td>
                                        @foreach ($years as $y)
                                            @if ($loop->first)
                                                <td>{{ number_format($sub->budget_allocation, 2) }}</td>
                                                {{-- CFF year --}}
                                            @else
                                                <td>0.00</td>
                                            @endif
                                        @endforeach
                                        <td>{{ number_format($sub->budget_allocation, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportFullReportExcel() {
            const table = document.getElementById('fullReportTable');
            const html = `<html><head><meta charset="UTF-8"></head><body>${table.outerHTML}</body></html>`;
            const blob = new Blob([html], {
                type: 'application/vnd.ms-excel'
            });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'Full_Project_Report.xls';
            a.click();
        }

        function printFullReport() {
            const printContent = document.getElementById('fullReportTable').outerHTML;
            const newWin = window.open('');
            newWin.document.write(`<html><head><title>Project Report</title></head><body>${printContent}</body></html>`);
            newWin.document.close();
            newWin.print();
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection

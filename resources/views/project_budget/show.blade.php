@extends('layouts.app')

@section('content')
    <style>
        /* ===================== FULL REPORT MODAL CUSTOM STYLES ===================== */

        .budget-report-modal .modal-dialog {
            max-width: 95% !important;
        }

        .budget-report-modal .modal-content {
            border-radius: 12px;
            border: 1px solid #dee2e6;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.15);
            background-color: #fdfdfd;
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
            padding: 1.5rem;
            background-color: #fafafa;
        }

        .budget-report-modal .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        /* Export buttons */
        .budget-report-actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
            gap: 10px;
        }

        .budget-report-actions button {
            border-radius: 30px;
            padding: 6px 16px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            transition: all 0.3s ease-in-out;
        }

        .budget-report-actions .btn-success {
            background-color: #198754;
            border: none;
        }

        .budget-report-actions .btn-success:hover {
            background-color: #146c43;
        }

        .budget-report-actions .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .budget-report-actions .btn-danger:hover {
            background-color: #b02a37;
        }

        /* Table styling */
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
            text-transform: uppercase;
            font-size: 13px;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .budget-report-table td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 13px;
            text-align: center;
            vertical-align: middle;
        }

        .budget-report-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .budget-report-table tr:hover {
            background-color: #e6f0ff;
        }

        .budget-report-table tr.main-project-row {
            background: #dce6f7;
            font-weight: bold;
        }

        /* Scrollbar for long reports */
        .budget-report-modal .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .budget-report-modal .modal-body::-webkit-scrollbar-thumb {
            background-color: #007bff;
            border-radius: 4px;
        }
    </style>
    <!-- ===================== FULL PROJECT REPORT MODAL ===================== -->
    @php
        // 🔁 Ensure modal can access all required data
        $years = range($project->start_year, $project->end_year);
        $allocations = $project->yearly_allocations ?? [];
        $subActivities = $project->subActivities ?? collect();
    @endphp

    <!-- ===================== FULL PROJECT REPORT MODAL ===================== -->
    <div class="modal fade budget-report-modal" id="fullReportModal" tabindex="-1" aria-labelledby="fullReportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-bar-chart-line me-2"></i> Full Project Report
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body bg-light">
                    <!-- ===== Export Buttons ===== -->
                    <div class="d-flex justify-content-end gap-2 mb-3">
                        <button class="btn btn-success btn-sm" onclick="exportFullReportExcel()">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="printFullReport()">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                        </button>
                    </div>

                    <!-- ===== Report Table ===== -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle text-center" id="fullReportTable">
                            <thead class="table-success">
                                <tr>
                                    <th>Program_ID</th>
                                    <th>Project / Sub-Activity</th>
                                    <th>Budget (USD)</th>
                                    @foreach ($years as $y)
                                        <th>Year {{ $y }}</th>
                                    @endforeach
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- ===== Main Project Row ===== --}}
                                <tr class="fw-bold bg-primary bg-opacity-10">
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

                                {{-- ===== Sub-Activities by Year ===== --}}
                                @foreach ($years as $index => $year)
                                    @php
                                        $percent = $allocations[$year] ?? 100 / count($years);
                                        $yearCap = ($project->total_budget * $percent) / 100;
                                        $yearSubs = $subActivities->where('year', $year);
                                    @endphp

                                    <tr class="table-secondary">
                                        <td colspan="{{ count($years) + 3 }}" class="text-start fw-bold">
                                            YEAR {{ $index + 1 }} — ({{ $year }}) — Allocation:
                                            {{ number_format($percent, 2) }}% (USD {{ number_format($yearCap, 2) }})
                                        </td>
                                    </tr>

                                    @forelse ($yearSubs as $sub)
                                        <tr>
                                            <td>{{ $sub->sub_activity_id }}</td>
                                            <td class="text-start ps-3">{{ $sub->sub_activity_name }}</td>
                                            <td>{{ number_format($sub->budget_allocation, 2) }}</td>
                                            @foreach ($years as $y)
                                                @if ($y == $year)
                                                    <td>{{ number_format($sub->budget_allocation, 2) }}</td>
                                                @else
                                                    <td>0.00</td>
                                                @endif
                                            @endforeach
                                            <td>{{ number_format($sub->budget_allocation, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ count($years) + 4 }}" class="text-muted text-center">
                                                No sub-activities recorded for {{ $year }}.
                                            </td>
                                        </tr>
                                    @endforelse
                                @endforeach

                                {{-- ===== Grand Total Row ===== --}}
                                @php
                                    $grandTotal = $subActivities->sum('budget_allocation');
                                @endphp
                                <tr class="fw-bold bg-success bg-opacity-10">
                                    <td colspan="2" class="text-end">Grand Total</td>
                                    <td>{{ number_format($grandTotal, 2) }}</td>
                                    @foreach ($years as $y)
                                        @php
                                            $sumPerYear = $subActivities->where('year', $y)->sum('budget_allocation');
                                        @endphp
                                        <td>{{ number_format($sumPerYear, 2) }}</td>
                                    @endforeach
                                    <td>{{ number_format($grandTotal, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Export & Print Scripts ===== -->



    <div class="nxl-container">
        <div class="page-header mb-4">
            <h4 class="fw-bold text-primary"><i class="bi bi-eye me-2"></i>Project Budgetary Breakdown</h4>
        </div>

        {{-- ===================== PROJECT DETAILS ===================== --}}
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-body bg-light">
                <h5 class="fw-bold text-dark">{{ $project->project_name }}</h5>
                <p class="mb-1"><strong>Project ID:</strong> {{ $project->project_id }}</p>
                <p class="mb-1"><strong>Total Budget:</strong> USD {{ number_format($project->total_budget, 2) }}</p>
                <p class="mb-1"><strong>Duration:</strong> {{ $project->start_year }} – {{ $project->end_year }}</p>

                @php
                    $years = range($project->start_year, $project->end_year);
                    $allocations = $project->yearly_allocations ?? [];
                    $subActivities = $project->subActivities;
                    $usedPerYear = [];

                    foreach ($subActivities as $sub) {
                        $usedPerYear[$sub->year] = ($usedPerYear[$sub->year] ?? 0) + $sub->budget_allocation;
                    }
                @endphp
            </div>

        </div>
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#fullReportModal"
                onclick="populateFullReport(@json($project), @json($project->subActivities), @json($allocations))">

                <i class="bi bi-file-earmark-text me-1"></i> View Full Project Report
            </button>
        </div>
        <!-- ==================== FULL REPORT MODAL ==================== -->
        <!-- ==================== FULL PROJECT REPORT MODAL ==================== -->




        {{-- ===================== YEARLY GROUPS ===================== --}}
        @foreach ($years as $index => $year)
            @php
                $percent = $allocations[$year] ?? 100 / count($years);
                $yearCap = ($project->total_budget * $percent) / 100;
                $used = $usedPerYear[$year] ?? 0;
                $remaining = $yearCap - $used;
                $usagePercent = $yearCap > 0 ? ($used / $yearCap) * 100 : 0;
                $yearId = "yearTable-$year";
            @endphp

            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <div>
                        <span>Year {{ $index + 1 }} ({{ $year }}) — Allocation: {{ $percent }}% (USD
                            {{ number_format($yearCap, 2) }})</span>
                        <span class="badge bg-light text-dark ms-2">
                            Used: USD {{ number_format($used, 2) }} | Remaining: USD
                            {{ number_format(max($remaining, 0), 2) }}
                        </span>
                    </div>
                    <button class="btn btn-success btn-sm"
                        onclick="exportYear('{{ $yearId }}', '{{ $project->project_id }}', '{{ $year }}')">
                        <i class="bi bi-download me-1"></i> Export Year {{ $year }}
                    </button>
                </div>

                <div class="card-body">
                    {{-- Progress bar --}}
                    <div class="progress mb-3" style="height: 18px;">
                        <div class="progress-bar {{ $usagePercent >= 100 ? 'bg-danger' : ($usagePercent >= 80 ? 'bg-warning' : 'bg-success') }}"
                            role="progressbar" style="width: {{ min($usagePercent, 100) }}%">
                            {{ number_format($usagePercent, 1) }}%
                        </div>
                    </div>

                    {{-- ====== ADD SUB-ACTIVITY FORM ====== --}}
                    <form action="{{ route('project_budget.store_sub_activity', $project->id) }}" method="POST"
                        class="mb-4"
                        onsubmit="return checkYearLimit({{ $yearCap }}, {{ $used }}, {{ $year }})">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Sub-Activity Name</label>
                                <input type="text" name="sub_activity_name" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Input Type</label>
                                <select class="form-select" id="type-{{ $year }}"
                                    onchange="toggleInputType('{{ $year }}')">
                                    <option value="amount">Enter Amount</option>
                                    <option value="percent">Enter Percentage</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Budget (USD)</label>
                                <input type="number" name="budget_allocation" step="0.01" class="form-control"
                                    id="amount-{{ $year }}" required
                                    oninput="checkLiveLimit({{ $yearCap }}, {{ $used }}, {{ $year }})">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Percentage (%)</label>
                                <input type="number" step="0.01" class="form-control d-none"
                                    id="percent-{{ $year }}"
                                    oninput="convertToAmount('{{ $year }}', {{ $yearCap }})">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- ====== TABLE OF SUB-ACTIVITIES ====== --}}
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle text-center" id="{{ $yearId }}">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Sub-Activity ID</th>
                                    <th>Activity Name</th>
                                    <th>Budget (USD)</th>
                                    <th>Percent of Year</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $yearSubs = $subActivities->where('year', $year);
                                @endphp

                                @forelse ($yearSubs as $i => $sub)
                                    @php
                                        $percentOfYear = $yearCap > 0 ? ($sub->budget_allocation / $yearCap) * 100 : 0;
                                    @endphp
                                    <tr id="row-{{ $sub->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $sub->sub_activity_id }}</td>
                                        <td>{{ $sub->sub_activity_name }}</td>
                                        <td class="text-end">{{ number_format($sub->budget_allocation, 2) }}</td>
                                        <td class="text-end">{{ number_format($percentOfYear, 2) }}%</td>
                                        <td>
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="confirmRemove('{{ route('project_budget.destroy_sub_activity', $sub->id) }}', '{{ $sub->sub_activity_name }}', '{{ $sub->id }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-muted">No sub-activities for this year</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ===================== SCRIPTS ===================== --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function toggleInputType(year) {
            const type = document.getElementById(`type-${year}`).value;
            const amount = document.getElementById(`amount-${year}`);
            const percent = document.getElementById(`percent-${year}`);
            if (type === 'percent') {
                amount.classList.add('d-none');
                percent.classList.remove('d-none');
                amount.removeAttribute('required');
            } else {
                percent.classList.add('d-none');
                amount.classList.remove('d-none');
                amount.setAttribute('required', true);
            }
        }

        function convertToAmount(year, cap) {
            const percentInput = document.getElementById(`percent-${year}`);
            const amountInput = document.getElementById(`amount-${year}`);
            const percent = parseFloat(percentInput.value) || 0;
            const amount = (cap * percent / 100).toFixed(2);
            amountInput.value = amount;
        }

        function checkLiveLimit(cap, used, year) {
            const input = document.getElementById(`amount-${year}`);
            const newValue = parseFloat(input.value) || 0;
            const total = used + newValue;
            if (total > cap) {
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        }

        function checkYearLimit(cap, used, year) {
            const amount = parseFloat(document.getElementById(`amount-${year}`).value) || 0;
            if ((used + amount) > cap) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Budget Limit Exceeded',
                    text: `You are exceeding the allocated Year ${year} budget limit of USD ${cap.toLocaleString()}.`,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                });
                return false;
            }
            return true;
        }

        // ======= EXPORT YEAR DATA =======
        // ======= EXPORT YEAR DATA (FINAL FIXED VERSION) =======
        function exportYear(tableId, projectId, year, yearCap = 0, used = 0, remaining = 0, percent = 0) {
            const table = document.getElementById(tableId);
            if (!table) {
                Swal.fire('Error', 'No table found for this year.', 'error');
                return;
            }

            // 🧾 Clean Excel-compatible HTML document with summary
            const html = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office"
              xmlns:x="urn:schemas-microsoft-com:office:excel"
              xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="UTF-8">
            @verbatim
            <!--[if gte mso 9]><xml>
            <x:ExcelWorkbook>
                <x:ExcelWorksheets>
                    <x:ExcelWorksheet>
                        <x:Name>Year ${year}</x:Name>
                        <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
                    </x:ExcelWorksheet>
                </x:ExcelWorksheets>
            </x:ExcelWorkbook>
            </xml><![endif]-->
            @endverbatim
            <style>
                table { border-collapse: collapse; width: 100%; font-family: Arial; }
                th, td { border: 1px solid #000; padding: 4px; text-align: center; }
                caption { font-weight: bold; margin-bottom: 10px; }
                .summary-table th { background-color: #d9ead3; }
            </style>
        </head>
        <body>
            <h3>Project ID: ${projectId}</h3>
            <h4>Year ${year} Summary</h4>
            <table class="summary-table" border="1">
                <tr>
                    <th>Allocation %</th>
                    <th>Allocated Budget (USD)</th>
                    <th>Used (USD)</th>
                    <th>Remaining (USD)</th>
                </tr>
                <tr>
                    <td>${percent.toFixed(2)}%</td>
                    <td>${yearCap.toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                    <td>${used.toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                    <td>${remaining.toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                </tr>
            </table>
            <br>
            ${table.outerHTML}
        </body>
        </html>
    `;

            // 💾 Build Blob for Excel
            const blob = new Blob([html], {
                type: 'application/vnd.ms-excel;charset=utf-8;'
            });

            // 📦 Create and trigger download
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${projectId}_Year_${year}_Budget.xls`; // .xls for compatibility
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }


        // ======= REMOVE SUB-ACTIVITY =======
        function confirmRemove(url, name, rowId) {
            Swal.fire({
                title: 'Are you sure?',
                html: `You are about to remove <strong>${name}</strong>.<br>This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove it',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const row = document.getElementById(`row-${rowId}`);
                                if (row) {
                                    row.style.transition = 'opacity 0.5s';
                                    row.style.opacity = '0';
                                    setTimeout(() => row.remove(), 500);
                                }
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Removed',
                                    text: data.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire('Error', data.message ||
                                    'Something went wrong while removing this item.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            Swal.fire('Error', 'Network error. Please try again.', 'error');
                        });
                }
            });
        }


        // ======= GENERATE FULL PROJECT REPORT (DYNAMIC) =======
        // ====== POPULATE FULL PROJECT REPORT MODAL ======
        // ======== POPULATE FULL PROJECT REPORT (AUTO YEARS) ========
    </script>

    <script>
        function populateFullReport(project, subActivities, allocations) {
            const body = document.getElementById('fullReportBody');
            const head = document.getElementById('fullReportHead');

            if (!project) {
                Swal.fire('Error', 'No project data available', 'error');
                return;
            }

            // Clear previous table
            body.innerHTML = '';
            head.innerHTML = '';

            // === Define project years ===
            const years = [];
            const start = parseInt(project.start_year);
            const end = parseInt(project.end_year);
            for (let y = start; y <= end; y++) years.push(y);

            // === Table Header ===
            let headerRow = `
        <tr>
            <th>Program_ID</th>
            <th>Project_name</th>
            <th>Budget</th>
            <th>CFF (${years[0]})</th>
    `;
            years.slice(1).forEach(y => headerRow += `<th>Year_${y}</th>`);
            headerRow += `<th>Total</th></tr>`;
            head.innerHTML = headerRow;

            const fmt = n => (n ? Number(n).toLocaleString(undefined, {
                minimumFractionDigits: 2
            }) : '0.00');

            // === MAIN PROJECT ROW ===
            let projectRow = `
        <tr class="main-project-row">
            <td>${project.project_id}</td>
            <td>${project.project_name}</td>
            <td>${fmt(project.total_budget)}</td>
    `;
            years.forEach(y => {
                const alloc = (allocations && allocations[y]) ? (project.total_budget * allocations[y] / 100) : 0;
                projectRow += `<td>${fmt(alloc)}</td>`;
            });
            projectRow += `<td>${fmt(project.total_budget)}</td></tr>`;
            body.insertAdjacentHTML('beforeend', projectRow);

            // === SUB-ACTIVITIES ===
            if (!subActivities || subActivities.length === 0) {
                body.insertAdjacentHTML('beforeend',
                    `<tr><td colspan="${years.length + 4}" class="text-muted">No sub-activities found</td></tr>`);
                return;
            }

            // Group by Year
            years.forEach((y, idx) => {
                const subs = subActivities.filter(s => s.year == y);
                if (subs.length > 0) {
                    subs.forEach(sub => {
                        const cff = (idx === 0) ? sub.budget_allocation : 0; // CFF is always first year
                        const row = `
                    <tr>
                        <td>${sub.sub_activity_id}</td>
                        <td>${sub.sub_activity_name}</td>
                        <td>${fmt(sub.budget_allocation)}</td>
                        <td>${fmt(cff)}</td>
                        ${years.slice(1).map(yr =>
                            `<td>${(yr === y && idx !== 0) ? fmt(sub.budget_allocation) : '0.00'}</td>`).join('')}
                        <td>${fmt(sub.budget_allocation)}</td>
                    </tr>
                `;
                        body.insertAdjacentHTML('beforeend', row);
                    });
                }
            });
        }
    </script>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection

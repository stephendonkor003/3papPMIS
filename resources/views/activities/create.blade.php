@extends('layouts.app')

@section('title', 'Create Activity')

@section('content')

    <style>
        .ai-helper {
            background: #eef7ff;
            border-left: 4px solid #0d6efd;
            padding: 12px;
            border-radius: 8px;
            animation: pulseGlow 2s infinite;
            font-size: 14px;
        }

        @keyframes pulseGlow {
            0% {
                box-shadow: 0 0 0 rgba(13, 110, 253, 0.2);
            }

            50% {
                box-shadow: 0 0 10px rgba(13, 110, 253, 0.4);
            }

            100% {
                box-shadow: 0 0 0 rgba(13, 110, 253, 0.2);
            }
        }

        .mode-active {
            background: #0d6efd !important;
            color: #fff !important;
            border-color: #0d6efd !important;
        }

        .percentage-column {
            display: none;
        }

        .auto-fill {
            background: #e3ffe8 !important;
            transition: 0.4s ease;
        }

        .info-box {
            background: #f8f9ff;
            border-left: 4px solid #6f42c1;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .comparison-positive {
            color: #198754;
            font-weight: bold;
        }

        .comparison-negative {
            color: #dc3545;
            font-weight: bold;
        }
    </style>

    <main class="nxl-container fade-in">
        <div class="nxl-content">

            <!-- PAGE HEADER -->
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Add New Activity</h4>
                    <div class="text-muted small">
                        Project <strong>{{ $project->project_id }}</strong> — {{ $project->name }} <br>
                        Duration: {{ $project->start_year }} - {{ $project->end_year }} ({{ $project->total_years }} years)
                    </div>
                </div>

                <a href="{{ route('budget.projects.show', $project) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left-circle me-1"></i> Back to Project
                </a>

            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="m-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- PROJECT SUMMARY -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-3">Project Information Summary</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted fw-semibold">Program</small>
                            <div class="fw-bold">{{ $project->program->name ?? 'N/A' }}</div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted fw-semibold">Sector</small>
                            <div class="fw-bold">{{ $project->sector->name ?? 'N/A' }}</div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted fw-semibold">Project Name</small>
                            <div class="fw-bold">{{ $project->name }}</div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted fw-semibold">Total Project Budget</small>
                            <div class="fw-bold">{{ number_format($project->total_budget, 2) }} {{ $project->currency }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted fw-semibold">Project Duration</small>
                            <div class="fw-bold">
                                {{ $project->start_year }} - {{ $project->end_year }} ({{ $project->total_years }} years)
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted fw-semibold">Project Code</small>
                            <div class="fw-bold">{{ $project->project_id }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- REAL-TIME BUDGET COMPARISON -->
            <div class="info-box">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Total Project Budget:</strong><br>
                        {{ number_format($project->total_budget, 2) }} {{ $project->currency }}
                    </div>

                    <div class="col-md-3">
                        <strong>Activity Total Allocation:</strong><br>
                        <span id="activityTotal" class="fw-bold text-primary">0.00</span> {{ $project->currency }}
                    </div>

                    <div class="col-md-3">
                        <strong>Remaining Project Balance:</strong><br>
                        <span id="remainingBalance" class="fw-bold comparison-positive">
                            {{ number_format($project->total_budget, 2) }}
                        </span> {{ $project->currency }}
                    </div>

                    <div class="col-md-3">
                        <strong>% of Project Used:</strong><br>
                        <span id="percentageUsed" class="fw-bold">0%</span>
                    </div>
                </div>
            </div>


            <!-- FORM CARD -->
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <div class="ai-helper mb-3">
                        🤖 <strong>AI Assist:</strong> Switch between amount mode and percentage mode. You can also evenly
                        distribute the allocation across all years. Real-time budget comparison helps ensure good planning.
                    </div>

                    <form action="{{ route('budget.activities.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $project->id }}">

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Activity Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                    placeholder="Enter activity name">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Description</label>
                                <input type="text" name="description" class="form-control" placeholder="(Optional)">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Expected Outcome Type <span class="text-danger">*</span></label>
                                <select name="expected_outcome_type" id="expectedOutcomeType" class="form-select" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="percentage" {{ old('expected_outcome_type') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                    <option value="text" {{ old('expected_outcome_type') === 'text' ? 'selected' : '' }}>Text</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Expected Outcome <span class="text-danger">*</span></label>
                                <div id="expectedOutcomePercentage" style="display:none;">
                                    <div class="input-group">
                                        <input type="number" name="expected_outcome_percentage" class="form-control" min="0" max="100" step="0.01"
                                            value="{{ old('expected_outcome_percentage') }}">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div id="expectedOutcomeText" style="display:none;">
                                    <textarea name="expected_outcome_text" class="form-control" rows="2"
                                        placeholder="Describe the expected outcome">{{ old('expected_outcome_text') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- MODE SWITCH -->
                        <div class="d-flex gap-2 mb-3">
                            <button type="button" id="modeAmount" class="btn btn-primary btn-sm mode-active">Amount
                                Mode</button>
                            <button type="button" id="modePercentage" class="btn btn-outline-primary btn-sm">Percentage
                                Mode</button>
                            <button type="button" id="autoDistribute" class="btn btn-outline-success btn-sm">Auto
                                Distribute Evenly</button>

                            <div class="ms-3 text-muted small" id="helperTip">
                                💡 Enter yearly amounts or switch to percentage mode.
                            </div>
                        </div>

                        <!-- ALLOCATION TABLE -->
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:15%">Year</th>
                                    <th>Amount ({{ $project->currency }})</th>
                                    <th class="percentage-col">Percentage (%)</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($project->years() as $year)
                                    <tr>
                                        <td class="fw-semibold">{{ $year }}</td>

                                        <!-- ALWAYS SUBMITS VALUES -->
                                        <td>
                                            <input type="number" name="allocations[{{ $year }}]"
                                                class="form-control amount-input" value="0" step="0.01"
                                                min="0" autocomplete="off">
                                        </td>

                                        <td class="percentage-col">
                                            <input type="number" name="percentages[{{ $year }}]"
                                                class="form-control percentage-input" value="0" step="0.01"
                                                min="0" max="100" autocomplete="off">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>


                        <div class="text-end fw-bold">
                            Total: <span id="totalAllocation" class="text-primary">0.00</span> {{ $project->currency }}
                        </div>

                        <div class="mt-4 text-end">
                            <button class="btn btn-success px-4">
                                <i class="bi bi-check-circle me-1"></i> Save Activity
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </main>


    <!-- JS LOGIC -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            const totalBudget = {{ $project->total_budget }};
            const amountInputs = document.querySelectorAll(".amount-input");
            const pctInputs = document.querySelectorAll(".percentage-input");

            const pctColumns = document.querySelectorAll(".percentage-col");
            const helperTip = document.getElementById("helperTip");

            const totalAllocation = document.getElementById("totalAllocation");
            const activityTotal = document.getElementById("activityTotal");
            const remainingBalance = document.getElementById("remainingBalance");
            const percentageUsed = document.getElementById("percentageUsed");

            let mode = "amount"; // default mode

            function updateSummary() {
                let total = 0;
                amountInputs.forEach(input => {
                    total += parseFloat(input.value) || 0;
                });

                totalAllocation.textContent = total.toFixed(2);
                activityTotal.textContent = total.toFixed(2);

                let remaining = totalBudget - total;
                remainingBalance.textContent = remaining.toFixed(2);

                percentageUsed.textContent = ((total / totalBudget) * 100).toFixed(1) + "%";

                if (remaining >= 0) {
                    remainingBalance.classList.add("text-success");
                    remainingBalance.classList.remove("text-danger");
                } else {
                    remainingBalance.classList.add("text-danger");
                    remainingBalance.classList.remove("text-success");
                }
            }

            // Switch to amount mode
            document.getElementById("modeAmount").addEventListener("click", () => {
                mode = "amount";

                pctColumns.forEach(col => col.style.display = "none");

                amountInputs.forEach(input => input.readOnly = false);
                pctInputs.forEach(input => input.readOnly = true);

                helperTip.textContent = "💡 Enter yearly amounts or use auto distribute.";

                document.getElementById("modeAmount").classList.add("mode-active");
                document.getElementById("modePercentage").classList.remove("mode-active");
            });

            // Switch to percentage mode
            document.getElementById("modePercentage").addEventListener("click", () => {
                mode = "percentage";

                pctColumns.forEach(col => col.style.display = "table-cell");

                amountInputs.forEach(input => input.readOnly = true);
                pctInputs.forEach(input => input.readOnly = false);

                helperTip.textContent = "💡 Enter percentages. System auto-calculates the amounts.";

                document.getElementById("modePercentage").classList.add("mode-active");
                document.getElementById("modeAmount").classList.remove("mode-active");
            });

            // When percentage is typed → auto calculate amount
            pctInputs.forEach((input, index) => {
                input.addEventListener("input", () => {
                    if (mode !== "percentage") return;

                    let pct = parseFloat(input.value) || 0;
                    let amount = (pct / 100) * totalBudget;

                    amountInputs[index].value = amount.toFixed(2);
                    updateSummary();
                });
            });

            // Amount input manually changed
            amountInputs.forEach(input => {
                input.addEventListener("input", () => {
                    if (mode === "amount") {
                        updateSummary();
                    }
                });
            });

            // Auto distribute evenly
            document.getElementById("autoDistribute").addEventListener("click", () => {
                let evenAmount = totalBudget / amountInputs.length;

                amountInputs.forEach(input => {
                    input.value = evenAmount.toFixed(2);

                    input.closest("tr").classList.add("auto-fill");
                    setTimeout(() => input.closest("tr").classList.remove("auto-fill"), 700);
                });

                updateSummary();
                helperTip.textContent = "✨ Auto distribution applied evenly across all years.";
            });

            updateSummary();
        });
    </script>

    <script>
        const expectedOutcomeType = document.getElementById("expectedOutcomeType");
        const expectedOutcomePercentage = document.getElementById("expectedOutcomePercentage");
        const expectedOutcomeText = document.getElementById("expectedOutcomeText");

        function toggleExpectedOutcome() {
            const type = expectedOutcomeType.value;
            expectedOutcomePercentage.style.display = type === "percentage" ? "block" : "none";
            expectedOutcomeText.style.display = type === "text" ? "block" : "none";
        }

        expectedOutcomeType.addEventListener("change", toggleExpectedOutcome);
        toggleExpectedOutcome();
    </script>

@endsection

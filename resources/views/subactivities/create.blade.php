@extends('layouts.app')

@section('title', 'Create Sub-Activity')

@section('content')

    <style>
        .fade-in {
            animation: fadeIn .3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
            }
        }

        .mode-btn {
            cursor: pointer;
        }

        .active-mode {
            font-weight: bold;
            color: #0d6efd;
        }

        .highlight-row {
            background: #eef6ff !important;
        }

        .total-warning {
            color: #dc3545;
            font-weight: bold;
        }

        .total-ok {
            color: #198754;
            font-weight: bold;
        }
    </style>

    <main class="nxl-container fade-in">
        <div class="nxl-content">

            <!-- HEADER -->
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Create Sub-Activity</h4>
                    <p class="text-muted small">Under Activity: <strong>{{ $activity->name }}</strong></p>
                </div>

                <a href="{{ route('budget.activities.show', $activity->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left-circle"></i> Back
                </a>
            </div>

            <!-- SUMMARY -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-3">Activity Summary</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted">Program</small>
                            <div class="fw-bold">{{ $activity->project->program->name ?? 'N/A' }}</div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Project</small>
                            <div class="fw-bold">{{ $activity->project->project_id }} — {{ $activity->project->name }}</div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Duration</small>
                            <div class="fw-bold">
                                {{ $activity->project->start_year }}–{{ $activity->project->end_year }}
                                ({{ count($activity->years()) }} years)
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Activity Budget</small>
                            <div class="fw-bold">
                                {{ number_format($activity->totalAllocation(), 2) }}
                                {{ $activity->project->currency }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM CARD -->
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="m-0">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('budget.subactivities.store') }}" method="POST">
                        @csrf

                        <input type="hidden" name="activity_id" value="{{ $activity->id }}">
                        <input type="hidden" id="maxBudget" value="{{ $activity->totalAllocation() }}">

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="fw-semibold">Sub-Activity Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="fw-semibold">Description</label>
                                <input type="text" name="description" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="fw-semibold">Expected Outcome Type *</label>
                                <select name="expected_outcome_type" id="expectedOutcomeType" class="form-select" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="percentage" {{ old('expected_outcome_type') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                    <option value="text" {{ old('expected_outcome_type') === 'text' ? 'selected' : '' }}>Text</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="fw-semibold">Expected Outcome *</label>
                                <div id="expectedOutcomePercentage" style="display:none;">
                                    <div class="input-group">
                                        <input type="number" name="expected_outcome_percentage" class="form-control" min="0" max="100" step="0.01"
                                            value="{{ old('expected_outcome_percentage') }}">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div id="expectedOutcomeText" style="display:none;">
                                    <textarea name="expected_outcome_text" class="form-control" rows="2">{{ old('expected_outcome_text') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- MODE SWITCH -->
                        <div class="mb-3">
                            <span class="mode-btn active-mode" id="amountModeBtn">Amount Mode</span> |
                            <span class="mode-btn" id="percentModeBtn">Percentage Mode</span>
                        </div>

                        <!-- TABLE -->
                        <table class="table table-bordered" id="allocationTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Year</th>
                                    <th class="amount-col">Amount ({{ $activity->project->currency }})</th>
                                    <th class="percent-col" style="display:none;">Percentage (%)</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($activity->years() as $year)
                                    <tr data-year="{{ $year }}">
                                        <td class="fw-semibold">{{ $year }}</td>

                                        <td class="amount-col">
                                            <input type="number" name="allocations[{{ $year }}]"
                                                class="form-control amount-input" min="0" step="0.01"
                                                value="0">
                                        </td>

                                        <td class="percent-col" style="display:none;">
                                            <input type="number" class="form-control percent-input" min="0"
                                                max="100" value="0">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>

                        <!-- TOTAL -->
                        <div class="text-end mt-3">
                            <strong>Total:</strong>
                            <span id="totalDisplay" class="total-ok">0.00</span>
                            {{ $activity->project->currency }}
                        </div>

                        <!-- SUBMIT -->
                        <div class="mt-4 d-flex justify-content-end">
                            <button class="btn btn-success px-4">
                                <i class="bi bi-check-circle"></i> Save
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </main>

    <script>
        /* GLOBALS */
        let maxBudget = parseFloat(document.getElementById("maxBudget").value);
        const totalDisplay = document.getElementById("totalDisplay");

        /* MODE SWITCH */
        const amountModeBtn = document.getElementById("amountModeBtn");
        const percentModeBtn = document.getElementById("percentModeBtn");

        function switchToAmountMode() {
            document.querySelectorAll(".amount-col").forEach(col => col.style.display = "");
            document.querySelectorAll(".percent-col").forEach(col => col.style.display = "none");

            amountModeBtn.classList.add("active-mode");
            percentModeBtn.classList.remove("active-mode");

            updateTotal();
        }

        function switchToPercentMode() {
            document.querySelectorAll(".amount-col").forEach(col => col.style.display = "none");
            document.querySelectorAll(".percent-col").forEach(col => col.style.display = "");

            percentModeBtn.classList.add("active-mode");
            amountModeBtn.classList.remove("active-mode");

            updateByPercentage();
        }

        amountModeBtn.onclick = switchToAmountMode;
        percentModeBtn.onclick = switchToPercentMode;

        /* TOTAL CALCULATION */
        function updateTotal() {
            let total = 0;

            document.querySelectorAll(".amount-input").forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            totalDisplay.textContent = total.toFixed(2);

            if (total > maxBudget) {
                totalDisplay.classList.add("total-warning");
                totalDisplay.classList.remove("total-ok");
            } else {
                totalDisplay.classList.add("total-ok");
                totalDisplay.classList.remove("total-warning");
            }
        }

        document.querySelectorAll(".amount-input").forEach(input => {
            input.addEventListener("input", () => {
                input.closest("tr").classList.add("highlight-row");
                updateTotal();
            });
        });

        /* PERCENTAGE CALCULATION */
        function updateByPercentage() {
            let totalPercent = 0;

            document.querySelectorAll(".percent-input").forEach(input => {
                totalPercent += parseFloat(input.value) || 0;
            });

            if (totalPercent === 0) {
                totalDisplay.textContent = "0.00";
                return;
            }

            document.querySelectorAll("tr[data-year]").forEach(row => {
                let pct = parseFloat(row.querySelector(".percent-input").value) || 0;
                let amount = (pct / totalPercent) * maxBudget;
                row.querySelector(".amount-input").value = amount.toFixed(2);
            });

            updateTotal();
        }

        document.querySelectorAll(".percent-input").forEach(input => {
            input.addEventListener("input", updateByPercentage);
        });

        updateTotal();
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

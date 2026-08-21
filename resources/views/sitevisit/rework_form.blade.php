@extends('layouts.app')
@section('title', 'Rework Site Visit Evaluation')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">

            <!-- ===== Header ===== -->
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 text-warning fw-bold">
                        <i class="bi bi-arrow-repeat me-2"></i>Rework Site Visit Evaluation
                    </h4>
                    <p class="text-muted mb-0">You are revising your previous evaluation for accuracy and completeness.</p>
                </div>
            </div>

            <!-- ===== Consortium Info ===== -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-warning text-dark fw-bold">Consortium Information</div>
                <div class="card-body">
                    <h5 class="fw-semibold text-dark">{{ $consortium->think_tank_name ?? 'Unnamed Consortium' }}</h5>
                    <p class="text-muted mb-0">
                        Country: {{ $consortium->country ?? 'N/A' }} <br>
                        Sub-region:
                        {{ is_array(json_decode($consortium->sub_region, true))
                            ? implode(', ', json_decode($consortium->sub_region, true))
                            : $consortium->sub_region ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <!-- ===== Rework Info ===== -->
            <div class="alert alert-warning border-0 shadow-sm mb-4">
                <strong>Rework Required:</strong>
                {{ $evaluation->rework_comment ?? 'Please update the evaluation accordingly.' }}
            </div>

            <!-- ===== Rework Form ===== -->
            <form action="{{ route('sitevisit.update.rework', $evaluation->id) }}" method="POST">
                @csrf

                <!-- Hidden Fields -->
                <input type="hidden" name="consortium_id" value="{{ $evaluation->consortium_id }}">

                @foreach ($sections as $sectionIndex => $section)
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-gradient bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-primary fw-bold">
                                {{ $sectionIndex }}. {{ $section['title'] }}
                                <span class="text-muted fw-normal">
                                    (Max {{ collect($section['subs'])->sum('marks') }} points)
                                </span>
                            </h6>
                        </div>

                        <div id="section{{ $sectionIndex }}" class="collapse show">
                            <div class="card-body bg-light">
                                @foreach ($section['subs'] as $subIndex => $sub)
                                    @php $field = "s{$sectionIndex}_" . ($subIndex + 1); @endphp
                                    <div class="border rounded-3 p-3 mb-3 bg-white">
                                        <h6 class="fw-semibold text-dark">
                                            {{ $sectionIndex }}.{{ $subIndex + 1 }} {{ $sub['label'] }}
                                            <small class="text-muted">(Max: {{ $sub['marks'] }} marks)</small>
                                        </h6>

                                        @if (!empty($sub['guidelines']))
                                            <ul class="mt-2 mb-3 text-muted small ps-3" style="list-style-type: disc;">
                                                @foreach ($sub['guidelines'] as $guide)
                                                    <li>{{ $guide }}</li>
                                                @endforeach
                                            </ul>
                                        @endif

                                        <div class="row mt-2">
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Score</label>
                                                <input type="number" step="0.1" max="{{ $sub['marks'] }}"
                                                    min="0" name="{{ $field }}_score"
                                                    value="{{ old($field . '_score', $evaluation[$field . '_score']) }}"
                                                    class="form-control score-input" placeholder="0-{{ $sub['marks'] }}">
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label fw-semibold text-success">Strength</label>
                                                <textarea name="{{ $field }}_strength" class="form-control" rows="2" placeholder="Write key strengths...">{{ old($field . '_strength', $evaluation[$field . '_strength']) }}</textarea>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label fw-semibold text-danger">Weakness</label>
                                                <textarea name="{{ $field }}_weakness" class="form-control" rows="2"
                                                    placeholder="Write key weaknesses...">{{ old($field . '_weakness', $evaluation[$field . '_weakness']) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Section Comments -->
                                <div class="mt-3">
                                    <label class="form-label fw-semibold">Comments to justify score:</label>
                                    <textarea name="s{{ $sectionIndex }}_comments" class="form-control" rows="2"
                                        placeholder="Enter justification or summary...">{{ old("s{$sectionIndex}_comments", $evaluation["s{$sectionIndex}_comments"]) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- General Observations -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-info text-white fw-bold">General Observations</div>
                    <div class="card-body bg-light">
                        <textarea name="general_observations" class="form-control" rows="3" placeholder="Enter overall observations...">{{ old('general_observations', $evaluation->general_observations) }}</textarea>
                    </div>
                </div>

                <!-- Summary & Final Comments -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white fw-bold">Summary & Final Comments</div>
                    <div class="card-body bg-light">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-success">Overall Strengths</label>
                                <textarea name="overall_strength" class="form-control" rows="3">{{ old('overall_strength', $evaluation->overall_strength) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-danger">Overall Weaknesses</label>
                                <textarea name="overall_weakness" class="form-control" rows="3">{{ old('overall_weakness', $evaluation->overall_weakness) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Additional Comments</label>
                                <textarea name="additional_comments" class="form-control" rows="3">{{ old('additional_comments', $evaluation->additional_comments) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Evaluator Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-gradient bg-light fw-bold">Evaluator Details</div>
                    <div class="card-body bg-white">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Evaluator Name</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->name ?? '' }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sign (Initials)</label>
                                <input type="text" name="evaluator_signature"
                                    value="{{ old('evaluator_signature', $evaluation->evaluator_signature) }}"
                                    class="form-control" placeholder="Enter your initials">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Evaluation Date</label>
                                <input type="date" name="evaluation_date"
                                    value="{{ old('evaluation_date', $evaluation->evaluation_date) }}"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-primary">Total Score</label>
                                <input type="number" name="total_score" id="totalScoreDisplay"
                                    value="{{ $evaluation->total_score }}" class="form-control bg-light" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="text-end mb-5">
                    <button class="btn btn-warning px-4 py-2">
                        <i class="bi bi-check2-circle me-1"></i> Submit Rework
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- ====== JS: Auto-total ====== -->
    <script>
        document.addEventListener("input", function() {
            let total = 0;
            document.querySelectorAll(".score-input").forEach((input) => {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById("totalScoreDisplay").value = total.toFixed(1);
        });
    </script>
@endsection

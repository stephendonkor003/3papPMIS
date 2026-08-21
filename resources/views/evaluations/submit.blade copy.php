@extends('layouts.app')

@section('content')
    <div class="nxl-container evaluation-wrapper">

        {{-- ================= HEADER ================= --}}
        <div class="evaluation-header mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-gradient">
                    {{ $assignment->procurement->title }}
                </h3>
                <p class="text-muted mb-0">
                    Evaluation Type:
                    <span class="badge bg-primary-subtle text-primary fw-semibold">
                        {{ $assignment->evaluation->name }}
                    </span>
                </p>
            </div>

            <div class="eval-guide shadow-sm">
                <i class="feather-info me-1"></i>
                Score objectively. Evidence-based assessment only.
            </div>
        </div>

        <div class="row">

            {{-- ================= MAIN CONTENT ================= --}}
            <div class="col-lg-9">

                {{-- LOCK NOTICE --}}
                <div id="lockedNotice" class="alert alert-warning soft-alert mb-4">
                    <strong>🔒 Evaluation Locked</strong><br>
                    Start the identity camera on the right to unlock scoring.
                </div>

                {{-- ================= APPLICANT INFO ================= --}}
                {{-- ================= APPLICANT INFO ================= --}}


                {{-- ================= APPLICANT INFO ================= --}}
                <div class="card soft-card mb-5">
                    <div class="card-header soft-card-header">
                        Applicant Submitted Information
                    </div>

                    <div class="card-body">

                        {{-- CURRENT APPLICANT ONLY --}}
                        <div class="submission-block mb-4">

                            <div class="row small text-muted mb-3">
                                <div class="col-md-4">
                                    <strong>Submission Code</strong><br>
                                    {{ $applicant->procurement_submission_code }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Applicant</strong><br>
                                    {{ optional($applicant->submitter)->name }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Date</strong><br>
                                    {{ $applicant->created_at->format('d M Y, H:i') }}
                                </div>
                            </div>

                            <table class="table table-sm table-bordered align-middle soft-table">
                                <tbody>
                                    @foreach ($applicant->values as $value)
                                        @php
                                            $val = $value->value;
                                            $decoded = is_string($val) ? json_decode($val, true) : null;
                                        @endphp

                                        <tr>
                                            <th width="30%" class="bg-light fw-semibold">
                                                {{ ucwords(str_replace('_', ' ', $value->field_key)) }}
                                            </th>
                                            <td>
                                                @if (is_string($val) && Str::contains($val, ['storage', 'procurement_submissions']))
                                                    <a href="{{ asset('storage/' . $val) }}" target="_blank"
                                                        class="btn btn-sm btn-outline-primary me-2">
                                                        View
                                                    </a>
                                                    <a href="{{ asset('storage/' . $val) }}" download
                                                        class="btn btn-sm btn-outline-secondary">
                                                        Download
                                                    </a>
                                                @elseif (is_array($decoded))
                                                    @foreach ($decoded as $item)
                                                        <span class="badge bg-secondary-subtle text-dark me-1">
                                                            {{ $item }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    {{ $val }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>

                    </div>
                </div>



                {{-- ================= SCORING ================= --}}
                {{-- ================= SCORING ================= --}}
                <form method="POST" action="{{ route('my.eval.submit', $assignment->id) }}" enctype="multipart/form-data"
                    id="finalForm">

                    @csrf

                    {{-- CRITICAL: applicant identity --}}
                    <input type="hidden" name="form_submission_id" value="{{ $submission->id }}">

                    <div id="evaluationForm" class="d-none">

                        @php
                            $palette = [
                                ['border' => '#2563eb', 'bg' => '#eff6ff'],
                                ['border' => '#16a34a', 'bg' => '#ecfdf5'],
                                ['border' => '#0891b2', 'bg' => '#ecfeff'],
                                ['border' => '#d97706', 'bg' => '#fffbeb'],
                                ['border' => '#dc2626', 'bg' => '#fef2f2'],
                            ];
                            $overallMax = 0;
                        @endphp

                        @foreach ($assignment->evaluation->sections as $i => $section)
                            @php
                                $sectionMax = $section->criteria->sum('max_score');
                                $overallMax += $sectionMax;
                                $theme = $palette[$i % count($palette)];
                            @endphp

                            <div class="card soft-card mb-4 evaluation-section"
                                style="border-left:6px solid {{ $theme['border'] }};
                        background: {{ $theme['bg'] }}">

                                <div class="card-header bg-transparent fw-bold d-flex justify-content-between">
                                    <span>{{ $section->name }}</span>
                                    <span>
                                        <span class="section-total">0</span> / {{ $sectionMax }}
                                    </span>
                                </div>

                                <div class="card-body">
                                    <table class="table table-sm table-bordered align-middle soft-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Criteria</th>
                                                <th>Description</th>
                                                <th width="80">Max</th>
                                                <th width="120">Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($section->criteria as $criteria)
                                                @php
                                                    $savedScore = $submissionModel?->criteriaScores->firstWhere(
                                                        'evaluation_criteria_id',
                                                        $criteria->id,
                                                    )?->score;
                                                @endphp
                                                <tr>
                                                    <td class="fw-semibold">{{ $criteria->name }}</td>
                                                    <td class="text-muted">{{ $criteria->description }}</td>
                                                    <td class="text-center fw-bold">{{ $criteria->max_score }}</td>
                                                    <td>
                                                        <input type="number" name="criteria[{{ $criteria->id }}]"
                                                            class="form-control form-control-sm score-input" min="0"
                                                            max="{{ $criteria->max_score }}" step="0.01"
                                                            data-max="{{ $criteria->max_score }}"
                                                            value="{{ $savedScore }}" required>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach

                        {{-- OVERALL --}}
                        <div class="card soft-card mb-4 border-dark">
                            <div class="card-body text-end fw-bold fs-5">
                                Overall Score:
                                <span id="overallScore" class="text-primary">0</span>
                                / {{ $overallMax }}
                            </div>
                        </div>

                        {{-- VIDEO --}}
                        <input type="file" name="video" id="finalVideo" hidden required>

                        <button class="btn btn-success btn-lg px-5 shadow-sm">
                            Submit Final Evaluation
                        </button>

                    </div>
                </form>



            </div>

            {{-- ================= RIGHT SIDEBAR ================= --}}
            <div class="col-lg-3">

                {{-- ================= MONITOR PANEL ================= --}}
                <div class="info-panel soft-card mb-3">

                    <div class="info-header">
                        <i class="feather-activity"></i>
                        Evaluation Monitor
                    </div>

                    <div class="info-body">

                        <div class="info-item">
                            <span><i class="feather-calendar"></i> Date</span>
                            <strong id="currentDate">—</strong>
                        </div>

                        <div class="info-item">
                            <span><i class="feather-clock"></i> Current Time</span>
                            <strong id="currentTime">—</strong>
                        </div>

                        <div class="info-item highlight">
                            <span><i class="feather-watch"></i> Time Spent</span>
                            <strong id="timeSpent">00:00:00</strong>
                        </div>

                        <div class="info-item">
                            <span><i class="feather-lock"></i> Evaluation Status</span>
                            <strong id="evalStatus" class="text-danger">Locked</strong>
                        </div>

                        <hr>

                        <div class="info-note">
                            <i class="feather-shield"></i>
                            This session is monitored, logged, and auditable.
                            Identity verification is mandatory.
                        </div>

                    </div>
                </div>

                {{-- ================= CAMERA PANEL ================= --}}
                <div class="camera-panel soft-card">

                    <div class="camera-header">
                        <span class="camera-status idle" id="cameraStatus"></span>
                        Identity Verification
                    </div>

                    <div class="camera-frame mb-2">
                        <video id="preview" autoplay muted playsinline></video>
                    </div>

                    <div class="camera-actions">
                        <button id="startCamera" class="btn btn-outline-primary btn-sm w-100">
                            <i class="feather-video"></i> Start Camera
                        </button>

                        <button id="stopCamera" class="btn btn-outline-danger btn-sm w-100 d-none">
                            <i class="feather-stop-circle"></i> Stop Recording (15s)
                        </button>
                    </div>

                    <div class="camera-note mt-2">
                        <i class="feather-lock"></i>
                        Video confirmation is mandatory before submission
                    </div>

                </div>

            </div>


        </div>
    </div>

    {{-- ================= STYLES ================= --}}
    <style>
        /* ================= GENERAL CARD ================= */
        .soft-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .05);
        }

        /* ================= MONITOR ================= */
        .info-header {
            background: linear-gradient(135deg, #2563eb, #16a34a);
            color: #fff;
            padding: 12px 14px;
            border-radius: 14px 14px 0 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-body {
            padding: 14px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .info-item span {
            color: #475569;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-item strong {
            font-weight: 600;
        }

        .info-item.highlight strong {
            font-size: 18px;
            color: #dc2626;
        }

        .info-note {
            font-size: 12px;
            color: #64748b;
            line-height: 1.4;
            display: flex;
            gap: 6px;
        }

        /* ================= CAMERA ================= */
        .camera-header {
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .camera-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .camera-status.idle {
            background: #94a3b8;
        }

        .camera-status.recording {
            background: #dc2626;
            box-shadow: 0 0 0 6px rgba(220, 38, 38, .2);
        }

        .camera-frame {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            background: #000;
        }

        .camera-frame video {
            width: 100%;
        }

        .camera-actions {
            margin-top: 10px;
        }

        .camera-actions button {
            margin-bottom: 6px;
        }

        .camera-note {
            font-size: 12px;
            color: #64748b;
            text-align: center;
        }

        .text-gradient {
            background: linear-gradient(90deg, #2563eb, #16a34a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .soft-card {
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .04);
        }

        .soft-card-header {
            background: #f8fafc;
            font-weight: 600;
        }

        .soft-table th,
        .soft-table td {
            vertical-align: middle;
        }

        .eval-guide {
            background: linear-gradient(135deg, #eff6ff, #ecfdf5);
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 14px;
        }

        .info-panel .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .camera-panel video {
            width: 100%;
            border-radius: 10px;
        }
    </style>

    {{-- ================= JS ================= --}}
    <script>
        /* =====================================================
         * SAFE DOM HELPERS
         * ===================================================== */
        const byId = id => document.getElementById(id);

        /* =====================================================
         * ELEMENT REFERENCES (DEFENSIVE)
         * ===================================================== */
        const currentDate = byId('currentDate');
        const currentTime = byId('currentTime');
        const timeSpent = byId('timeSpent');
        const startCamera = byId('startCamera');
        const stopCamera = byId('stopCamera');
        const preview = byId('preview');
        const finalVideo = byId('finalVideo');
        const finalForm = byId('finalForm');
        const lockedNotice = byId('lockedNotice');
        const evaluationForm = byId('evaluationForm');
        const overallScore = byId('overallScore');
        const cameraStatus = byId('cameraStatus');
        const evalStatus = byId('evalStatus');

        /* =====================================================
         * CLOCK
         * ===================================================== */
        function updateClock() {
            if (!currentDate || !currentTime) return;
            const now = new Date();
            currentDate.textContent = now.toDateString();
            currentTime.textContent = now.toLocaleTimeString();
        }
        updateClock();
        setInterval(updateClock, 1000);

        /* =====================================================
         * TIMER
         * ===================================================== */
        let startedAt = null;
        let timerInterval = setInterval(() => {
            if (!startedAt || !timeSpent) return;

            const diff = Math.floor((Date.now() - startedAt) / 1000);
            const h = String(Math.floor(diff / 3600)).padStart(2, '0');
            const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
            const s = String(diff % 60).padStart(2, '0');
            timeSpent.textContent = `${h}:${m}:${s}`;
        }, 1000);

        /* =====================================================
         * CAMERA STATUS HANDLER
         * ===================================================== */
        function setCameraState(state) {
            if (!cameraStatus || !evalStatus) return;

            cameraStatus.className = 'camera-status';

            switch (state) {
                case 'recording':
                    cameraStatus.classList.add('recording');
                    evalStatus.textContent = 'Verifying Identity';
                    evalStatus.className = 'text-warning fw-semibold';
                    break;

                case 'verified':
                    cameraStatus.classList.add('verified');
                    evalStatus.textContent = 'Unlocked';
                    evalStatus.className = 'text-success fw-semibold';
                    break;

                default:
                    cameraStatus.classList.add('idle');
                    evalStatus.textContent = 'Locked';
                    evalStatus.className = 'text-danger fw-semibold';
            }
        }
        setCameraState('idle');

        /* =====================================================
         * CAMERA (VIDEO REQUIRED)
         * ===================================================== */
        let recorder = null;
        let chunks = [];
        let stream = null;
        let autoStopTimer = null;

        if (startCamera && stopCamera && preview && finalVideo) {

            startCamera.addEventListener('click', async () => {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: true
                    });

                    preview.srcObject = stream;

                    recorder = new MediaRecorder(stream, {
                        mimeType: 'video/webm'
                    });
                    chunks = [];
                    recorder.start();
                    startedAt = Date.now();
                    setCameraState('recording');

                    startCamera.classList.add('d-none');
                    stopCamera.classList.remove('d-none');

                    recorder.ondataavailable = e => e.data.size && chunks.push(e.data);

                    autoStopTimer = setTimeout(() => {
                        if (recorder && recorder.state === 'recording') {
                            stopCamera.click();
                        }
                    }, 15000);

                } catch (err) {
                    alert('Camera or microphone access denied.');
                    console.error(err);
                }
            });

            stopCamera.addEventListener('click', () => {
                if (!recorder) return;

                recorder.stop();
                clearTimeout(autoStopTimer);
                stopCamera.classList.add('d-none');

                recorder.onstop = () => {
                    stream?.getTracks().forEach(t => t.stop());
                    preview.srcObject = null;

                    const blob = new Blob(chunks, {
                        type: 'video/webm'
                    });
                    const file = new File([blob], 'identity.webm', {
                        type: 'video/webm'
                    });

                    const dt = new DataTransfer();
                    dt.items.add(file);
                    finalVideo.files = dt.files;

                    setCameraState('verified');
                    lockedNotice?.classList.add('d-none');
                    evaluationForm?.classList.remove('d-none');
                };
            });
        }

        /* =====================================================
         * SCORING (UI ONLY)
         * ===================================================== */
        function recalcScores() {
            let total = 0;

            document.querySelectorAll('.evaluation-section').forEach(section => {
                let sectionTotal = 0;

                section.querySelectorAll('.score-input').forEach(input => {
                    let val = parseFloat(input.value) || 0;
                    const max = parseFloat(input.dataset.max || 0);

                    if (val > max) {
                        val = max;
                        input.value = max;
                    }

                    sectionTotal += val;
                });

                const display = section.querySelector('.section-total');
                if (display) display.textContent = sectionTotal.toFixed(2);

                total += sectionTotal;
            });

            if (overallScore) overallScore.textContent = total.toFixed(2);
        }

        document.addEventListener('input', e => {
            if (e.target.classList.contains('score-input')) {
                recalcScores();
            }
        });

        /* =====================================================
         * FORM SUBMIT VALIDATION
         * ===================================================== */
        if (finalForm) {
            finalForm.addEventListener('submit', e => {
                if (!finalVideo?.files?.length) {
                    e.preventDefault();
                    alert('Identity verification video is required.');
                }
            });
        }
    </script>
@endsection

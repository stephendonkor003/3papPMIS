@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header mb-4">
            <h4 class="fw-bold mb-1">Prescreening Assignments</h4>
            <p class="text-muted mb-0">
                Assign and manage prescreening evaluators for each procurement
            </p>
        </div>

        {{-- ================= EDUCATIVE INFO ================= --}}
        <div class="alert alert-info d-flex align-items-start gap-3 mb-4">
            <i class="feather-info fs-4 mt-1"></i>
            <div>
                <h6 class="fw-semibold mb-1">How Prescreening Assignments Work</h6>
                <ul class="mb-0 ps-3 small">
                    <li>Each procurement can have <strong>one prescreening evaluator</strong>.</li>
                    <li>Only the assigned user can <strong>evaluate submissions</strong>.</li>
                    <li>Assignments can be updated anytime <strong>before prescreening starts</strong>.</li>
                    <li>This step does <strong>not</strong> affect the prescreening template or criteria.</li>
                </ul>
            </div>
        </div>

        {{-- ================= SEARCH FILTER ================= --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1">
                            Search Procurements
                        </label>
                        <input type="text" id="procurementSearch" class="form-control"
                            placeholder="Search by title, reference number, or status…">
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <span class="text-muted small">
                            Showing <span id="resultCount">{{ $procurements->count() }}</span>
                            procurements
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= ACCORDION ================= --}}
        <div class="accordion" id="prescreeningAccordion">

            @forelse ($procurements as $procurement)
                @php
                    $statusColor = match ($procurement->status) {
                        'published' => 'primary',
                        'approved' => 'success',
                        'closed' => 'dark',
                        'awarded' => 'success',
                        'submitted' => 'warning',
                        default => 'secondary',
                    };
                @endphp

                <div class="accordion-item mb-3 procurement-card" data-title="{{ strtolower($procurement->title) }}"
                    data-ref="{{ strtolower($procurement->reference_no ?? '') }}"
                    data-status="{{ strtolower($procurement->status ?? '') }}">

                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed border-start border-4 border-{{ $statusColor }}"
                            type="button" data-bs-toggle="collapse" data-bs-target="#proc{{ $procurement->id }}">

                            <div class="w-100 d-flex justify-content-between align-items-center pe-3">

                                {{-- LEFT --}}
                                <div>
                                    <h6 class="mb-1 fw-semibold">
                                        {{ $procurement->title }}
                                    </h6>
                                    <div class="small text-muted">
                                        Ref: {{ $procurement->reference_no ?? '—' }}
                                        • {{ $procurement->fiscal_year ?? '—' }}
                                    </div>
                                </div>

                                {{-- RIGHT --}}
                                <div class="d-flex align-items-center gap-2">

                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ ucfirst($procurement->status ?? 'draft') }}
                                    </span>

                                    <span class="badge bg-light text-dark">
                                        {{ $procurement->prescreeningUsers->count() }} Evaluator(s)
                                    </span>

                                </div>

                            </div>
                        </button>
                    </h2>

                    <div id="proc{{ $procurement->id }}" class="accordion-collapse collapse"
                        data-bs-parent="#prescreeningAccordion">

                        <div class="accordion-body">

                            {{-- ================= USERS ================= --}}
                            @if ($procurement->prescreeningUsers->count())
                                <div class="row g-3 mb-4">
                                    @foreach ($procurement->prescreeningUsers->take(1) as $user)
                                        <div class="col-md-4 col-lg-3">
                                            <div class="border rounded p-3 bg-light d-flex gap-3">

                                                <div class="rounded-circle bg-{{ $statusColor }} text-white fw-bold
                                                d-flex align-items-center justify-content-center"
                                                    style="width:40px;height:40px;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>

                                                <div>
                                                    <div class="fw-semibold">{{ $user->name }}</div>
                                                    <div class="small text-muted">{{ $user->email }}</div>
                                                </div>

                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if ($procurement->prescreeningUsers->count() > 1)
                                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
                                        <i class="feather-alert-circle"></i>
                                        <span>More than one prescreener is assigned. Only one should be assigned.</span>
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
                                    <i class="feather-alert-circle"></i>
                                    <span>No prescreening evaluators assigned yet.</span>
                                </div>
                            @endif

                            {{-- ================= ACTIONS ================= --}}
                            <div class="d-flex justify-content-between align-items-center">

                                <div class="small text-muted">
                                    Last updated:
                                    {{ $procurement->updated_at?->diffForHumans() ?? '—' }}
                                </div>

                                <a href="{{ route('prescreening.assignments.edit', $procurement) }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="feather-user-plus me-1"></i>
                                    Assign / Update Evaluators
                                </a>

                            </div>

                        </div>
                    </div>
                </div>

            @empty
                <div class="alert alert-info">
                    No procurements available for prescreening assignment.
                </div>
            @endforelse

        </div>

    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('procurementSearch').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            let visible = 0;

            document.querySelectorAll('.procurement-card').forEach(card => {
                const title = card.dataset.title;
                const ref = card.dataset.ref;
                const status = card.dataset.status;

                if (title.includes(query) || ref.includes(query) || status.includes(query)) {
                    card.style.display = '';
                    visible++;
                } else {
                    card.style.display = 'none';
                }
            });

            document.getElementById('resultCount').innerText = visible;
        });
    });
</script>
@endpush

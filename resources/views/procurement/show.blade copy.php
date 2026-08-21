@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="page-title mb-1">Procurement Details</h4>
                <p class="text-muted mb-0">
                    View procurement information and manage attached forms
                </p>
            </div>

            <a href="{{ route('procurements.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="feather-arrow-left me-1"></i> Back to List
            </a>
        </div>

        {{-- ================= PROCUREMENT INFO ================= --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-semibold">Procurement Information</h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="text-muted small">Reference Number</label>
                        <div class="fw-semibold">
                            {{ $procurement->reference_no ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-8">
                        <label class="text-muted small">Title</label>
                        <div class="fw-semibold">
                            {{ $procurement->title }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="text-muted small">Category</label>
                        <div>
                            {{ $procurement->resource->name ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="text-muted small">Fiscal Year</label>
                        <div>
                            {{ $procurement->fiscal_year ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="text-muted small">Estimated Budget</label>
                        <div>
                            {{ $procurement->estimated_budget ? number_format($procurement->estimated_budget, 2) : '—' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="text-muted small">Status</label>
                        <div>
                            <span class="badge bg-secondary">
                                {{ ucfirst($procurement->status ?? 'draft') }}
                            </span>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <label class="text-muted small">Description</label>
                        <div class="text-muted">
                            {{ $procurement->description ?? 'No description provided.' }}
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ================= ATTACHED FORMS ================= --}}
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Attached Forms</h6>
                <span class="badge bg-secondary">
                    {{ $procurement->forms->count() }} Forms
                </span>
            </div>

            <div class="card-body p-0">

                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Form</th>
                            <th>Stage</th>
                            <th>Status</th>
                            <th width="220" class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($procurement->forms as $form)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $form->name }}</div>
                                    <small class="text-muted">
                                        {{ $form->resource->name ?? '—' }}
                                    </small>
                                </td>

                                <td>
                                    <span class="badge bg-info">
                                        {{ ucfirst($form->applies_to) }}
                                    </span>
                                </td>

                                <td>
                                    <span
                                        class="badge
                                @if ($form->status === 'approved') bg-success
                                @elseif($form->status === 'submitted') bg-warning text-dark
                                @elseif($form->status === 'rejected') bg-danger
                                @else bg-secondary @endif">
                                        {{ ucfirst($form->status) }}
                                    </span>
                                </td>

                                <td class="text-center d-flex justify-content-center gap-1">

                                    {{-- VIEW / EDIT --}}
                                    <a href="{{ route('forms.edit', $form->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="feather-eye"></i>
                                    </a>

                                    {{-- SUBMIT --}}
                                    {{-- @if ($form->canEdit()) --}}
                                    <form method="POST" action="{{ route('forms.submit', $form) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-warning">
                                            <i class="feather-send"></i>
                                        </button>
                                    </form>
                                    {{-- @endif --}}

                                    {{-- APPROVE --}}
                                    {{-- @if ($form->isSubmitted()) --}}
                                    <form method="POST" action="{{ route('forms.approve', $form) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success">
                                            <i class="feather-check"></i>
                                        </button>
                                    </form>

                                    {{-- REJECT --}}
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#rejectFormModal{{ $form->id }}">
                                        <i class="feather-x"></i>
                                    </button>
                                    {{-- @endif --}}

                                </td>
                            </tr>

                            {{-- REJECT FORM MODAL --}}
                            <div class="modal fade" id="rejectFormModal{{ $form->id }}" tabindex="-1">
                                <div class="modal-dialog modal-md modal-dialog-centered">
                                    <div class="modal-content">

                                        <form method="POST" action="{{ route('forms.reject', $form) }}">
                                            @csrf

                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">
                                                    Reject Form
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <label class="form-label fw-semibold">
                                                    Rejection Reason <span class="text-danger">*</span>
                                                </label>
                                                <textarea name="rejection_reason" class="form-control" rows="4" required></textarea>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">
                                                    Cancel
                                                </button>

                                                <button class="btn btn-danger">
                                                    Reject
                                                </button>
                                            </div>

                                        </form>

                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No forms attached to this procurement yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>

    </div>
@endsection

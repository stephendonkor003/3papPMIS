@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- HEADER --}}
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold">Assign Prescreening Template</h4>
                <p class="text-muted mb-0">
                    Procurement: <strong>{{ $procurement->title }}</strong>
                </p>
            </div>

            <a href="{{ route('procurements.show', $procurement) }}" class="btn btn-outline-secondary btn-sm">
                Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                <form method="POST" action="{{ route('procurements.prescreening.store', $procurement) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Select Prescreening Template <span class="text-danger">*</span>
                        </label>

                        <select name="prescreening_template_id" class="form-select" required
                            {{ $procurement->submissions()->exists() ? 'disabled' : '' }}>
                            <option value="">-- Select Template --</option>

                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}" @selected(optional($assignedTemplate)->id === $template->id)>
                                    {{ $template->name }}
                                    ({{ $template->criteria->count() }} criteria)
                                </option>
                            @endforeach
                        </select>

                        @if ($procurement->submissions()->exists())
                            <small class="text-danger">
                                Template cannot be changed after submissions begin.
                            </small>
                        @endif
                    </div>

                    @if (!$procurement->submissions()->exists())
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-success">
                                <i class="feather-save me-1"></i>
                                Assign Template
                            </button>
                        </div>
                    @endif

                </form>

            </div>
        </div>

    </div>
@endsection

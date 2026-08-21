@extends('layouts.app')

@section('title', 'Partner Request')

@section('content')
    <div class="nxl-container">
        <div class="page-header mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="feather-message-square me-2"></i>{{ $request->subject }}
                </h4>
                <p class="text-muted mb-0">Partner request #{{ $request->id }}</p>
            </div>
            <a href="{{ route('finance.partner-requests.index') }}" class="btn btn-light">
                <i class="feather-arrow-left me-1"></i>Back to Requests
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>The response could not be saved.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Request Details</h5>
                        <div class="d-flex gap-2">
                            <span class="badge {{ $request->getPriorityBadgeClass() }}">{{ \Illuminate\Support\Str::headline($request->priority ?? 'normal') }}</span>
                            <span class="badge {{ $request->getStatusBadgeClass() }}">{{ \Illuminate\Support\Str::headline($request->status) }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-muted">Funding partner</dt>
                            <dd class="col-sm-8">{{ $request->funder?->name ?? 'Not specified' }}</dd>

                            <dt class="col-sm-4 text-muted">Submitted by</dt>
                            <dd class="col-sm-8">
                                {{ $request->requester?->name ?? 'Unknown user' }}
                                @if ($request->requester?->email)
                                    <div class="small text-muted">{{ $request->requester->email }}</div>
                                @endif
                            </dd>

                            <dt class="col-sm-4 text-muted">Request type</dt>
                            <dd class="col-sm-8">{{ \Illuminate\Support\Str::headline($request->request_type) }}</dd>

                            <dt class="col-sm-4 text-muted">Related program</dt>
                            <dd class="col-sm-8">{{ $request->programFunding?->program?->name ?? $request->programFunding?->program_name ?? 'General request' }}</dd>

                            <dt class="col-sm-4 text-muted">Submitted</dt>
                            <dd class="col-sm-8">{{ $request->created_at?->format('d M Y H:i') ?? 'Not recorded' }}</dd>
                        </dl>

                        <hr>
                        <h6 class="fw-semibold">Message</h6>
                        <div class="rounded bg-light p-3" style="white-space:pre-wrap;">{{ $request->message }}</div>
                    </div>
                </div>

                @if ($request->hasResponse())
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Current Response</h5>
                        </div>
                        <div class="card-body">
                            <div style="white-space:pre-wrap;">{{ $request->response }}</div>
                            <hr>
                            <div class="small text-muted">
                                Responded by {{ $request->responder?->name ?? 'System user' }}
                                @if ($request->responded_at)
                                    on {{ $request->responded_at->format('d M Y H:i') }}
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-xl-4">
                @can('partner.requests.respond')
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Respond to Partner</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('finance.partner-requests.respond', $request->id) }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="status" class="form-label fw-semibold">Status</label>
                                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        @foreach (['in_progress' => 'In Progress', 'completed' => 'Completed', 'rejected' => 'Rejected'] as $value => $label)
                                            <option value="{{ $value }}" @selected(old('status', $request->status) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="response" class="form-label fw-semibold">Response</label>
                                    <textarea
                                        id="response"
                                        name="response"
                                        rows="9"
                                        minlength="10"
                                        class="form-control @error('response') is-invalid @enderror"
                                        required
                                    >{{ old('response', $request->response) }}</textarea>
                                    @error('response')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">The partner will receive this response by email.</div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="feather-send me-1"></i>Save and Send Response
                                </button>
                            </form>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>
@endsection

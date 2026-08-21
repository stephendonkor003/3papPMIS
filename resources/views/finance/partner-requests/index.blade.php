@extends('layouts.app')

@section('title', 'Partner Information Requests')

@section('content')
    <div class="nxl-container">
        <div class="page-header mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="feather-message-square me-2"></i>Partner Information Requests
                </h4>
                <p class="text-muted mb-0">Review and respond to requests submitted by funding partners.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <x-data-table id="partnerRequestsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th>Partner</th>
                            <th>Program</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-center" width="90">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $request)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $request->subject }}</div>
                                    <div class="small text-muted">{{ \Illuminate\Support\Str::headline($request->request_type) }}</div>
                                </td>
                                <td>{{ $request->funder?->name ?? 'Not specified' }}</td>
                                <td>{{ $request->programFunding?->program?->name ?? $request->programFunding?->program_name ?? 'General request' }}</td>
                                <td>
                                    <span class="badge {{ $request->getPriorityBadgeClass() }}">
                                        {{ \Illuminate\Support\Str::headline($request->priority ?? 'normal') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $request->getStatusBadgeClass() }}">
                                        {{ \Illuminate\Support\Str::headline($request->status) }}
                                    </span>
                                </td>
                                <td data-order="{{ $request->created_at?->timestamp }}">
                                    {{ $request->created_at?->format('d M Y H:i') ?? 'Not recorded' }}
                                </td>
                                <td class="text-center">
                                    <a
                                        href="{{ route('finance.partner-requests.show', $request->id) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Review request"
                                        aria-label="Review {{ $request->subject }}"
                                    >
                                        <i class="feather-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No partner requests have been submitted.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-data-table>
            </div>
        </div>
    </div>
@endsection

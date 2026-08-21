@extends('layouts.partner')

@section('content')
<div class="nxl-container">
    <div class="page-header">
        <div>
            <h4 class="fw-bold mb-1">{{ __('partner.my_requests') }}</h4>
            <p class="text-muted mb-0">{{ __('partner.requests_description') }}</p>
        </div>
        @can('partner.requests.create')
            <a href="{{ route('partner.requests.create') }}" class="btn btn-primary">
                <i class="feather-plus me-1"></i> {{ __('partner.new_request') }}
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="feather-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <table id="requestsTable" class="table table-striped table-hover {{ $requests->count() > 0 ? 'data-table' : '' }}" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th>{{ __('partner.subject') }}</th>
                        <th style="width: 120px;">{{ __('partner.request_type') }}</th>
                        <th style="width: 100px;">{{ __('partner.priority') }}</th>
                        <th style="width: 120px;">{{ __('partner.status') }}</th>
                        <th style="width: 120px;">{{ __('partner.created_date') }}</th>
                        <th style="width: 100px;" class="text-center no-sort no-export">{{ __('partner.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $request->subject }}</strong>
                            @if($request->programFunding)
                                <div class="text-muted small">
                                    <i class="feather-folder me-1"></i>
                                    {{ $request->programFunding->program_name ?? $request->programFunding->program->name ?? '' }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $request->request_type)) }}</span>
                        </td>
                        <td>
                            @php
                                $priorityClass = match($request->priority) {
                                    'urgent' => 'bg-danger',
                                    'high' => 'bg-warning text-dark',
                                    'normal' => 'bg-primary',
                                    'low' => 'bg-secondary',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $priorityClass }}">{{ ucfirst($request->priority) }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $request->getStatusBadgeClass() }}">
                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                            </span>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td class="text-center no-export">
                            <a href="{{ route('partner.requests.show', $request->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="feather-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            {{ __('partner.no_requests') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

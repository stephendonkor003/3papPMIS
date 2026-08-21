@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="fw-bold text-dark">Procurements Management</h4>

            {{-- @can('procurement.manage') --}}
            <a href="{{ route('procurements.create') }}" class="btn btn-primary btn-sm">
                <i class="feather-plus me-1"></i> New Procurement
            </a>
            {{-- @endcan --}}
        </div>

        {{-- ================= CONTEXT BANNER (ATTACH MODE) ================= --}}
        @if ($attachFormId)
            <div class="alert alert-info mt-3">
                <i class="feather-info me-1"></i>
                Select a procurement to attach the selected form.
            </div>
        @endif

        {{-- ================= TABLE CARD ================= --}}
        <div class="card mt-3 shadow-sm">
            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Reference</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th width="160">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($procurements as $index => $procurement)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td>{{ $procurement->reference_no ?? '—' }}</td>

                                <td>{{ $procurement->title ?? '—' }}</td>

                                <td>
                                    @php
                                        $status = strtolower($procurement->status);
                                        $badgeClass = match ($status) {
                                            'draft' => 'bg-secondary',
                                            'approved' => 'bg-warning',
                                            'published' => 'bg-primary',
                                            'closed' => 'bg-dark',
                                            'awarded' => 'bg-success',
                                            default => 'bg-info',
                                        };
                                    @endphp

                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>

                                <td class="text-center">

                                    {{-- VIEW --}}
                                    <a href="{{ route('procurements.show', $procurement->id) }}"
                                        class="btn btn-sm btn-outline-primary" title="View Procurement">
                                        <i class="feather-eye"></i>
                                    </a>

                                    {{-- ATTACH FORM (ONLY WHEN COMING FROM FORMS INDEX) --}}
                                    @if ($attachFormId)
                                        @can('procurement.manage')
                                            <a href="{{ route('procurements.forms.attach', $procurement->id) }}"
                                                class="btn btn-sm btn-outline-success ms-1" title="Attach Form">
                                                <i class="feather-link"></i>
                                            </a>
                                        @endcan
                                    @endif

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    No procurements found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>

    </div>
@endsection

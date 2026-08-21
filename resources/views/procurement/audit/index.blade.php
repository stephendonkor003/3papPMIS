@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        <div class="page-header d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="feather-file-text text-primary me-2"></i>
                    Procurement Audit Logs
                </h4>
                <p class="text-muted mb-0">
                    Track all procurement activities and changes
                </p>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <x-data-table id="auditTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Action</th>
                            <th>Procurement</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-medium">{{ $log->user->name ?? 'User #' . $log->user_id }}</div>
                                    <small class="text-muted">{{ $log->user->email ?? '' }}</small>
                                </td>
                                <td>
                                    @php
                                        $actionColors = [
                                            'created' => 'success',
                                            'updated' => 'info',
                                            'deleted' => 'danger',
                                            'submitted' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'published' => 'primary',
                                            'closed' => 'dark',
                                            'awarded' => 'success',
                                        ];
                                        $color = $actionColors[$log->action] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }} px-3 py-1">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->procurement)
                                        <div class="fw-medium">{{ $log->procurement->title }}</div>
                                        <small class="text-muted">{{ $log->procurement->reference_no }}</small>
                                    @else
                                        <span class="text-muted">Procurement #{{ $log->procurement_id }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $log->created_at->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-data-table>
            </div>
        </div>

    </div>
@endsection

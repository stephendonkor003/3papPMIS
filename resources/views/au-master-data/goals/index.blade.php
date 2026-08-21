@extends('layouts.app')

@section('title', 'AU Goals')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Agenda 2063 Goals</h4>
                    <p class="text-muted mb-0">Manage the 20 goals of the African Union's Agenda 2063.</p>
                </div>
                @can('settings.au_master_data.create')
                    <a href="{{ route('settings.au.goals.create') }}" class="btn btn-success">
                        <i class="feather-plus-circle me-1"></i> Add Goal
                    </a>
                @endcan
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <x-data-table id="goalsTable" :config="['order' => [[0, 'asc']], 'pageLength' => 25]">
                        <thead class="table-light">
                            <tr>
                                <th width="80">Number</th>
                                <th>Title</th>
                                <th>Aspiration</th>
                                <th width="80">Status</th>
                                <th width="120" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($goals as $goal)
                                <tr>
                                    <td>
                                        <span class="badge bg-info fs-6">{{ $goal->number }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $goal->title }}</strong>
                                        @if ($goal->description)
                                            <br><small class="text-muted">{{ Str::limit($goal->description, 80) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">Aspiration {{ $goal->aspiration->number }}</span>
                                        <br><small class="text-muted">{{ Str::limit($goal->aspiration->title, 50) }}</small>
                                    </td>
                                    <td>
                                        @if ($goal->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @can('settings.au_master_data.edit')
                                            <a href="{{ route('settings.au.goals.edit', $goal->id) }}"
                                                class="btn btn-sm btn-outline-warning">
                                                <i class="feather-edit"></i>
                                            </a>
                                        @endcan
                                        @can('settings.au_master_data.delete')
                                            <form action="{{ route('settings.au.goals.destroy', $goal->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Delete this goal?')">
                                                    <i class="feather-trash-2"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-data-table>
                </div>
            </div>
        </div>
    </main>
@endsection

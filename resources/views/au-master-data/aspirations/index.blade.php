@extends('layouts.app')

@section('title', 'AU Aspirations')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Agenda 2063 Aspirations</h4>
                    <p class="text-muted mb-0">Manage the seven aspirations of the African Union's Agenda 2063.</p>
                </div>
                @can('settings.au_master_data.create')
                    <a href="{{ route('settings.au.aspirations.create') }}" class="btn btn-success">
                        <i class="feather-plus-circle me-1"></i> Add Aspiration
                    </a>
                @endcan
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <x-data-table id="aspirationsTable" :config="['order' => [[0, 'asc']], 'pageLength' => 25]">
                        <thead class="table-light">
                            <tr>
                                <th width="80">Number</th>
                                <th>Title</th>
                                <th width="80">Goals</th>
                                <th width="80">Status</th>
                                <th width="120" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($aspirations as $aspiration)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary fs-6">{{ $aspiration->number }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $aspiration->title }}</strong>
                                        @if ($aspiration->description)
                                            <br><small class="text-muted">{{ Str::limit($aspiration->description, 100) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $aspiration->goals->count() }} goals</span>
                                    </td>
                                    <td>
                                        @if ($aspiration->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @can('settings.au_master_data.edit')
                                            <a href="{{ route('settings.au.aspirations.edit', $aspiration->id) }}"
                                                class="btn btn-sm btn-outline-warning">
                                                <i class="feather-edit"></i>
                                            </a>
                                        @endcan
                                        @can('settings.au_master_data.delete')
                                            <form action="{{ route('settings.au.aspirations.destroy', $aspiration->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Delete this aspiration? This will also delete all associated goals.')">
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

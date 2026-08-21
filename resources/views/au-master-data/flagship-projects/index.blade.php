@extends('layouts.app')

@section('title', 'AU Flagship Projects')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">AU Flagship Projects</h4>
                    <p class="text-muted mb-0">Manage the 12 flagship projects of the African Union's Agenda 2063.</p>
                </div>
                @can('settings.au_master_data.create')
                    <a href="{{ route('settings.au.flagship-projects.create') }}" class="btn btn-success">
                        <i class="feather-plus-circle me-1"></i> Add Flagship Project
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
                    <x-data-table id="flagshipProjectsTable" :config="['order' => [[0, 'asc']], 'pageLength' => 25]">
                        <thead class="table-light">
                            <tr>
                                <th width="80">Number</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th width="80">Status</th>
                                <th width="120" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($flagshipProjects as $project)
                                <tr>
                                    <td>
                                        <span class="badge bg-warning text-dark fs-6">{{ $project->number }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $project->name }}</strong>
                                    </td>
                                    <td>
                                        @if ($project->description)
                                            <small class="text-muted">{{ Str::limit($project->description, 100) }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($project->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @can('settings.au_master_data.edit')
                                            <a href="{{ route('settings.au.flagship-projects.edit', $project->id) }}"
                                                class="btn btn-sm btn-outline-warning">
                                                <i class="feather-edit"></i>
                                            </a>
                                        @endcan
                                        @can('settings.au_master_data.delete')
                                            <form action="{{ route('settings.au.flagship-projects.destroy', $project->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Delete this flagship project?')">
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

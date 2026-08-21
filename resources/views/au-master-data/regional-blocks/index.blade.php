@extends('layouts.app')

@section('title', 'AU Regional Blocks')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">AU Regional Blocks (RECs)</h4>
                    <p class="text-muted mb-0">Manage Regional Economic Communities recognized by the African Union.</p>
                </div>
                @can('settings.au_master_data.create')
                    <a href="{{ route('settings.au.regional-blocks.create') }}" class="btn btn-success">
                        <i class="feather-plus-circle me-1"></i> Add Regional Block
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
                    <x-data-table id="regionalBlocksTable" :config="['order' => [[3, 'asc']], 'pageLength' => 25]">
                        <thead class="table-light">
                            <tr>
                                <th width="60">#</th>
                                <th>Name</th>
                                <th width="100">Abbreviation</th>
                                <th width="80">Order</th>
                                <th width="80">Status</th>
                                <th width="120" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($regionalBlocks as $block)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $block->name }}</strong>
                                        @if ($block->description)
                                            <br><small class="text-muted">{{ Str::limit($block->description, 80) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($block->abbreviation)
                                            <span class="badge bg-primary">{{ $block->abbreviation }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $block->sort_order }}</td>
                                    <td>
                                        @if ($block->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @can('settings.au_master_data.edit')
                                            <a href="{{ route('settings.au.regional-blocks.edit', $block->id) }}"
                                                class="btn btn-sm btn-outline-warning">
                                                <i class="feather-edit"></i>
                                            </a>
                                        @endcan
                                        @can('settings.au_master_data.delete')
                                            <form action="{{ route('settings.au.regional-blocks.destroy', $block->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Delete this regional block?')">
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

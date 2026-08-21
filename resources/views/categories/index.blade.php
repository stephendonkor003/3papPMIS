@extends('layouts.app')
@section('title', 'Project Categories')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Project Categories</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">All Categories</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <a href="{{ route('categories.create') }}" class="btn btn-light-brand">
                            <i class="feather-plus-circle me-2"></i>
                            <span>Add Category</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="main-content">
                <div class="card">
                    <div class="card-body">
                        <x-data-table id="categoriesTable" :striped="true" :hover="true">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Created By</th>
                                    <th class="text-center no-sort no-export" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $index => $category)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><strong>{{ $category->name }}</strong></td>
                                        <td>{{ Str::limit($category->description, 80) }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $category->creator->name ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <x-table-actions
                                                :editRoute="route('categories.edit', $category->id)"
                                                :deleteRoute="route('categories.destroy', $category->id)"
                                                deleteMessage="Are you sure you want to delete this category?"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No categories found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </x-data-table>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

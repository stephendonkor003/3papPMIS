@extends('layouts.app')
@section('title', 'Think Datasets')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Think Datasets</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">All Records</li>
                    </ul>
                </div>
            </div>

            <div class="main-content">
                <div class="card">
                    <div class="card-body table-responsive">
                        <a href="{{ route('think-datasets.create') }}" class="btn btn-sm btn-primary mb-3">Add New
                            Dataset</a>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Think Tank</th>
                                    <th>Country</th>
                                    <th>Email</th>
                                    <th>Validated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($datasets as $index => $dataset)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $dataset->tt_name_en }}</td>
                                        <td>{{ $dataset->country }}</td>
                                        <td>{{ $dataset->g_email }}</td>
                                        <td>{{ $dataset->is_validated }}</td>
                                        <td>
                                            <a href="{{ route('think-datasets.show', $dataset->id) }}"
                                                class="btn btn-sm btn-outline-primary">View</a>
                                            <a href="{{ route('think-datasets.edit', $dataset->id) }}"
                                                class="btn btn-sm btn-outline-secondary">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $datasets->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

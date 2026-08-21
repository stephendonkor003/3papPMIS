@extends('layouts.app')
@section('title', 'Evaluation Committees')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Evaluation Committees</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">All Committees</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <a href="{{ route('committees.create') }}" class="btn btn-light-brand">
                            <i class="feather-plus-circle me-2"></i>
                            <span>Add Committee</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="main-content">
                <div class="card">
                    <div class="card-body table-responsive">
                        <table class="table table-hover" style="width:100%" id="proposalList1">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Committee Name</th>
                                    <th>Project</th>
                                    <th>Members Count</th>
                                    <th>Chairperson</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($committees as $index => $committee)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $committee->name }}</td>
                                        <td>{{ $committee->project->title ?? 'N/A' }}</td>
                                        <td>{{ $committee->members->count() }}</td>
                                        <td>{{ $committee->chairperson->name ?? 'Not Assigned' }}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('committees.edit', $committee->id) }}"
                                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form action="{{ route('committees.destroy', $committee->id) }}"
                                                    method="POST" onsubmit="return confirm('Delete this committee?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No committee records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="m-3">
                            {{ $committees->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@extends('layouts.app')
@section('title', 'Committee Members')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Committee Members</h5>
                <a href="{{ route('committee-members.create') }}" class="btn btn-primary">Add Member</a>
            </div>

            @if (session('success'))
                <div class="alert alert-success mt-3">{{ session('success') }}</div>
            @endif
            <div class="main-content">
                <div class="card">
                    <div class="card-body">

                        <div class="table-responsive mt-4">
                            <table class="table table-bordered table-hover" style="width:100%" id="proposalList1">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>User</th>
                                        <th>Committee</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($members as $index => $member)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $member->user->name ?? 'N/A' }}</td>
                                            <td>{{ $member->committee->name ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('committee-members.show', $member->id) }}"
                                                    class="btn btn-sm btn-info">View</a>
                                                <form action="{{ route('committee-members.destroy', $member->id) }}"
                                                    method="POST" style="display:inline-block;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Remove this member?')">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if ($members->isEmpty())
                                        <tr>
                                            <td colspan="4" class="text-center">No members found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

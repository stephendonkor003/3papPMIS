@extends('layouts.app')
@section('title', 'View Member')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <h5>Member Details</h5>
            </div>
            <div class="main-content">
                <div class="card">
                    <div class="card-body">
                        <div class="card mt-4">
                            <div class="card-body">
                                <h6><strong>User:</strong> {{ $committeeMember->user->name ?? 'N/A' }}</h6>
                                <h6><strong>Email:</strong> {{ $committeeMember->user->email ?? 'N/A' }}</h6>
                                <h6><strong>Committee:</strong> {{ $committeeMember->committee->name ?? 'N/A' }}</h6>
                                <h6><strong>Added On:</strong> {{ $committeeMember->created_at->format('F d, Y') }}</h6>
                            </div>
                        </div>

                        <a href="{{ route('committee-members.index') }}" class="btn btn-secondary mt-3">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

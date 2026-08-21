@extends('layouts.app')
@section('title', 'User Details')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">

            <!-- Page Header -->
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">User Details</h4>
                    <p class="text-muted mb-0">Information about this user.</p>
                </div>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left-circle me-1"></i> Back to Users
                </a>
            </div>

            <!-- User Card -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold text-primary">{{ $user->name }}</h5>
                    <p class="mb-2"><strong>Email:</strong> {{ $user->email }}</p>
                    <p class="mb-2"><strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $user->user_type)) }}</p>
                    <p class="mb-0"><strong>Created:</strong> {{ $user->created_at->format('d M, Y H:i') }}</p>
                    <p class="mb-0"><strong>Last Updated:</strong> {{ $user->updated_at->format('d M, Y H:i') }}</p>
                </div>
            </div>

        </div>
    </main>
@endsection

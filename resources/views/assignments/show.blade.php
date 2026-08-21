@extends('layouts.app')
@section('title', 'Assignment Details')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <h4>Assignment Details</h4>
                <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left-circle"></i> Back to Assignments
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Applicant Information</h5>
                    <p><strong>Think Tank:</strong> {{ $assignment->applicant->think_tank_name ?? 'N/A' }}</p>
                    <p><strong>Country:</strong> {{ $assignment->applicant->country ?? 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ $assignment->applicant->email ?? 'N/A' }}</p>

                    <hr>
                    <h5 class="mb-3">Evaluator Information</h5>
                    <p><strong>Name:</strong> {{ $assignment->evaluator->name ?? 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ $assignment->evaluator->email ?? 'N/A' }}</p>

                    <hr>
                    <h5 class="mb-3">Assignment Details</h5>
                    <p><strong>Role:</strong> {{ $assignment->role ?? '-' }}</p>
                    <p><strong>Assigned On:</strong> {{ $assignment->created_at->format('d M, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </main>
@endsection

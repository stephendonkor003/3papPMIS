@extends('layouts.app')
@section('title', 'Assign Financial Evaluators')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header d-flex justify-content-between align-items-center mb-3">
                <h5>Assign Financial Evaluators</h5>
            </div>

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Assignment Form --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white fw-bold">
                    New Assignment
                </div>
                <div class="card-body">
                    <form action="{{ route('financial.assign.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            {{-- Applicant --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Applicant</label>
                                <select name="applicant_id" class="form-select" required>
                                    <option value="">-- Select Applicant --</option>
                                    @foreach ($applicants as $applicant)
                                        <option value="{{ $applicant->id }}">
                                            {{ $applicant->think_tank_name }} ({{ $applicant->country }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('applicant_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Evaluator --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Financial Evaluator</label>
                                <select name="evaluator_id" class="form-select" required>
                                    <option value="">-- Select Evaluator --</option>
                                    @foreach ($evaluators as $eval)
                                        <option value="{{ $eval->id }}">{{ $eval->name }} ({{ $eval->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('evaluator_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-12 mt-3 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-user-check"></i> Assign Evaluator
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Already Assigned List --}}
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <strong>Current Financial Assignments</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Applicant</th>
                                <th>Evaluator</th>
                                <th>Assigned On</th>
                                <th class="text-center" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assign)
                                <tr>
                                    <td>{{ $assign->think_tank_name }}</td>
                                    <td>{{ $assign->evaluator_name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($assign->created_at)->format('d M Y, H:i') }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('financial.assign.delete', $assign->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to remove this assignment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="feather-trash-2"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No assignments yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
@endsection

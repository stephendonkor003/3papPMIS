@extends('layouts.app')
@section('title', 'Site Visit Teams Management')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">

            <!-- ======= Header ======= -->
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 text-primary fw-bold"><i class="bi bi-people me-2"></i>Site Visit Teams Management</h4>
                    <p class="text-muted mb-0">Create evaluation teams, manage members, and assign consortia for site visits.
                    </p>
                </div>
            </div>

            <!-- ======= Create New Team ======= -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-gradient bg-primary text-white fw-bold">
                    <i class="bi bi-plus-circle me-2"></i>Create New Evaluation Team
                </div>
                <div class="card-body">
                    <form action="{{ route('sitevisit.create.team') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Team Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter team name"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Team Leader</label>
                            <select name="leader_id" class="form-select" required>
                                <option value="">-- Choose Leader --</option>
                                @foreach ($availableEvaluators as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @if ($availableEvaluators->isEmpty())
                                <small class="text-danger">All evaluators are currently assigned to teams.</small>
                            @endif
                        </div>
                        <div class="col-12">
                            <button class="btn btn-success"><i class="bi bi-plus-circle me-1"></i> Create Team</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ======= Teams List ======= -->
            @forelse ($teams as $team)
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-primary fw-bold">
                            {{ $team->name }} — Leader:
                            <span class="text-dark">{{ $team->leader->name ?? 'N/A' }}</span>
                        </h6>
                        <span class="badge bg-secondary">{{ $team->members->count() }} Members</span>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- Add Member -->
                            <div class="col-md-6 mb-3">
                                <form action="{{ route('sitevisit.add.member', $team->id) }}" method="POST"
                                    class="d-flex gap-2">
                                    @csrf
                                    <select name="user_id" class="form-select" required>
                                        <option value="">Select Evaluator</option>
                                        @foreach ($availableEvaluators as $evaluator)
                                            <option value="{{ $evaluator->id }}">{{ $evaluator->name }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-person-plus"></i>
                                    </button>
                                </form>
                                @if ($availableEvaluators->isEmpty())
                                    <small class="text-danger d-block mt-1">No available evaluators to add.</small>
                                @endif
                            </div>

                            <!-- Assign Consortium -->
                            <div class="col-md-6 mb-3">
                                <form action="{{ route('sitevisit.assign.consortium', $team->id) }}" method="POST"
                                    class="d-flex gap-2">
                                    @csrf
                                    <select name="consortium_id" class="form-select" required>
                                        <option value="">Assign Consortium</option>
                                        @foreach (\App\Models\Applicant::all() as $applicant)
                                            <option value="{{ $applicant->id }}">
                                                {{ $applicant->think_tank_name ?? 'Unnamed Consortium' }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-link-45deg"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- ===== Team Members ===== -->
                        <h6 class="fw-bold mt-4 mb-2 text-dark"><i class="bi bi-people-fill me-1 text-primary"></i>Team
                            Members</h6>
                        <ul class="list-group list-group-flush">
                            @forelse($team->members as $member)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-person-circle text-primary me-1"></i>
                                        {{ $member->user->name ?? 'N/A' }}
                                        <small class="text-muted">({{ ucfirst($member->role) }})</small>
                                    </div>

                                    {{-- Disable remove button for the leader --}}
                                    @if ($member->role !== 'leader')
                                        <a href="{{ route('sitevisit.remove.member', $member->id) }}"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to remove {{ $member->user->name }} from this team?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    @else
                                        <span class="badge bg-info text-dark">Leader</span>
                                    @endif
                                </li>
                            @empty
                                <li class="list-group-item text-muted">No members added yet.</li>
                            @endforelse
                        </ul>

                        <!-- ===== Assigned Consortia ===== -->
                        <h6 class="fw-bold mt-4 mb-2 text-dark"><i class="bi bi-diagram-3 me-1 text-success"></i>Assigned
                            Consortia</h6>
                        <ul class="list-group list-group-flush">
                            @forelse($team->consortia as $c)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $c->consortium->think_tank_name ?? 'N/A' }}</span>
                                    <span class="badge bg-{{ $c->status == 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($c->status) }}
                                    </span>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">No consortia assigned yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @empty
                <div class="alert alert-warning">No teams created yet. Create one above to get started.</div>
            @endforelse
        </div>
    </main>
@endsection

@extends('layouts.app')

@section('content')
    <div class="nxl-container">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="fw-bold text-dark">Budget Allocations Management</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProgramModal">
                <i class="bi bi-plus-circle me-1"></i> New Program
            </button>
        </div>

        {{-- Success / Error Messages --}}
        @if (session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger mt-3">{{ $errors->first() }}</div>
        @endif

        {{-- PROGRAM TABLE --}}
        <div class="card mt-4 shadow-sm border-0">
            <div class="card-body">
                <h5 class="mb-3">Programs Overview</h5>
                <table class="table table-bordered align-middle" id="programsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Program ID</th>
                            <th>Name</th>
                            <th>Sector</th>
                            <th>Total Budget (USD)</th>
                            <th>Years</th>
                            <th>Projects</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($programs as $program)
                            <tr>
                                <td><strong>{{ $program->program_id }}</strong></td>
                                <td>{{ $program->name }}</td>
                                <td>{{ $program->sector->name ?? '-' }}</td>
                                <td>{{ number_format($program->total_budget, 2) }}</td>
                                <td>{{ $program->years }}</td>
                                <td>{{ $program->projects->count() }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#createProjectModal" data-program-id="{{ $program->id }}">
                                        Add Project
                                    </button>
                                    <a href="{{ route('budget.show', $program->id) }}"
                                        class="btn btn-sm btn-outline-success">
                                        View Details
                                    </a>
                                </td>
                            </tr>

                            {{-- PROJECT LIST --}}
                            @foreach ($program->projects as $project)
                                <tr class="bg-light">
                                    <td class="ps-4">↳ {{ $project->project_id }}</td>
                                    <td colspan="2">{{ $project->name }}</td>
                                    <td>{{ number_format($project->total_budget, 2) }}</td>
                                    <td>{{ $project->years }}</td>
                                    <td>{{ $project->activities->count() }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                            data-bs-target="#createActivityModal" data-project-id="{{ $project->id }}">
                                            Add Activity
                                        </button>
                                    </td>
                                </tr>

                                {{-- ACTIVITY LIST --}}
                                @foreach ($project->activities as $activity)
                                    <tr>
                                        <td class="ps-5">↳ {{ $activity->activity_id }}</td>
                                        <td colspan="2">{{ $activity->name }}</td>
                                        <td>{{ number_format($activity->total_budget, 2) }}</td>
                                        <td>{{ $activity->years }}</td>
                                        <td>{{ $activity->subActivities->count() }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                                data-bs-target="#createSubActivityModal"
                                                data-activity-id="{{ $activity->id }}">
                                                Add Sub-Activity
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========================= MODALS ========================= --}}

    {{-- CREATE PROGRAM --}}
    <div class="modal fade" id="createProgramModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" action="{{ route('budget.program.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Sector</label>
                        <select class="form-select" name="sector_id" required>
                            <option value="">Select Sector</option>
                            @foreach ($sectors as $sector)
                                <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Program Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total Budget (USD)</label>
                        <input type="number" step="0.01" name="total_budget" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Number of Years</label>
                        <input type="number" name="years" min="1" max="10" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Program</button>
                </div>
            </form>
        </div>
    </div>

    {{-- CREATE PROJECT --}}
    <div class="modal fade" id="createProjectModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" action="{{ route('budget.project.store') }}">
                @csrf
                <input type="hidden" name="program_id" id="project_program_id">
                <div class="modal-header">
                    <h5 class="modal-title">Create Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Project Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Budget (USD)</label>
                            <input type="number" step="0.01" name="total_budget" id="project_budget"
                                class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Number of Years</label>
                            <input type="number" name="years" id="project_years" min="1" max="10"
                                class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Yearly Allocations</label>
                            <div id="yearAllocationFields"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <small class="me-auto text-muted">Ensure allocations total ≤ Total Budget</small>
                    <button type="submit" class="btn btn-primary">Save Project</button>
                </div>
            </form>
        </div>
    </div>

    {{-- CREATE ACTIVITY --}}
    <div class="modal fade" id="createActivityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" action="{{ route('budget.activity.store') }}">
                @csrf
                <input type="hidden" name="project_id" id="activity_project_id">
                <div class="modal-header">
                    <h5 class="modal-title">Create Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Activity Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total Budget</label>
                        <input type="number" name="total_budget" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Number of Years</label>
                        <input type="number" name="years" id="activity_years" min="1" max="10"
                            class="form-control" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Yearly Allocations</label>
                        <div id="activityAllocations"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Activity</button>
                </div>
            </form>
        </div>
    </div>
    {{-- CREATE SUB-ACTIVITY --}}
    <div class="modal fade" id="createSubActivityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" action="{{ route('budget.sub.store') }}">
                @csrf
                <input type="hidden" name="activity_id" id="sub_activity_id">
                <div class="modal-header">
                    <h5 class="modal-title">Create Sub-Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Sub-Activity Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total Budget</label>
                        <input type="number" name="total_budget" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Number of Years</label>
                        <input type="number" name="years" id="sub_years" min="1" max="10"
                            class="form-control" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Yearly Allocations</label>
                        <div id="subAllocations"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Sub-Activity</button>
                </div>
            </form>
        </div>
    </div>
@endsection


{{-- <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Pass IDs to modals dynamically
        const projectModal = document.getElementById('createProjectModal');
        projectModal.addEventListener('show.bs.modal', event => {
            document.getElementById('project_program_id').value = event.relatedTarget.getAttribute(
                'data-program-id');
        });

        const activityModal = document.getElementById('createActivityModal');
        activityModal.addEventListener('show.bs.modal', event => {
            document.getElementById('activity_project_id').value = event.relatedTarget.getAttribute(
                'data-project-id');
        });

        const subModal = document.getElementById('createSubActivityModal');
        subModal.addEventListener('show.bs.modal', event => {
            document.getElementById('sub_activity_id').value = event.relatedTarget.getAttribute(
                'data-activity-id');
        });

        // Dynamic yearly allocation fields
        const budgetInput = document.getElementById('project_budget');
        const yearsInput = document.getElementById('project_years');
        const yearAllocContainer = document.getElementById('yearAllocationFields');

        function renderYearFields() {
            yearAllocContainer.innerHTML = '';
            const years = parseInt(yearsInput.value || 0);
            if (years > 0) {
                for (let i = 1; i <= years; i++) {
                    const div = document.createElement('div');
                    div.className = 'mb-2';
                    div.innerHTML = `
                    <label>Year ${i}</label>
                    <input type="number" step="0.01" name="allocations[${new Date().getFullYear() + i - 1}]" class="form-control" placeholder="Enter allocation for year ${i}">
                `;
                    yearAllocContainer.appendChild(div);
                }
            }
        }

        yearsInput?.addEventListener('input', renderYearFields);
    });
</script> --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        /* =======================
           1️⃣ Pass IDs to Modals
        ======================== */
        const projectModal = document.getElementById('createProjectModal');
        projectModal.addEventListener('show.bs.modal', event => {
            document.getElementById('project_program_id').value = event.relatedTarget.getAttribute(
                'data-program-id');
        });

        const activityModal = document.getElementById('createActivityModal');
        activityModal.addEventListener('show.bs.modal', event => {
            document.getElementById('activity_project_id').value = event.relatedTarget.getAttribute(
                'data-project-id');
        });

        const subModal = document.getElementById('createSubActivityModal');
        subModal.addEventListener('show.bs.modal', event => {
            document.getElementById('sub_activity_id').value = event.relatedTarget.getAttribute(
                'data-activity-id');
        });

        /* =======================
           2️⃣ Dynamic Year Fields
        ======================== */
        function setupDynamicFields(yearInputId, containerId) {
            const yearInput = document.querySelector(`#${yearInputId}`);
            const container = document.querySelector(`#${containerId}`);

            if (!yearInput || !container) return;

            yearInput.addEventListener('input', () => {
                const years = parseInt(yearInput.value || 0);
                container.innerHTML = '';

                if (years > 0) {
                    for (let i = 1; i <= years; i++) {
                        const year = new Date().getFullYear() + (i - 1);
                        const field = document.createElement('div');
                        field.classList.add('mb-2');
                        field.innerHTML = `
                        <label>Year ${i} (${year})</label>
                        <input type="number" step="0.01" name="allocations[${year}]"
                               class="form-control" placeholder="Enter allocation for year ${i}">
                    `;
                        container.appendChild(field);
                    }
                }
            });
        }

        // Setup for all three modals
        setupDynamicFields('project_years', 'yearAllocationFields');
        setupDynamicFields('activity_years', 'activityAllocations');
        setupDynamicFields('sub_years', 'subAllocations');
    });
</script>

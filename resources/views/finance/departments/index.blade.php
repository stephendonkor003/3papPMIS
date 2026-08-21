@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">Departments</h4>
                <p class="text-muted mb-0">
                    Institutional units responsible for programs, funding, and execution
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#governanceDrawer" aria-controls="governanceDrawer">
                    <i class="feather-info me-1"></i> Governance Map
                </button>
                @can('finance.departments.create')
                    <a href="{{ route('finance.departments.create') }}" class="btn btn-primary">
                        <i class="feather-plus-circle me-1"></i> New Department
                    </a>
                @endcan
            </div>
        </div>

        {{-- ================= SEARCH ================= --}}
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">

                <div class="row mb-3 align-items-center">
                    <div class="col-md-6">
                        <input type="text" id="departmentSearch" class="form-control"
                            placeholder="Search departments, codes, heads, status…">
                    </div>

                    <div class="col-md-6 text-end">
                        <span class="text-muted small">
                            Total Departments:
                            {{-- <strong>{{ $departments->total() }}</strong> --}}
                        </span>
                    </div>
                </div>

                {{-- ================= TABLE ================= --}}
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="departmentsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="100">Code</th>
                                <th>Department</th>
                                <th width="220">Department Head</th>
                                <th width="120">Status</th>
                                <th width="140" class="text-center">Programs</th>
                                <th width="160" class="text-center">Funded</th>
                                <th width="180" class="text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($departments as $department)
                                <tr>

                                    {{-- CODE --}}
                                    <td class="fw-semibold">
                                        {{ $department->code }}
                                    </td>

                                    {{-- DEPARTMENT --}}
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $department->name }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ \Illuminate\Support\Str::limit($department->description, 70) }}
                                        </div>
                                    </td>

                                    {{-- HEAD --}}
                                    <td>
                                        @if ($department->head)
                                            <div class="fw-semibold">
                                                {{ $department->head->name }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $department->head->email }}
                                            </div>
                                        @else
                                            <span class="text-danger small fw-semibold">
                                                Not Assigned
                                            </span>
                                        @endif
                                    </td>

                                    {{-- STATUS --}}
                                    <td>
                                        <span
                                            class="badge {{ $department->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($department->status) }}
                                        </span>
                                    </td>

                                    {{-- PROGRAMS --}}
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">
                                            {{ $department->programs_count }}
                                        </span>
                                    </td>

                                    {{-- FUNDED --}}
                                    <td class="text-center">
                                        <span class="badge bg-primary">
                                            {{ $department->program_fundings_count }}
                                        </span>
                                    </td>

                                    {{-- ACTIONS (INLINE & CLEAN) --}}
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            @can('finance.departments.assign_head')
                                                <button class="btn btn-sm btn-outline-primary" title="Assign Department Head"
                                                    data-bs-toggle="modal" data-bs-target="#assignHeadModal"
                                                    data-id="{{ $department->id }}" data-name="{{ $department->name }}"
                                                    data-head="{{ $department->head_user_id ?? '' }}">
                                                    <i class="feather-user-check"></i>
                                                </button>
                                            @endcan

                                            <a href="{{ route('finance.departments.show', $department) }}"
                                                class="btn btn-sm btn-outline-info" title="View">
                                                <i class="feather-eye"></i>
                                            </a>
                                            @can('finance.departments.edit')
                                                <a href="{{ route('finance.departments.edit', $department) }}"
                                                    class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="feather-edit"></i>
                                                </a>
                                            @endcan

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No departments found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ================= PAGINATION ================= --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $departments->links() }}
                </div>

            </div>
        </div>
    </div>

    {{-- ================= ASSIGN HEAD MODAL ================= --}}
    <div class="modal fade" id="assignHeadModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Assign Department Head</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="assignHeadForm">
                    @csrf

                    <div class="modal-body">

                        <input type="hidden" id="departmentId">

                        <div class="mb-3">
                            <label class="fw-semibold">Department</label>
                            <input type="text" id="departmentName" class="form-control" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="fw-semibold">Select Employee</label>
                            <select class="form-select" name="head_user_id" required>
                                <option value="">-- Select Employee --</option>
                                @foreach (\App\Models\User::where('user_type', 'employee')->orderBy('name')->get() as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Assign Head</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- ================= JS ================= --}}
    <script>
        /* SEARCH */
        document.getElementById('departmentSearch').addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            document.querySelectorAll('#departmentsTable tbody tr').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
            });
        });

        /* MODAL BINDING */
        const modal = document.getElementById('assignHeadModal');
        const form = document.getElementById('assignHeadForm');

        modal.addEventListener('show.bs.modal', e => {
            const btn = e.relatedTarget;
            document.getElementById('departmentId').value = btn.dataset.id;
            document.getElementById('departmentName').value = btn.dataset.name;
            if (btn.dataset.head) form.head_user_id.value = btn.dataset.head;
        });

        /* SUBMIT */
        form.addEventListener('submit', e => {
            e.preventDefault();
            const id = document.getElementById('departmentId').value;

            fetch(`/finance/departments/${id}/assign-head`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: new FormData(form)
            }).then(() => location.reload());
        });
    </script>

    {{-- ================= GOVERNANCE DRAWER ================= --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="governanceDrawer" aria-labelledby="governanceDrawerLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="governanceDrawerLabel">Internal Work Structure (AUC Level)</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <p class="text-muted mb-4">
                The day-to-day work of the <strong>African Union Commission (AUC)</strong> is organized like a
                <strong>large civil service</strong>, similar to a government ministry system.
            </p>

            <h6 class="fw-bold">1. Top Leadership Layer</h6>
            <div class="mb-3">
                <p class="fw-semibold mb-1">Chairperson of the Commission</p>
                <ul class="mb-2">
                    <li>Overall <strong>chief executive</strong></li>
                    <li>Political leadership + external representation</li>
                    <li>Final approval on major policy and administrative decisions</li>
                </ul>

                <p class="fw-semibold mb-1">Deputy Chairperson</p>
                <ul class="mb-2">
                    <li>Oversees <strong>administration, finance, and HR</strong></li>
                    <li>Supervises internal services</li>
                </ul>

                <p class="fw-semibold mb-1">Commissioners (Portfolio Heads)</p>
                <ul class="mb-0">
                    <li>Each Commissioner leads <strong>one or more departments</strong></li>
                    <li>Comparable to <strong>ministers</strong></li>
                </ul>
            </div>

            <h6 class="fw-bold">2. Departments (Core Working Units)</h6>
            <p class="mb-2">Each <strong>Department</strong> handles a broad policy area and is divided internally into
                <strong>Directorates → Divisions → Units</strong>.
            </p>
            <p class="fw-semibold mb-1">Major AUC Departments include:</p>
            <ul class="mb-3">
                <li><strong>Political Affairs, Peace &amp; Security</strong></li>
                <li><strong>Economic Development, Trade, Industry &amp; Mining</strong></li>
                <li><strong>Education, Science, Technology &amp; Innovation</strong></li>
                <li><strong>Health, Humanitarian Affairs &amp; Social Development</strong></li>
                <li><strong>Infrastructure &amp; Energy</strong></li>
                <li><strong>Agriculture, Rural Development, Blue Economy</strong></li>
                <li><strong>Women, Gender &amp; Youth</strong></li>
                <li><strong>Legal Counsel</strong></li>
                <li><strong>Communications &amp; Information</strong></li>
                <li><strong>Administration &amp; Human Resources</strong></li>
                <li><strong>Finance</strong></li>
                <li><strong>Strategic Planning &amp; Delivery</strong></li>
            </ul>

            <h6 class="fw-bold">3. Directorate Level (Where Most Policy Work Happens)</h6>
            <p class="mb-2"><strong>Department → Directorates</strong></p>
            <p class="mb-1 fw-semibold">Example (simplified):</p>
            <ul class="mb-2">
                <li>Department of Political Affairs</li>
                <li>Directorate of Governance</li>
                <li>Directorate of Elections</li>
                <li>Directorate of Democracy &amp; Constitutionalism</li>
            </ul>
            <p class="fw-semibold mb-1">Directorate roles:</p>
            <ul class="mb-3">
                <li>Draft <strong>policies, frameworks, and action plans</strong></li>
                <li>Coordinate with <strong>member states</strong></li>
                <li>Prepare reports for <strong>STCs, Executive Council, Assembly</strong></li>
                <li>Manage programs and projects</li>
            </ul>

            <h6 class="fw-bold">4. Divisions &amp; Units (Operational Layer)</h6>
            <p class="fw-semibold mb-1">Divisions</p>
            <ul class="mb-2">
                <li>Subsections of a directorate</li>
                <li>Focus on <strong>specific themes or regions</strong></li>
            </ul>
            <p class="fw-semibold mb-1">Units</p>
            <ul class="mb-3">
                <li>Small, technical teams</li>
                <li>Handle research &amp; drafting, program implementation, monitoring &amp; evaluation, data
                    collection</li>
            </ul>
            <p class="text-muted mb-3">This is where <strong>staff officers and experts</strong> work daily.</p>

            <h6 class="fw-bold">5. Support &amp; Control Functions (Horizontal)</h6>
            <p class="mb-1">These cut across <strong>all departments</strong>:</p>
            <ul class="mb-3">
                <li><strong>HR &amp; Administration</strong> → recruitment, contracts</li>
                <li><strong>Finance</strong> → budgeting, procurement, audits</li>
                <li><strong>Legal Counsel</strong> → treaty review, compliance</li>
                <li><strong>Internal Audit</strong> → financial &amp; performance checks</li>
                <li><strong>Strategic Planning &amp; Delivery Unit</strong> → KPIs, results tracking</li>
            </ul>

            <h6 class="fw-bold">Typical Internal Workflow (Policy Example)</h6>
            <p class="mb-2">Let’s say the AU wants a <strong>new youth employment framework</strong>:</p>
            <ol class="mb-0">
                <li><strong>Unit</strong> drafts concept note</li>
                <li><strong>Division</strong> refines technical details</li>
                <li><strong>Directorate</strong> consolidates &amp; consults stakeholders</li>
                <li><strong>Department</strong> clears politically &amp; administratively</li>
                <li><strong>Commissioner</strong> approves draft</li>
                <li><strong>Chairperson’s Office</strong> validates</li>
                <li>Sent to <strong>STC → Executive Council → Assembly</strong></li>
                <li>After adoption → back to <strong>Departments for implementation</strong></li>
            </ol>
        </div>
    </div>
@endsection

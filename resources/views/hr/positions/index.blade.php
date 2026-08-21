@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header mb-4">
            <div class="d-flex flex-column align-items-start gap-2">

                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="feather-briefcase text-primary me-2"></i>
                        HR Positions
                    </h4>
                    <p class="text-muted mb-0">
                        Define and manage job roles available for recruitment and workforce planning
                    </p>
                </div>
                @can('hrm.positions.create')
                    <button class="btn btn-primary d-inline-flex align-items-center" data-bs-toggle="modal"
                        data-bs-target="#addPositionModal">
                        <i class="feather-plus me-2"></i>
                        New Position
                    </button>
                @endcan

            </div>
        </div>

        {{-- ================= FEEDBACK ================= --}}
        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center mb-4">
                <i class="feather-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- ================= POSITIONS TABLE ================= --}}
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <x-data-table id="positionsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Position</th>
                            <th>Resource</th>
                            <th>Governance Node</th>
                            <th class="text-center">Employment Type</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="100">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($positions as $position)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold text-dark">
                                        {{ $position->title }}
                                    </div>
                                    <small class="text-muted">
                                        Created {{ $position->created_at->format('d M Y') }}
                                    </small>
                                </td>

                                <td>
                                    {{ $position->resource->name ?? '—' }}
                                </td>

                                <td>
                                    @if($position->governanceNode)
                                        <span class="badge bg-primary-subtle text-primary">
                                            <i class="feather-layers me-1"></i>
                                            {{ $position->governanceNode->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-info-subtle text-info px-3 py-1">
                                        {{ ucfirst($position->employment_type) }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    @if($position->status === 'active')
                                        <span class="badge bg-success-subtle text-success px-3 py-1">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-3 py-1">Inactive</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                            <i class="feather-more-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @can('hrm.positions.edit')
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#editPositionModal"
                                                       data-id="{{ $position->id }}"
                                                       data-title="{{ $position->title }}"
                                                       data-resource-id="{{ $position->resource_id }}"
                                                       data-employment-type="{{ $position->employment_type }}"
                                                       data-description="{{ $position->description }}"
                                                       data-status="{{ $position->status }}">
                                                        <i class="feather-edit-2 me-2"></i> Edit
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('hrm.positions.delete')
                                                @if(!$position->vacancies()->exists() && !$position->employees()->exists())
                                                    <li>
                                                        <form action="{{ route('hr.positions.destroy', $position) }}" method="POST" class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to delete this position?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="feather-trash-2 me-2"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                @else
                                                    <li>
                                                        <span class="dropdown-item text-muted" title="Has vacancies or employees">
                                                            <i class="feather-lock me-2"></i> Cannot Delete
                                                        </span>
                                                    </li>
                                                @endif
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-data-table>
            </div>
        </div>

    </div>

    {{-- ================= ADD POSITION MODAL ================= --}}
    <div class="modal fade" id="addPositionModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <form method="POST" action="{{ route('hr.positions.store') }}" class="w-100">
                @csrf

                <div class="modal-content border-0 shadow">

                    <div class="modal-header">
                        <h5 class="fw-bold mb-0">
                            <i class="feather-briefcase text-primary me-2"></i>
                            Create HR Position
                        </h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-4">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">HR Resource *</label>
                                <select name="resource_id" class="form-select" required>
                                    <option value="">Select Resource</option>
                                    @foreach (\App\Models\Resource::where('is_human_resource', 1)->get() as $resource)
                                        <option value="{{ $resource->id }}">
                                            {{ $resource->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Employment Type *</label>
                                <select name="employment_type" class="form-select" required>
                                    <option value="permanent">Permanent</option>
                                    <option value="contract">Contract</option>
                                    <option value="temporary">Temporary</option>
                                    <option value="consultant">Consultant</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Position Title *</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Position Description</label>
                                <textarea name="description" id="positionDescription" class="form-control"></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-light" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button class="btn btn-primary">
                            <i class="feather-save me-2"></i>
                            Save Position
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ================= EDIT POSITION MODAL ================= --}}
    <div class="modal fade" id="editPositionModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <form method="POST" id="editPositionForm" class="w-100">
                @csrf
                @method('PUT')

                <div class="modal-content border-0 shadow">

                    <div class="modal-header">
                        <h5 class="fw-bold mb-0">
                            <i class="feather-edit text-primary me-2"></i>
                            Edit Position
                        </h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-4">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">HR Resource *</label>
                                <select name="resource_id" id="edit_resource_id" class="form-select" required>
                                    <option value="">Select Resource</option>
                                    @foreach (\App\Models\Resource::where('is_human_resource', 1)->get() as $resource)
                                        <option value="{{ $resource->id }}">
                                            {{ $resource->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Employment Type *</label>
                                <select name="employment_type" id="edit_employment_type" class="form-select" required>
                                    <option value="permanent">Permanent</option>
                                    <option value="contract">Contract</option>
                                    <option value="temporary">Temporary</option>
                                    <option value="consultant">Consultant</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Position Title *</label>
                                <input type="text" name="title" id="edit_title" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Position Description</label>
                                <textarea name="description" id="edit_description" class="form-control" rows="4"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status *</label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-light" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button class="btn btn-primary">
                            <i class="feather-save me-2"></i>
                            Update Position
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

@endsection

@push('styles')
<style>
    .ck-editor__editable {
        min-height: 220px;
        max-height: 320px;
        font-size: 14px;
        line-height: 1.6;
    }
    .ck-content {
        font-family: Inter, system-ui, sans-serif;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let editorLoaded = false;

        document.getElementById('addPositionModal')
            .addEventListener('shown.bs.modal', function() {
                if (!editorLoaded) {
                    ClassicEditor.create(document.querySelector('#positionDescription'), {
                        toolbar: [
                            'heading', '|',
                            'bold', 'italic', 'underline', '|',
                            'bulletedList', 'numberedList', '|',
                            'link', 'blockQuote', 'insertTable', '|',
                            'undo', 'redo'
                        ]
                    });
                    editorLoaded = true;
                }
            });

        // Edit modal population
        const editModal = document.getElementById('editPositionModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const title = button.getAttribute('data-title');
            const resourceId = button.getAttribute('data-resource-id');
            const employmentType = button.getAttribute('data-employment-type');
            const description = button.getAttribute('data-description');
            const status = button.getAttribute('data-status');

            // Update form action
            document.getElementById('editPositionForm').action = '{{ url("hr/positions") }}/' + id;

            // Fill form fields
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_resource_id').value = resourceId;
            document.getElementById('edit_employment_type').value = employmentType;
            document.getElementById('edit_description').value = description || '';
            document.getElementById('edit_status').value = status || 'active';
        });
    });
</script>
@endpush

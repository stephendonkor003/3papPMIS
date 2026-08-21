@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="feather-box text-primary me-1"></i>
                    Resources
                </h4>
                <p class="text-muted mb-0">
                    Resource items committed against approved budgets
                </p>
            </div>

            <div class="d-flex align-items-center gap-2">
                @can('finance.resources.create')
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                        <i class="feather-plus me-1"></i>
                        Add Resource
                    </button>
                @endcan
            </div>
        </div>

        {{-- ================= TABLE CARD ================= --}}
        <div class="card mt-4 shadow-sm border-0">
            <div class="card-body">

                <x-data-table id="resourcesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Resource Name</th>
                            <th>Category</th>
                            <th>Governance Node</th>
                            <th class="text-center">Type</th>
                            <th class="text-center">Commitments</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="100">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($resources as $resource)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-medium">
                                        <i class="feather-package text-muted me-1"></i>
                                        {{ $resource->name }}
                                    </div>
                                    @if($resource->reference_code)
                                        <small class="text-muted">Code: {{ $resource->reference_code }}</small>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge bg-info-subtle text-info">
                                        {{ $resource->category->name ?? 'N/A' }}
                                    </span>
                                </td>

                                <td>
                                    @if($resource->governanceNode)
                                        <span class="badge bg-primary-subtle text-primary">
                                            <i class="feather-layers me-1"></i>
                                            {{ $resource->governanceNode->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">System-wide</span>
                                    @endif
                                </td>

                                {{-- RESOURCE TYPE INDICATOR --}}
                                <td class="text-center">
                                    @if ($resource->is_human_resource ?? false)
                                        <span class="badge bg-warning-subtle text-warning">
                                            <i class="feather-users me-1"></i>
                                            Human Resource
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            <i class="feather-box me-1"></i>
                                            Non-Human
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $resource->commitments_count ?? $resource->commitments()->count() }}</span>
                                </td>

                                <td class="text-center">
                                    @if($resource->status === 'active')
                                        <span class="badge bg-success-subtle text-success px-3">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-3">Inactive</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                            <i class="feather-more-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @can('finance.resources.edit')
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#editResourceModal"
                                                       data-id="{{ $resource->id }}"
                                                       data-name="{{ $resource->name }}"
                                                       data-category-id="{{ $resource->resource_category_id }}"
                                                       data-reference-code="{{ $resource->reference_code }}"
                                                       data-description="{{ $resource->description }}"
                                                       data-is-human="{{ $resource->is_human_resource ? '1' : '0' }}"
                                                       data-status="{{ $resource->status }}">
                                                        <i class="feather-edit-2 me-2"></i> Edit
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('finance.resources.delete')
                                                @if(!$resource->commitments()->exists() && !$resource->procurements()->exists())
                                                    <li>
                                                        <form action="{{ route('finance.resources.items.destroy', $resource) }}" method="POST" class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to delete this resource?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="feather-trash-2 me-2"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                @else
                                                    <li>
                                                        <span class="dropdown-item text-muted" title="Has commitments or procurements">
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

    {{-- ================= ADD RESOURCE MODAL ================= --}}
    <div class="modal fade" id="addResourceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('finance.resources.items.store') }}" class="w-100">
                @csrf

                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="fw-bold mb-0">
                            <i class="feather-box text-primary me-1"></i>
                            Add Resource
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        {{-- CATEGORY --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium">
                                Resource Category <span class="text-danger">*</span>
                            </label>

                            <select name="resource_category_id" class="form-select" id="resourceCategory" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- RESOURCE NAME --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium">
                                Resource Name <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="name" class="form-control"
                                placeholder="e.g. Vehicles, Consultants, Engineers" required>
                        </div>

                        {{-- REFERENCE CODE --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium">Reference Code</label>
                            <input type="text" name="reference_code" class="form-control"
                                placeholder="e.g. RES-001">
                        </div>

                        {{-- DESCRIPTION --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Optional description..."></textarea>
                        </div>

                        {{-- HUMAN RESOURCE INDICATOR --}}
                        <div class="mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_human_resource"
                                    id="isHumanResource" value="1">
                                <label class="form-check-label fw-medium" for="isHumanResource">
                                    This resource involves human employment / staffing
                                </label>
                            </div>

                            <small class="text-muted">
                                Enable this for jobs, consultants, temporary staff, or any employment-related resource.
                                This will allow public competitive bidding and vacancy features.
                            </small>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-save me-1"></i> Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= EDIT RESOURCE MODAL ================= --}}
    <div class="modal fade" id="editResourceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="editResourceForm" class="w-100">
                @csrf
                @method('PUT')

                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="fw-bold mb-0">
                            <i class="feather-edit text-primary me-1"></i>
                            Edit Resource
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        {{-- CATEGORY --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium">
                                Resource Category <span class="text-danger">*</span>
                            </label>
                            <select name="resource_category_id" id="edit_category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- RESOURCE NAME --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium">
                                Resource Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>

                        {{-- REFERENCE CODE --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium">Reference Code</label>
                            <input type="text" name="reference_code" id="edit_reference_code" class="form-control">
                        </div>

                        {{-- DESCRIPTION --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                        </div>

                        {{-- HUMAN RESOURCE INDICATOR --}}
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_human_resource"
                                    id="edit_is_human_resource" value="1">
                                <label class="form-check-label fw-medium" for="edit_is_human_resource">
                                    This resource involves human employment / staffing
                                </label>
                            </div>
                        </div>

                        {{-- STATUS --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-save me-1"></i> Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-detect HR by category name on add modal
        document.getElementById('resourceCategory').addEventListener('change', function() {
            const text = this.options[this.selectedIndex].text.toLowerCase();
            const keywords = ['human', 'staff', 'consult', 'personnel', 'employment'];
            document.getElementById('isHumanResource').checked =
                keywords.some(word => text.includes(word));
        });

        // Edit modal population
        const editModal = document.getElementById('editResourceModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const categoryId = button.getAttribute('data-category-id');
            const referenceCode = button.getAttribute('data-reference-code');
            const description = button.getAttribute('data-description');
            const isHuman = button.getAttribute('data-is-human');
            const status = button.getAttribute('data-status');

            // Update form action
            document.getElementById('editResourceForm').action = '{{ url("finance/resources/items") }}/' + id;

            // Fill form fields
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_category_id').value = categoryId;
            document.getElementById('edit_reference_code').value = referenceCode || '';
            document.getElementById('edit_description').value = description || '';
            document.getElementById('edit_is_human_resource').checked = isHuman === '1';
            document.getElementById('edit_status').value = status || 'active';
        });
    });
</script>
@endpush

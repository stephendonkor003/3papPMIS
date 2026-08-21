@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="feather-folder text-primary me-1"></i>
                    Resource Categories
                </h4>
                <p class="text-muted mb-0">
                    Organize and manage resource classifications
                </p>
            </div>

            <div class="d-flex align-items-center gap-2">
                @can('finance.resources.create')
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="feather-plus me-1"></i>
                        Add Category
                    </button>
                @endcan
            </div>
        </div>

        {{-- ================= TABLE CARD ================= --}}
        <div class="card mt-4 shadow-sm border-0">
            <div class="card-body">

                <x-data-table id="categoriesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Category Name</th>
                            <th>Description</th>
                            <th>Governance Node</th>
                            <th class="text-center">Resources</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="100">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td class="ps-4 fw-medium">
                                    <i class="feather-tag text-muted me-1"></i>
                                    {{ $category->name }}
                                </td>

                                <td>
                                    <span class="text-muted">{{ $category->description ?? '-' }}</span>
                                </td>

                                <td>
                                    @if($category->governanceNode)
                                        <span class="badge bg-info-subtle text-info">
                                            <i class="feather-layers me-1"></i>
                                            {{ $category->governanceNode->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">System-wide</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $category->resources_count ?? $category->resources()->count() }}</span>
                                </td>

                                <td class="text-center">
                                    @if($category->status === 'active')
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
                                                       data-bs-target="#editCategoryModal"
                                                       data-id="{{ $category->id }}"
                                                       data-name="{{ $category->name }}"
                                                       data-description="{{ $category->description }}"
                                                       data-status="{{ $category->status }}">
                                                        <i class="feather-edit-2 me-2"></i> Edit
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('finance.resources.delete')
                                                @if(!$category->resources()->exists() && !$category->commitments()->exists())
                                                    <li>
                                                        <form action="{{ route('finance.resources.categories.destroy', $category) }}" method="POST" class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="feather-trash-2 me-2"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                @else
                                                    <li>
                                                        <span class="dropdown-item text-muted" title="Has resources or commitments">
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

    {{-- ================= ADD CATEGORY MODAL ================= --}}
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('finance.resources.categories.store') }}" class="w-100">
                @csrf

                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="fw-bold mb-0">
                            <i class="feather-folder-plus text-primary me-1"></i>
                            Add Resource Category
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-medium">
                                Category Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Equipment, Services" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Optional description..."></textarea>
                        </div>

                        @if(Auth::user()->governance_node_id)
                            <div class="alert alert-info mb-0">
                                <i class="feather-info me-1"></i>
                                This category will be assigned to your governance node: <strong>{{ Auth::user()->governanceNode->name ?? 'N/A' }}</strong>
                            </div>
                        @endif
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

    {{-- ================= EDIT CATEGORY MODAL ================= --}}
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="editCategoryForm" class="w-100">
                @csrf
                @method('PUT')

                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="fw-bold mb-0">
                            <i class="feather-edit text-primary me-1"></i>
                            Edit Resource Category
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-medium">
                                Category Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>

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
        const editModal = document.getElementById('editCategoryModal');

        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const description = button.getAttribute('data-description');
            const status = button.getAttribute('data-status');

            // Update form action
            document.getElementById('editCategoryForm').action = '{{ url("finance/resources/categories") }}/' + id;

            // Fill form fields
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description || '';
            document.getElementById('edit_status').value = status || 'active';
        });
    });
</script>
@endpush

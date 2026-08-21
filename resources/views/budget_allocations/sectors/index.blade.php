@extends('layouts.app')

@section('content')
    <div class="nxl-container">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-dark mb-0">Sectors Management</h4>
                <small class="text-muted">Define and manage all sectors under which programs and projects are
                    grouped.</small>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSectorModal">
                <i class="bi bi-plus-circle me-1"></i> Add Sector
            </button>
        </div>

        {{-- ALERTS --}}
        @if (session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger mt-3">{{ $errors->first() }}</div>
        @endif

        {{-- SECTORS TABLE --}}
        <div class="card mt-4 shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Registered Sectors</h5>
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Sector Name</th>
                            <th>Description</th>
                            <th class="text-center" width="160">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sectors as $index => $sector)
                            <tr>
                                <td>{{ $sectors->firstItem() + $index }}</td>
                                <td class="fw-bold text-primary">{{ $sector->name }}</td>
                                <td>{{ $sector->description ?? '-' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-warning editSectorBtn"
                                        data-id="{{ $sector->id }}" data-name="{{ $sector->name }}"
                                        data-desc="{{ $sector->description ?? '' }}" data-bs-toggle="modal"
                                        data-bs-target="#editSectorModal">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form action="{{ route('sectors.destroy', $sector->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Delete this sector?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No sectors found. <br>
                                    <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal"
                                        data-bs-target="#addSectorModal">
                                        <i class="bi bi-plus-circle"></i> Add your first Sector
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $sectors->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== ADD SECTOR MODAL ==================== --}}
    <div class="modal fade" id="addSectorModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" action="{{ route('sectors.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark">Add New Sector</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Sector Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Energy, Water, Health"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Brief description of the sector"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save Sector</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== EDIT SECTOR MODAL ==================== --}}
    <div class="modal fade" id="editSectorModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editSectorForm" class="modal-content" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark">Edit Sector</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Sector Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editSectorName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="editSectorDesc" rows="3" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="submit">Update Sector</button>
                </div>
            </form>
        </div>
    </div>
@endsection


<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Fill edit modal dynamically
        document.querySelectorAll('.editSectorBtn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const name = btn.dataset.name;
                const desc = btn.dataset.desc;

                document.getElementById('editSectorName').value = name;
                document.getElementById('editSectorDesc').value = desc || '';
                document.getElementById('editSectorForm').action = `/budget/sectors/${id}`;
            });
        });
    });
</script>

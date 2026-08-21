@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= HEADER ================= --}}
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold">Prescreening Templates</h4>
                <p class="text-muted mb-0">
                    Manage prescreening configurations used for procurement evaluations
                </p>
            </div>

            <a href="{{ route('prescreening.templates.create') }}" class="btn btn-primary btn-sm">
                <i class="feather-plus me-1"></i> New Template
            </a>
        </div>

        {{-- ================= TABLE ================= --}}
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <x-data-table id="templatesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Name</th>
                            <th>Description</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Criteria</th>
                            <th width="160" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($templates as $template)
                            <tr>
                                <td class="ps-4 fw-semibold">
                                    {{ $template->name }}
                                </td>

                                <td class="text-muted">
                                    {{ Str::limit($template->description, 80) ?? '—' }}
                                </td>

                                <td class="text-center">
                                    <span class="badge {{ $template->is_active ? 'bg-success' : 'bg-secondary' }} px-3 py-1">
                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-info px-3 py-1">
                                        {{ $template->criteria_count ?? $template->criteria->count() }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('prescreening.templates.show', $template) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="feather-eye"></i>
                                    </a>
                                    <a href="{{ route('prescreening.templates.edit', $template) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="feather-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-data-table>
            </div>
        </div>

    </div>
@endsection

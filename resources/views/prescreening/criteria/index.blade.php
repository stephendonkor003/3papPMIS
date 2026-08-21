@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        <div class="page-header d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold">Prescreening Criteria</h4>
                <p class="text-muted mb-0">
                    Template: <strong>{{ $template->name }}</strong>
                </p>
            </div>

            <a href="{{ route('prescreening.criteria.create', $template) }}" class="btn btn-primary btn-sm">
                <i class="feather-plus me-1"></i> Add Criterion
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <x-data-table id="criteriaTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Order</th>
                            <th>Name</th>
                            <th>Field Key</th>
                            <th>Type</th>
                            <th class="text-center">Mandatory</th>
                            <th width="160" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($criteria as $criterion)
                            <tr>
                                <td class="ps-4">{{ $criterion->sort_order }}</td>
                                <td class="fw-semibold">{{ $criterion->name }}</td>
                                <td><code>{{ $criterion->field_key }}</code></td>
                                <td>{{ ucfirst(str_replace('_', ' ', $criterion->evaluation_type)) }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $criterion->is_mandatory ? 'bg-success' : 'bg-secondary' }} px-3 py-1">
                                        {{ $criterion->is_mandatory ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('prescreening.criteria.show', [$template, $criterion]) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="feather-eye"></i>
                                    </a>
                                    <a href="{{ route('prescreening.criteria.edit', [$template, $criterion]) }}"
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

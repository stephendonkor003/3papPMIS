@extends('layouts.app')
@section('title', 'Dataset Details')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <h5 class="m-b-10">View Think Dataset</h5>
            </div>

            <div class="main-content">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-4">
                            @foreach ($dataset->getAttributes() as $key => $value)
                                @continue(in_array($key, ['id', 'created_at', 'updated_at', 'created_by']))

                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-light h-100">
                                        <div class="text-muted small mb-1">{{ ucwords(str_replace(['_', '.'], ' ', $key)) }}
                                        </div>
                                        <div class="fw-bold text-dark">
                                            {{ $value && $value !== 'null' ? $value : '—' }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Optional: Created By -->
                            @if ($dataset->created_by)
                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-light h-100">
                                        <div class="text-muted small mb-1">Created By</div>
                                        <div class="fw-bold text-dark">{{ $dataset->created_by }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('think-datasets.index') }}" class="btn btn-secondary">Back to List</a>
                            <a href="{{ route('think-datasets.edit', $dataset->id) }}" class="btn btn-primary">Edit
                                Dataset</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

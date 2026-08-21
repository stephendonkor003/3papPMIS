@extends('layouts.app')

@section('title', 'Upload Think Dataset')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="card">
                <div class="card-header"><strong>Upload Think Tank Dataset</strong></div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('think-datasets.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="excel_file" class="form-label">Select Excel File</label>
                            <input type="file" name="excel_file" class="form-control" required>
                        </div>
                        <button class="btn btn-primary">Upload and Import</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

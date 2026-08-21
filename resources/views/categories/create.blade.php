@extends('layouts.app')
@section('title', 'Add Category')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <div class="page-header-left">
                    <h5 class="m-b-10">Add New Category</h5>
                </div>
            </div>

            <div class="main-content">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('categories.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Category Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="5">{{ old('description') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Create Category</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

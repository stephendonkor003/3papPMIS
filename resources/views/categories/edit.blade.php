@extends('layouts.app')
@section('title', 'Edit Category')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <div class="page-header-left">
                    <h5 class="m-b-10">Edit Category</h5>
                </div>
            </div>

            <div class="main-content">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('categories.update', $category->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Category Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $category->name) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="5">{{ old('description', $category->description) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Category</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

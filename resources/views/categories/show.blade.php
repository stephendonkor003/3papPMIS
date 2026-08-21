@extends('layouts.app')
@section('title', 'Category Details')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <div class="page-header-left">
                    <h5 class="m-b-10">Category Details</h5>
                </div>
            </div>

            <div class="main-content">
                <div class="card">
                    <div class="card-body">
                        <h5><strong>Name:</strong> {{ $category->name }}</h5>
                        <p><strong>Description:</strong></p>
                        <p>{{ $category->description }}</p>
                        <p><strong>Created By:</strong> {{ $category->creator->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

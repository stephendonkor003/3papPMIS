@extends('layouts.app')
@section('title', 'Add Committee')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <div class="page-header-left">
                    <h5 class="m-b-10">Create Evaluation Committee</h5>
                </div>
            </div>

            <div class="main-content">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('committees.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Committee Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Associated Project</label>
                                <select name="project_id" class="form-control" required>
                                    <option value="">-- Select Project --</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Chairperson</label>
                                <select name="chairperson_id" class="form-control">
                                    <option value="">-- Select Chairperson --</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Create Committee</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@extends('layouts.app')
@section('title', 'Edit Committee')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <div class="page-header-left">
                    <h5 class="m-b-10">Edit Evaluation Committee</h5>
                </div>
            </div>

            <div class="main-content">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('committees.update', $committee->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Committee Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $committee->name) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Associated Project</label>
                                <select name="project_id" class="form-control" required>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}"
                                            {{ $committee->project_id == $project->id ? 'selected' : '' }}>
                                            {{ $project->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Chairperson</label>
                                <select name="chairperson_id" class="form-control">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ $committee->chairperson_id == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Committee</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

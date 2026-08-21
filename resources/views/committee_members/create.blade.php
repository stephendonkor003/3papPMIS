@extends('layouts.app')
@section('title', 'Add Committee Member')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <h5 class="m-0">Add Committee Member</h5>
            </div>

            <div class="main-content">
                <div class="card">
                    <div class="card-body">

                        <form action="{{ route('committee-members.store') }}" method="POST" class="mt-4">
                            @csrf

                            <div class="mb-3">
                                <label for="user_id" class="form-label">Select User</label>
                                <select name="user_id" id="user_id" class="form-control" required>
                                    <option value="">-- Choose User --</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="committee_id" class="form-label">Select Committee</label>
                                <select name="committee_id" id="committee_id" class="form-control" required>
                                    <option value="">-- Choose Committee --</option>
                                    @foreach ($committees as $committee)
                                        <option value="{{ $committee->id }}">{{ $committee->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success">Add Member</button>
                            <a href="{{ route('committee-members.index') }}" class="btn btn-secondary">Back</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

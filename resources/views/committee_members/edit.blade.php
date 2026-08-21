@extends('layouts.app')
@section('title', 'Edit Committee Member')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <h5>Edit Committee Member</h5>
            </div>

            <div class="main-content">
                <div class="card">
                    <div class="card-body">

                        <form action="{{ route('committee-members.update', $committeeMember->id) }}" method="POST"
                            class="mt-4">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">User</label>
                                <select name="user_id" class="form-control" required>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ $committeeMember->user_id == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Committee</label>
                                <select name="committee_id" class="form-control" required>
                                    @foreach ($committees as $committee)
                                        <option value="{{ $committee->id }}"
                                            {{ $committeeMember->committee_id == $committee->id ? 'selected' : '' }}>
                                            {{ $committee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('committee-members.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

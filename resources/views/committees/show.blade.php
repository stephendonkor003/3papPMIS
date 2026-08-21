@extends('layouts.app')
@section('title', 'Committee Details')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <div class="page-header-left">
                    <h5 class="m-b-10">Committee Details</h5>
                </div>
            </div>

            <div class="main-content">
                <div class="card">
                    <div class="card-body">
                        <h5><strong>Name:</strong> {{ $committee->name }}</h5>
                        <p><strong>Project:</strong> {{ $committee->project->title ?? 'N/A' }}</p>
                        <p><strong>Chairperson:</strong> {{ $committee->chairperson->name ?? 'Not Assigned' }}</p>

                        <div class="mt-4">
                            <h6><strong>Members:</strong></h6>
                            <ul>
                                @foreach ($committee->members as $member)
                                    <li>{{ $member->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

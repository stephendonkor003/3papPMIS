@extends('layouts.app')

@section('content')
    <div class="nxl-container">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="page-header">
            <h4 class="fw-bold mb-1">
                <i class="feather-briefcase text-primary me-1"></i>
                {{ $vacancy->position->title }}
            </h4>
            <p class="text-muted mb-0">
                {{ ucfirst($vacancy->position->employment_type) }}
            </p>
        </div>

        {{-- ================= ALERTS ================= --}}
        @if (session('success'))
            <div class="alert alert-success mt-3">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ================= CONTENT ================= --}}
        <div class="row mt-4">

            {{-- ================= JOB DETAILS ================= --}}
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <h6 class="fw-bold">Job Description</h6>
                        <p class="text-muted">
                            {{ $vacancy->position->description ?? 'No description provided.' }}
                        </p>

                        <hr>

                        <p class="mb-1">
                            <strong>Employment Type:</strong>
                            {{ ucfirst($vacancy->position->employment_type) }}
                        </p>

                        <p class="mb-1">
                            <strong>Positions Available:</strong>
                            {{ $vacancy->number_of_positions }}
                        </p>

                        <p class="mb-0">
                            <strong>Application Deadline:</strong>
                            {{ $vacancy->close_date->format('d M Y') }}
                        </p>

                    </div>
                </div>
            </div>

            {{-- ================= APPLICATION FORM ================= --}}
            <div class="col-md-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <h6 class="fw-bold mb-3">
                            Apply for this position
                        </h6>

                        <form method="POST" action="{{ route('careers.apply', $vacancy->id) }}"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">
                                        Full Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="full_name" class="form-control"
                                        value="{{ old('full_name') }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">
                                        Email Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                        required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">
                                        Phone Number
                                    </label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">
                                        Gender
                                    </label>
                                    <select name="gender" class="form-select">
                                        <option value="">Select</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium">
                                    Nationality
                                </label>
                                <input type="text" name="nationality" class="form-control"
                                    value="{{ old('nationality') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium">
                                    CV (PDF / Word) <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="cv" class="form-control" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-medium">
                                    Cover Letter (Optional)
                                </label>
                                <input type="file" name="cover_letter" class="form-control">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="feather-send me-1"></i>
                                Submit Application
                            </button>

                        </form>

                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection

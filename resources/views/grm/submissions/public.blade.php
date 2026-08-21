@extends('layouts.public')

@section('title', 'Log a Grievance | 3PAP PMIS')
@section('description', 'Submit a public or anonymous grievance to the 3PAP Portfolio Management Information System.')

@section('content')
    @include('grm.submissions._form', [
        'submissionAction' => route('public.grievances.store'),
        'isPublicSubmission' => true,
    ])
@endsection

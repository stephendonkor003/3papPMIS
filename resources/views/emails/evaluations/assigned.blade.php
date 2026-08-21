@extends('emails.layouts.notification', [
    'title' => 'New Evaluation Assignment',
    'preheader' => 'An evaluation has been assigned to you in 3PAP PMIS.',
    'actionUrl' => route('my.eval.index'),
    'actionText' => 'Open My Evaluations',
])

@section('content')
    <p style="margin:0 0 15px;">Hello {{ $evaluator->name ?: 'Evaluator' }},</p>
    <p style="margin:0 0 18px;">You have been assigned an evaluation. Sign in to review the material and complete it within the required timeline.</p>

    <div style="border:1px solid #dbe4ee;border-radius:10px;background:#f4f7fa;padding:16px 18px;">
        <div style="margin-bottom:8px;"><strong>Procurement:</strong> {{ $procurement->title }}</div>
        @if ($procurement->reference_no)
            <div style="margin-bottom:8px;"><strong>Reference:</strong> {{ $procurement->reference_no }}</div>
        @endif
        <div style="margin-bottom:8px;"><strong>Evaluation:</strong> {{ $evaluation->name ?? $evaluation->title ?? 'Evaluation' }}</div>
        @if ($submission)
            <div><strong>Submission:</strong> {{ $submission->procurement_submission_code }}</div>
        @endif
    </div>

    <p style="margin:18px 0 0;color:#60728b;font-size:13px;">Evaluation information is confidential and should only be accessed through your own account.</p>
@endsection

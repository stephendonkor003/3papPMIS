@extends('emails.layouts.notification', [
    'title' => 'Evaluation Submitted',
    'preheader' => 'A completed evaluation report is attached for review.',
])

@section('content')
    <p style="margin:0 0 15px;">Hello,</p>
    <p style="margin:0 0 18px;">An evaluator has submitted a completed evaluation. A detailed PDF report is attached to this email.</p>

    <div style="border:1px solid #dbe4ee;border-radius:10px;background:#f4f7fa;padding:16px 18px;">
        <div style="margin-bottom:8px;"><strong>Procurement:</strong> {{ $submission->procurement?->title ?? 'Not specified' }}</div>
        <div style="margin-bottom:8px;"><strong>Submission:</strong> {{ $submission->applicant?->procurement_submission_code ?? 'Not specified' }}</div>
        <div style="margin-bottom:8px;"><strong>Evaluator:</strong> {{ $submission->evaluator?->name ?? 'Not specified' }}</div>
        @if ($submission->overall_score !== null)
            <div><strong>Overall score:</strong> {{ number_format((float) $submission->overall_score, 2) }}@if ($overallMax !== null) / {{ number_format((float) $overallMax, 2) }}@endif</div>
        @endif
    </div>

    <p style="margin:18px 0 0;color:#60728b;font-size:13px;">Handle the attached evaluation report as confidential procurement information.</p>
@endsection

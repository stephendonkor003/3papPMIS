@extends('emails.layouts.notification', [
    'title' => 'Prescreening Completed',
    'preheader' => 'A completed prescreening report is attached for review.',
])

@section('content')
    <p style="margin:0 0 15px;">Hello,</p>
    <p style="margin:0 0 18px;">Prescreening has been completed for the submission below. The full report is included as a PDF attachment.</p>

    <div style="border:1px solid #dbe4ee;border-radius:10px;background:#f4f7fa;padding:16px 18px;">
        <div style="margin-bottom:8px;"><strong>Procurement:</strong> {{ $submission->procurement?->title ?? 'Not specified' }}</div>
        <div style="margin-bottom:8px;"><strong>Submission:</strong> {{ $submission->procurement_submission_code }}</div>
        @if ($template)
            <div style="margin-bottom:8px;"><strong>Template:</strong> {{ $template->name ?? $template->title ?? 'Prescreening template' }}</div>
        @endif
        @if ($submission->prescreeningResult?->final_status)
            <div><strong>Result:</strong> {{ \Illuminate\Support\Str::headline($submission->prescreeningResult->final_status) }}</div>
        @endif
    </div>

    <p style="margin:18px 0 0;color:#60728b;font-size:13px;">Handle the attached report as confidential procurement information.</p>
@endsection

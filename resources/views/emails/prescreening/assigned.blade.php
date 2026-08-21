@extends('emails.layouts.notification', [
    'title' => 'New Prescreening Assignment',
    'preheader' => 'A procurement prescreening assignment is ready for you.',
    'actionUrl' => route('prescreening.assignments.my'),
    'actionText' => 'View My Assignments',
])

@section('content')
    <p style="margin:0 0 15px;">Hello {{ $assignee->name ?: 'Reviewer' }},</p>
    <p style="margin:0 0 18px;">You have been assigned to prescreen submissions for the procurement opportunity below.</p>

    <div style="border:1px solid #dbe4ee;border-radius:10px;background:#f4f7fa;padding:16px 18px;">
        <div style="font-size:17px;font-weight:800;">{{ $procurement->title }}</div>
        @if ($procurement->reference_no)
            <div style="margin-top:5px;color:#60728b;font-size:13px;">Reference: {{ $procurement->reference_no }}</div>
        @endif
    </div>

    <p style="margin:18px 0 0;color:#60728b;font-size:13px;">Please keep applicant and procurement information confidential.</p>
@endsection

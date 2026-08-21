@extends('emails.layouts.notification', [
    'title' => ($isResend ? 'Resent: ' : '') . 'Treaty Proof-of-Service Codes',
    'preheader' => 'Proof-of-service codes submitted for AU Legal review.',
    'actionUrl' => $reviewUrl,
    'actionText' => 'Review Treaty Record',
    'accent' => '#d97706',
])

@section('content')
    <p style="margin:0 0 15px;">Hello AU Legal Directorate,</p>
    <p style="margin:0 0 18px;">{{ $memberStateName }} has {{ $isResend ? 'resent' : 'submitted' }} proof-of-service codes for the treaty below.</p>

    <div style="border:1px solid #dbe4ee;border-radius:10px;background:#f4f7fa;padding:16px 18px;">
        <div style="font-size:17px;font-weight:800;">{{ $treaty->title }}</div>
        <div style="margin-top:8px;"><strong>Reference:</strong> {{ $treaty->reference_code ?: 'Not assigned' }}</div>
        <div style="margin-top:6px;"><strong>Member state:</strong> {{ $memberStateName }}</div>
        <div style="margin-top:6px;"><strong>Submitted by:</strong> {{ $actor->name }}</div>
    </div>

    <div style="margin-top:18px;border-left:4px solid #d97706;background:#fff7ed;padding:14px 16px;">
        <div style="margin-bottom:7px;word-break:break-all;"><strong>Signed code:</strong> {{ $status->signed_service_code ?: 'Not provided' }}</div>
        <div style="word-break:break-all;"><strong>Ratified code:</strong> {{ $status->ratified_service_code ?: 'Not provided' }}</div>
    </div>

    <p style="margin:18px 0 0;color:#7c2d12;font-size:13px;">Treat these service codes as confidential and verify them only in the secured treaty record.</p>
@endsection

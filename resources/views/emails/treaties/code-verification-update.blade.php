@extends('emails.layouts.notification', [
    'title' => $codeLabel . ' Treaty Code Verified',
    'preheader' => 'Your treaty proof-of-service code has been verified.',
    'actionUrl' => $memberPortalUrl,
    'actionText' => 'Open Treaty Portal',
])

@section('content')
    <p style="margin:0 0 15px;">Hello {{ $memberStateName }} representative,</p>
    <p style="margin:0 0 18px;">The AU Legal Directorate has verified your {{ strtolower($codeLabel) }} proof-of-service code.</p>

    <div style="border:1px solid #dbe4ee;border-radius:10px;background:#f4f7fa;padding:16px 18px;">
        <div style="font-size:17px;font-weight:800;">{{ $treaty->title }}</div>
        <div style="margin-top:8px;"><strong>Reference:</strong> {{ $treaty->reference_code ?: 'Not assigned' }}</div>
        <div style="margin-top:6px;"><strong>Member state:</strong> {{ $memberStateName }}</div>
        <div style="margin-top:6px;"><strong>Verification:</strong> {{ $codeLabel }} code verified</div>
        <div style="margin-top:6px;"><strong>Verified by:</strong> {{ $actorName }}</div>
        @php($verifiedAt = $codeType === 'ratified' ? $status->ratified_service_code_verified_at : $status->signed_service_code_verified_at)
        @if ($verifiedAt)
            <div style="margin-top:6px;"><strong>Verified at:</strong> {{ $verifiedAt->format('d M Y H:i') }}</div>
        @endif
    </div>

    <p style="margin:18px 0 0;">No further action is required for this verification.</p>
@endsection

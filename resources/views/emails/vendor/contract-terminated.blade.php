@extends('emails.layouts.notification', [
    'title' => 'Contract Termination Notice',
    'preheader' => 'An update has been recorded for your procurement contract.',
    'actionUrl' => $portalUrl,
    'actionText' => 'Sign In to Vendor Portal',
    'accent' => '#dc2626',
])

@section('content')
    <p style="margin:0 0 15px;">Hello {{ $vendor->name ?: 'Vendor' }},</p>
    <p style="margin:0 0 18px;">This message confirms that the contract associated with the procurement below has been terminated.</p>

    <div style="border:1px solid #fecaca;border-radius:10px;background:#fef2f2;padding:16px 18px;">
        <div style="font-size:17px;font-weight:800;">{{ $procurement->title }}</div>
        @if ($procurement->reference_no)
            <div style="margin-top:6px;color:#7f1d1d;">Reference: {{ $procurement->reference_no }}</div>
        @endif
    </div>

    <div style="margin-top:18px;">
        <div style="margin-bottom:6px;font-weight:800;">Reason</div>
        <div style="border-left:4px solid #dc2626;background:#fef2f2;padding:14px 16px;white-space:pre-line;">{{ $reason }}</div>
    </div>

    <p style="margin:18px 0 0;">Contact the procurement team through the portal if you need clarification.</p>
@endsection

@extends('emails.layouts.notification', [
    'title' => 'Procurement Awarded',
    'preheader' => 'Your organization has been selected for a procurement award.',
    'actionUrl' => $portalUrl,
    'actionText' => 'Sign In to Vendor Portal',
    'accent' => '#059669',
])

@section('content')
    <p style="margin:0 0 15px;">Congratulations {{ $vendor->name ?: 'Vendor' }},</p>
    <p style="margin:0 0 18px;">Your organization has been selected for the procurement opportunity below.</p>

    <div style="border:1px solid #a7f3d0;border-radius:10px;background:#ecfdf5;padding:16px 18px;">
        <div style="font-size:17px;font-weight:800;">{{ $procurement->title }}</div>
        @if ($procurement->reference_no)
            <div style="margin-top:7px;"><strong>Reference:</strong> {{ $procurement->reference_no }}</div>
        @endif
        @if ($negotiation->agreed_amount !== null)
            <div style="margin-top:7px;"><strong>Agreed amount:</strong> {{ number_format((float) $negotiation->agreed_amount, 2) }}</div>
        @endif
        @if ($negotiation->agreed_at)
            <div style="margin-top:7px;"><strong>Agreement date:</strong> {{ $negotiation->agreed_at->format('d M Y') }}</div>
        @endif
    </div>

    <p style="margin:18px 0 0;">Sign in to the vendor portal to review the next contract and delivery steps.</p>
@endsection

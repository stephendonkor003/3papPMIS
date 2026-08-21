@extends('emails.layouts.notification', [
    'title' => 'New Procurement Opportunity',
    'preheader' => 'You are invited to review a procurement opportunity.',
    'actionUrl' => $link,
    'actionText' => 'View Procurement Opportunity',
])

@section('content')
    <p style="margin:0 0 15px;">Hello {{ $vendor->name ?: 'Vendor' }},</p>
    <p style="margin:0 0 18px;">You are invited to review the procurement opportunity below and apply if it matches your organization&rsquo;s capabilities.</p>

    <div style="border:1px solid #dbe4ee;border-radius:10px;background:#f4f7fa;padding:16px 18px;">
        <div style="font-size:17px;font-weight:800;">{{ $procurement->title }}</div>
        @if ($procurement->reference_no)
            <div style="margin-top:7px;"><strong>Reference:</strong> {{ $procurement->reference_no }}</div>
        @endif
        @if ($procurement->application_end_date)
            <div style="margin-top:7px;"><strong>Application deadline:</strong> {{ $procurement->application_end_date->format('d M Y') }}</div>
        @endif
    </div>

    @if ($message)
        <div style="margin-top:18px;">
            <div style="margin-bottom:6px;font-weight:800;">Message from the procurement team</div>
            <div style="border-left:4px solid #1687ea;background:#eff8ff;padding:14px 16px;white-space:pre-line;">{{ $message }}</div>
        </div>
    @endif
@endsection

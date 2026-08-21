@extends('emails.layouts.notification', [
    'title' => 'New Member-State Communication',
    'preheader' => $memberStateName . ' submitted a new communication.',
    'actionUrl' => $reviewUrl,
    'actionText' => 'Review Communication',
])

@section('content')
    <p style="margin:0 0 15px;">Hello,</p>
    <p style="margin:0 0 18px;">A new communication from {{ $memberStateName }} is ready for review.</p>

    <div style="border:1px solid #dbe4ee;border-radius:10px;background:#f4f7fa;padding:16px 18px;">
        <div style="font-size:17px;font-weight:800;">{{ $communication->subject }}</div>
        <div style="margin-top:8px;"><strong>Member state:</strong> {{ $memberStateName }}</div>
        <div style="margin-top:6px;"><strong>Channel:</strong> {{ \Illuminate\Support\Str::headline($communication->channel ?? 'portal') }}</div>
        @if ($communication->communication_date)
            <div style="margin-top:6px;"><strong>Date:</strong> {{ $communication->communication_date->format('d M Y') }}</div>
        @endif
    </div>

    <div style="margin-top:18px;white-space:pre-line;">{{ $communication->message }}</div>
@endsection

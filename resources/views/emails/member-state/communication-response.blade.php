@extends('emails.layouts.notification', [
    'title' => 'Response to Your Communication',
    'preheader' => 'The African Union team has responded to your communication.',
    'actionUrl' => $portalUrl,
    'actionText' => 'Open Communications Portal',
])

@section('content')
    <p style="margin:0 0 15px;">Hello {{ $memberStateName }} representative,</p>
    <p style="margin:0 0 18px;">A response has been recorded for your communication.</p>

    <div style="border:1px solid #dbe4ee;border-radius:10px;background:#f4f7fa;padding:16px 18px;">
        <div style="color:#60728b;font-size:11px;font-weight:800;text-transform:uppercase;">Subject</div>
        <div style="margin-top:4px;font-size:17px;font-weight:800;">{{ $communication->subject }}</div>
        <div style="margin-top:7px;color:#60728b;font-size:13px;">Status: {{ \Illuminate\Support\Str::headline($communication->status) }}</div>
    </div>

    <div style="margin-top:18px;">
        <div style="margin-bottom:6px;font-weight:800;">AU response</div>
        <div style="border-left:4px solid #1687ea;background:#eff8ff;padding:14px 16px;white-space:pre-line;">{{ $communication->response_text }}</div>
    </div>
@endsection

@extends('emails.layouts.notification', [
    'title' => 'Response to Your Information Request',
    'preheader' => 'The 3PAP team has responded to your request.',
    'actionUrl' => route('partner.requests.index'),
    'actionText' => 'View My Requests',
])

@section('content')
    <p style="margin:0 0 15px;">Hello {{ $request->requester?->name ?? 'Funding partner' }},</p>
    <p style="margin:0 0 18px;">A response has been recorded for your information request.</p>

    <div style="border:1px solid #dbe4ee;border-radius:10px;background:#f4f7fa;padding:16px 18px;">
        <div style="color:#60728b;font-size:11px;font-weight:800;text-transform:uppercase;">Request</div>
        <div style="margin-top:4px;font-size:17px;font-weight:800;">{{ $request->subject }}</div>
        <div style="margin-top:7px;color:#60728b;font-size:13px;">Status: {{ \Illuminate\Support\Str::headline($request->status) }}</div>
    </div>

    <div style="margin-top:18px;">
        <div style="margin-bottom:6px;font-weight:800;">Response</div>
        <div style="border-left:4px solid #1687ea;background:#eff8ff;padding:14px 16px;white-space:pre-line;">{{ $request->response }}</div>
    </div>
@endsection

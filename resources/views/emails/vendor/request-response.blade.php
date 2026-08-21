@extends('emails.layouts.notification', [
    'title' => $subjectLine,
    'preheader' => 'The procurement team has responded to your request.',
    'actionUrl' => route('login'),
    'actionText' => 'Sign In to Vendor Portal',
])

@section('content')
    <p style="margin:0 0 15px;">Hello {{ $request->user?->name ?? 'Vendor' }},</p>
    <p style="margin:0 0 18px;">A response has been recorded for your {{ $type === 'message' ? 'clarification request' : 'information request' }}.</p>

    <div style="border:1px solid #dbe4ee;border-radius:10px;background:#f4f7fa;padding:16px 18px;">
        <div style="font-size:17px;font-weight:800;">{{ $request->subject ?? $request->request_topic ?? 'Vendor request' }}</div>
        @if ($request->procurement?->title)
            <div style="margin-top:7px;color:#60728b;">{{ $request->procurement->title }}</div>
        @endif
        <div style="margin-top:7px;color:#60728b;font-size:13px;">Status: {{ \Illuminate\Support\Str::headline($request->status) }}</div>
    </div>

    <div style="margin-top:18px;">
        <div style="margin-bottom:6px;font-weight:800;">Response</div>
        <div style="border-left:4px solid #1687ea;background:#eff8ff;padding:14px 16px;white-space:pre-line;">{{ $request->response }}</div>
    </div>
@endsection

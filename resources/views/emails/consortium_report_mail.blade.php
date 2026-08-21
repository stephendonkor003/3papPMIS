@extends('emails.layouts.notification', [
    'title' => 'Consortium Evaluation Report',
    'preheader' => 'Your consortium evaluation report is attached to this email.',
])

@section('content')
    <p style="margin:0 0 15px;">Hello {{ $consortium->name ?? 'Consortium representative' }},</p>
    <p style="margin:0 0 18px;">Your think tank consortium evaluation report has been generated successfully.</p>

    <div style="border:1px solid #dbe4ee;border-radius:10px;background:#f4f7fa;padding:16px 18px;">
        <div style="color:#60728b;font-size:11px;font-weight:800;text-transform:uppercase;">Consortium</div>
        <div style="margin-top:4px;font-size:17px;font-weight:800;">{{ $consortium->name ?? 'Consortium' }}</div>
        @if (! empty($consortium->code))
            <div style="margin-top:5px;color:#60728b;font-size:13px;">Reference: {{ $consortium->code }}</div>
        @endif
    </div>

    <p style="margin:18px 0 0;">The full report is included as a PDF attachment. Keep it for your records.</p>
@endsection

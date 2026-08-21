@extends('emails.layouts.notification', [
    'title' => 'Indicator Reporting Reminder',
    'preheader' => 'A reminder about your indicator reporting responsibility.',
    'actionUrl' => route('budget.me.indicators.index'),
    'actionText' => 'Open Indicator Management',
])

@section('content')
    <p style="margin:0 0 15px;">Hello {{ $user->name ?: 'Colleague' }},</p>
    <p style="margin:0 0 18px;">This is a reminder that you are responsible for keeping the following indicator data up to date.</p>

    <div style="border:1px solid #dbe4ee;border-radius:10px;background:#f4f7fa;padding:16px 18px;">
        <div style="font-size:17px;font-weight:800;">{{ $indicator->name }}</div>
        <div style="margin-top:8px;"><strong>Code:</strong> {{ $indicator->indicator_code ?? 'Not assigned' }}</div>
        <div style="margin-top:6px;"><strong>Reporting frequency:</strong> {{ $frequency ?: 'Not specified' }}</div>
        @if ($indicator->responsible_party)
            <div style="margin-top:6px;"><strong>Responsible party:</strong> {{ $indicator->responsible_party }}</div>
        @endif
    </div>

    <p style="margin:18px 0 0;">Please review the reporting schedule and enter any outstanding data and supporting evidence.</p>
@endsection

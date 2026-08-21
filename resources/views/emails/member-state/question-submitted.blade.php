@extends('emails.layouts.notification', [
    'title' => 'New Member-State Question',
    'preheader' => $memberStateName . ' submitted a question for review.',
    'actionUrl' => $questionsDeskUrl,
    'actionText' => 'Open Questions Desk',
])

@section('content')
    <p style="margin:0 0 15px;">Hello,</p>
    <p style="margin:0 0 18px;">A question from {{ $memberStateName }} is ready for review.</p>

    <div style="border:1px solid #dbe4ee;border-radius:10px;background:#f4f7fa;padding:16px 18px;">
        <div style="font-size:17px;font-weight:800;">{{ $question->subject }}</div>
        <div style="margin-top:8px;"><strong>Member state:</strong> {{ $memberStateName }}</div>
        <div style="margin-top:6px;"><strong>Priority:</strong> {{ \Illuminate\Support\Str::headline($question->priority ?? 'normal') }}</div>
        @if ($question->asked_on)
            <div style="margin-top:6px;"><strong>Asked on:</strong> {{ $question->asked_on->format('d M Y') }}</div>
        @endif
    </div>

    <div style="margin-top:18px;white-space:pre-line;">{{ $question->question_text }}</div>
@endsection

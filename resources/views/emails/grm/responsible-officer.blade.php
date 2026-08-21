@php
    $emailTitle = 'New grievance assigned: '.$grievance->case_number;
    $emailPreheader = 'A new grievance requires review under case '.$grievance->case_number.'.';
    $emailEyebrow = 'Officer action required';
    $emailHeading = 'A new grievance has been assigned';
    $emailSubheading = 'Review the incident details, acknowledge receipt, and record the appropriate action.';
    $emailAccent = '#1687ea';
@endphp

@extends('emails.grm.layout')

@section('content')
    <p style="margin:0 0 16px;">Dear {{ $officer->name }},</p>
    <p style="margin:0 0 20px;">A new grievance has been registered and assigned to you as the responsible GRM officer for the linked program.</p>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0 0 20px;border-collapse:separate;border-spacing:0;border:1px solid #dbe4ee;border-radius:9px;overflow:hidden;">
        <tr>
            <td style="width:34%;padding:11px 13px;background:#f4f7fa;border-bottom:1px solid #dbe4ee;color:#60728b;font-size:12px;font-weight:700;">Case number</td>
            <td style="padding:11px 13px;border-bottom:1px solid #dbe4ee;color:#0756c7;font-weight:800;">{{ $grievance->case_number }}</td>
        </tr>
        <tr>
            <td style="padding:11px 13px;background:#f4f7fa;border-bottom:1px solid #dbe4ee;color:#60728b;font-size:12px;font-weight:700;">Subject</td>
            <td style="padding:11px 13px;border-bottom:1px solid #dbe4ee;color:#283b58;">{{ $grievance->subject }}</td>
        </tr>
        <tr>
            <td style="padding:11px 13px;background:#f4f7fa;border-bottom:1px solid #dbe4ee;color:#60728b;font-size:12px;font-weight:700;">Program</td>
            <td style="padding:11px 13px;border-bottom:1px solid #dbe4ee;color:#283b58;">{{ $grievance->program?->name ?? 'Not specified' }}</td>
        </tr>
        <tr>
            <td style="padding:11px 13px;background:#f4f7fa;border-bottom:1px solid #dbe4ee;color:#60728b;font-size:12px;font-weight:700;">Portfolio</td>
            <td style="padding:11px 13px;border-bottom:1px solid #dbe4ee;color:#283b58;">{{ $grievance->program?->sector?->name ?? 'Not specified' }}</td>
        </tr>
        <tr>
            <td style="padding:11px 13px;background:#f4f7fa;border-bottom:1px solid #dbe4ee;color:#60728b;font-size:12px;font-weight:700;">Complainant</td>
            <td style="padding:11px 13px;border-bottom:1px solid #dbe4ee;color:#283b58;">{{ $grievance->is_anonymous ? 'Anonymous' : ($grievance->submitter_name ?: $grievance->submitter?->name ?: 'Not provided') }}</td>
        </tr>
        <tr>
            <td style="padding:11px 13px;background:#f4f7fa;border-bottom:1px solid #dbe4ee;color:#60728b;font-size:12px;font-weight:700;">Received through</td>
            <td style="padding:11px 13px;border-bottom:1px solid #dbe4ee;color:#283b58;">{{ $grievance->channel_label }}</td>
        </tr>
        <tr>
            <td style="padding:11px 13px;background:#f4f7fa;border-bottom:1px solid #dbe4ee;color:#60728b;font-size:12px;font-weight:700;">Evidence files</td>
            <td style="padding:11px 13px;border-bottom:1px solid #dbe4ee;color:#283b58;">{{ number_format((int) ($grievance->attachments_count ?? 0)) }}</td>
        </tr>
        <tr>
            <td style="padding:11px 13px;background:#f4f7fa;color:#60728b;font-size:12px;font-weight:700;">Response due</td>
            <td style="padding:11px 13px;color:#9a3412;font-weight:700;">{{ $grievance->due_response_at?->format('d M Y, H:i') ?? 'Not set' }}</td>
        </tr>
    </table>

    <div style="margin:0 0 22px;padding:16px 18px;background:#f4f7fa;border:1px solid #dbe4ee;border-radius:9px;">
        <strong style="display:block;margin-bottom:7px;color:#14213d;">Incident details / summary</strong>
        <div style="white-space:pre-line;color:#283b58;">{{ \Illuminate\Support\Str::limit($grievance->description, 700) }}</div>
    </div>

    <table cellpadding="0" cellspacing="0" role="presentation" style="margin:0 0 20px;">
        <tr>
            <td style="border-radius:7px;background:#0756c7;">
                <a href="{{ $caseUrl }}" style="display:inline-block;padding:12px 19px;color:#ffffff;text-decoration:none;font-weight:800;font-size:14px;">Open secured GRM case</a>
            </td>
        </tr>
    </table>

    <p style="margin:0;color:#60728b;font-size:13px;">
        @if ($grievance->replyEmail())
            The complainant has received a separate acknowledgement containing the case number.
        @elseif ($grievance->is_anonymous && $grievance->replyPhone())
            A confidential phone contact is available only inside the secured case workspace.
        @else
            No reply email is available; use another recorded follow-up channel where possible.
        @endif
    </p>
@endsection

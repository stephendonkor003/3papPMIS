@php
    $emailTitle = 'Grievance received: '.$grievance->case_number;
    $emailPreheader = 'Your grievance has been registered under '.$grievance->case_number.'.';
    $emailEyebrow = 'Submission confirmation';
    $emailHeading = 'Your grievance has been received';
    $emailSubheading = 'The case is now registered for formal review and follow-up.';
    $emailAccent = '#1687ea';
@endphp

@extends('emails.grm.layout')

@section('content')
    <p style="margin:0 0 16px;">Dear {{ $grievance->is_anonymous ? 'Stakeholder' : ($grievance->submitter_name ?: 'Stakeholder') }},</p>

    <p style="margin:0 0 18px;">Thank you for contacting the 3PAP PMIS Grievance Redress Mechanism. Your incident details have been securely recorded and routed to the responsible team.</p>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0 0 20px;background:#eff8ff;border:1px solid #dbe4ee;border-radius:10px;">
        <tr>
            <td style="padding:16px 18px;">
                <div style="font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#60728b;">Case reference</div>
                <div style="margin-top:3px;color:#0756c7;font-size:22px;font-weight:800;letter-spacing:.4px;">{{ $grievance->case_number }}</div>
                <div style="margin-top:4px;color:#283b58;font-size:12px;">Status: {{ $grievance->status_label }}</div>
            </td>
        </tr>
    </table>

    @if ($rule?->auto_response_body)
        <div style="margin:0 0 20px;white-space:pre-line;">{{ $rule->auto_response_body }}</div>
    @else
        <p style="margin:0 0 20px;">The responsible GRM officer will review the case and respond using the private contact method you provided, where available.</p>
    @endif

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0 0 20px;border-collapse:separate;border-spacing:0;border:1px solid #dbe4ee;border-radius:9px;overflow:hidden;">
        <tr>
            <td style="width:34%;padding:11px 13px;background:#f4f7fa;border-bottom:1px solid #dbe4ee;color:#60728b;font-size:12px;font-weight:700;">Program</td>
            <td style="padding:11px 13px;border-bottom:1px solid #dbe4ee;color:#283b58;">3PAP PMIS Portfolio</td>
        </tr>
        <tr>
            <td style="padding:11px 13px;background:#f4f7fa;border-bottom:1px solid #dbe4ee;color:#60728b;font-size:12px;font-weight:700;">Grievance level</td>
            <td style="padding:11px 13px;border-bottom:1px solid #dbe4ee;color:#283b58;">{{ $grievance->level?->name ?? 'General grievance' }}</td>
        </tr>
        <tr>
            <td style="padding:11px 13px;background:#f4f7fa;border-bottom:1px solid #dbe4ee;color:#60728b;font-size:12px;font-weight:700;">Submitted</td>
            <td style="padding:11px 13px;border-bottom:1px solid #dbe4ee;color:#283b58;">{{ $grievance->submitted_at?->format('d M Y, H:i') ?? 'Recorded' }}</td>
        </tr>
        <tr>
            <td style="padding:11px 13px;background:#f4f7fa;border-bottom:1px solid #dbe4ee;color:#60728b;font-size:12px;font-weight:700;">Channel</td>
            <td style="padding:11px 13px;border-bottom:1px solid #dbe4ee;color:#283b58;">{{ $grievance->channel_label }}</td>
        </tr>
        <tr>
            <td style="padding:11px 13px;background:#f4f7fa;color:#60728b;font-size:12px;font-weight:700;">Response target</td>
            <td style="padding:11px 13px;color:#283b58;">{{ $grievance->due_response_at?->format('d M Y, H:i') ?? 'To be confirmed' }}</td>
        </tr>
    </table>

    <div style="margin:0;padding:14px 16px;border-left:4px solid #1687ea;background:#eff8ff;color:#14213d;border-radius:0 8px 8px 0;">
        <strong style="display:block;margin-bottom:3px;">Keep your case number safe</strong>
        Quote <strong>{{ $grievance->case_number }}</strong> in all future communication about this grievance.
    </div>
@endsection

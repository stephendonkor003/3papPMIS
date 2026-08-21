<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $emailTitle ?? '3PAP PMIS Grievance Redress Mechanism' }}</title>
</head>
<body style="margin:0;padding:0;background:#F4F7FA;color:#14213D;font-family:Arial,Helvetica,sans-serif;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
        {{ $emailPreheader ?? 'An update from the 3PAP PMIS Grievance Redress Mechanism.' }}
    </div>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;background:#F4F7FA;">
        <tr>
            <td align="center" style="padding:30px 12px;">
                <table width="680" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;max-width:680px;background:#ffffff;border:1px solid #dbe4ee;border-radius:14px;overflow:hidden;box-shadow:0 12px 28px rgba(16,84,59,.10);">
                    <tr>
                        <td style="height:6px;background:{{ $emailAccent ?? '#1687ea' }};font-size:0;line-height:0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="background:#06152F;padding:24px 28px;color:#ffffff;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td width="54" valign="top">
                                        <div style="width:42px;height:42px;line-height:42px;text-align:center;border-radius:8px;background:#0A6ED1;color:#ffffff;font-weight:800;font-size:13px;">3PAP</div>
                                    </td>
                                    <td valign="middle">
                                        <div style="font-size:11px;line-height:1.4;font-weight:700;letter-spacing:1.3px;text-transform:uppercase;color:#73D9FF;">3PAP PMIS</div>
                                        <div style="margin-top:3px;font-size:13px;line-height:1.4;color:#ffffff;">Grievance Redress Mechanism</div>
                                    </td>
                                    <td align="right" valign="middle" style="font-size:12px;color:#dceeff;white-space:nowrap;">
                                        {{ $grievance->case_number ?? 'PMIS GRM' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 30px 12px;">
                            <div style="font-size:11px;line-height:1.4;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;color:{{ $emailAccent ?? '#0756c7' }};">
                                {{ $emailEyebrow ?? 'Case communication' }}
                            </div>
                            <h1 style="margin:7px 0 8px;color:#14213d;font-size:25px;line-height:1.25;font-weight:800;">
                                {{ $emailHeading ?? 'Grievance case update' }}
                            </h1>
                            @if (filled($emailSubheading ?? null))
                                <p style="margin:0;color:#60728b;font-size:14px;line-height:1.6;">{{ $emailSubheading }}</p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:10px 30px 32px;color:#283b58;font-size:15px;line-height:1.65;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="border-top:1px solid #dbe4ee;background:#f4f7fa;padding:20px 30px;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td valign="top" style="color:#60728b;font-size:12px;line-height:1.6;">
                                        <strong style="display:block;color:#14213d;font-size:12px;">Confidential case communication</strong>
                                        Use the case number in this email whenever you follow up with the GRM team.
                                    </td>
                                    <td align="right" valign="top" style="color:#60728b;font-size:11px;line-height:1.6;white-space:nowrap;">
                                        &copy; {{ now()->year }} 3PAP<br>
                                        Portfolio Management Information System
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#0A2450;color:#DCEEFF;padding:12px 30px;text-align:center;font-size:10px;line-height:1.5;letter-spacing:.2px;">
                            Secure case communication from 3PAP PMIS.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

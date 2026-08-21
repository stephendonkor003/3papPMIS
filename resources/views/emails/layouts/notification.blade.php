@php
    $mailTitle = $title ?? config('app.name', '3PAP PMIS');
    $mailPreheader = $preheader ?? null;
    $mailAccent = $accent ?? '#1687ea';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $mailTitle }}</title>
</head>
<body style="margin:0;background:#f4f7fa;color:#14213d;font-family:Arial,Helvetica,sans-serif;">
    @if ($mailPreheader)
        <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
            {{ $mailPreheader }}
        </div>
    @endif

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f7fa;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="width:100%;max-width:640px;overflow:hidden;border:1px solid #dbe4ee;border-radius:16px;background:#ffffff;">
                    <tr>
                        <td style="background:#06152f;padding:26px 30px;color:#ffffff;border-bottom:4px solid {{ $mailAccent }};">
                            <div style="color:#73d9ff;font-size:11px;font-weight:700;letter-spacing:1.3px;text-transform:uppercase;">3PAP PMIS</div>
                            <div style="margin-top:7px;font-size:23px;font-weight:800;line-height:1.3;">{{ $mailTitle }}</div>
                            @if ($mailPreheader)
                                <div style="margin-top:7px;color:#dceeff;font-size:13px;line-height:1.5;">{{ $mailPreheader }}</div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px;font-size:15px;line-height:1.65;overflow-wrap:anywhere;">
                            @yield('content')

                            @if (! empty($actionUrl) && ! empty($actionText))
                                <table role="presentation" cellspacing="0" cellpadding="0" style="margin-top:24px;">
                                    <tr>
                                        <td style="border-radius:8px;background:{{ $mailAccent }};">
                                            <a href="{{ $actionUrl }}" style="display:inline-block;padding:12px 19px;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;">{{ $actionText }}</a>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="border-top:1px solid #dbe4ee;background:#f4f7fa;padding:19px 30px;color:#60728b;font-size:12px;line-height:1.55;">
                            This is an automated notification from {{ config('app.name', '3PAP PMIS') }}.<br>
                            If you were not expecting this message, contact your system administrator.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

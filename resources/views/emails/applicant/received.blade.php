<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Application received</title></head>
<body style="margin:0;background:#f4f7fa;color:#14213d;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f7fa;padding:28px 12px;"><tr><td align="center">
    <table role="presentation" width="620" cellspacing="0" cellpadding="0" style="width:100%;max-width:620px;overflow:hidden;border:1px solid #dbe4ee;border-radius:16px;background:#fff;">
        <tr><td style="background:#06152f;padding:26px 30px;color:#fff;"><div style="color:#73d9ff;font-size:11px;font-weight:800;letter-spacing:1.3px;text-transform:uppercase;">3PAP PMIS</div><div style="margin-top:7px;font-size:23px;font-weight:800;">Application received</div></td></tr>
        <tr><td style="padding:30px;font-size:15px;line-height:1.7;"><p style="margin:0 0 16px;">Dear {{ $applicant->name ?? $applicant->organization_name ?? 'Applicant' }},</p><p style="margin:0 0 18px;">Your application has been received and registered for review.</p><div style="border-left:4px solid #1687ea;border-radius:5px;background:#eff8ff;padding:14px 16px;"><strong>Application code:</strong> {{ $loginCode }}<br><strong>Temporary password:</strong> {{ $defaultPassword }}</div><p style="margin:18px 0 0;color:#60728b;font-size:13px;">Keep these details private. You will be asked to change the temporary password after signing in.</p></td></tr>
        <tr><td style="border-top:1px solid #dbe4ee;background:#f4f7fa;padding:18px 30px;color:#60728b;font-size:12px;">3PAP PMIS &middot; Portfolio Management Information System &middot; info@3pap.com</td></tr>
    </table>
</td></tr></table>
</body>
</html>

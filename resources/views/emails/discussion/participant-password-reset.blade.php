<!doctype html>
<html lang="en">
<body style="margin:0;background:#f4f7fa;font-family:Arial,sans-serif;color:#14213d;line-height:1.6;">
    <div style="max-width:620px;margin:0 auto;padding:32px 18px;">
        <div style="background:#ffffff;border:1px solid #dbe4ee;border-radius:14px;padding:30px;">
            <p style="margin:0 0 8px;color:#0A6ED1;font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">3PAP PMIS community</p>
            <h1 style="margin:0 0 18px;font-size:25px;color:#14213d;">Reset your discussion password</h1>
            <p>Hello {{ $participant->display_name }},</p>
            <p>We received a request to reset the password for your discussion participant account.</p>
            <p style="margin:28px 0;">
                <a href="{{ $resetUrl }}" style="display:inline-block;background:#0A6ED1;color:#ffffff;padding:12px 20px;border-radius:2px;text-decoration:none;font-weight:700;">
                    Reset discussion password
                </a>
            </p>
            <p>This secure link expires in {{ $expiresInMinutes }} minutes and can be used only once.</p>
            <p>If you did not request a password reset, you can ignore this email. Your current password will remain unchanged.</p>
            <p style="margin-top:26px;color:#60728B;font-size:13px;">3PAP PMIS public discussion community</p>
        </div>
    </div>
</body>
</html>

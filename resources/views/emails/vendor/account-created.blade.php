@extends('emails.layouts.notification', [
    'title' => 'Your Vendor Portal Account',
    'preheader' => 'Your vendor portal sign-in credentials are ready.',
    'actionUrl' => $portalUrl,
    'actionText' => 'Sign In to Vendor Portal',
])

@section('content')
    <p style="margin:0 0 15px;">Hello {{ $vendor->name ?: 'Vendor' }},</p>
    <p style="margin:0 0 18px;">A 3PAP PMIS vendor portal account has been created for you.</p>

    <div style="border-left:4px solid #1687ea;border-radius:6px;background:#eff8ff;padding:15px 17px;">
        <div style="margin-bottom:7px;"><strong>Email:</strong> {{ $vendor->email }}</div>
        <div style="word-break:break-all;"><strong>Temporary password:</strong> {{ $plainPassword }}</div>
    </div>

    <p style="margin:18px 0 0;">Change the temporary password when prompted. Do not share or forward these credentials.</p>
@endsection

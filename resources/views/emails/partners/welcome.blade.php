@extends('emails.layouts.notification', [
    'title' => 'Welcome to the Funding Partner Portal',
    'preheader' => 'Your 3PAP PMIS funding partner account is ready.',
    'actionUrl' => $loginUrl,
    'actionText' => 'Open Partner Portal',
])

@section('content')
    <p style="margin:0 0 15px;">Hello {{ $user->name ?: ($funder->contact_person ?: 'Funding partner') }},</p>
    <p style="margin:0 0 18px;">Portal access has been created for {{ $funder->name }}. Use the credentials below to sign in.</p>

    <div style="border-left:4px solid #1687ea;border-radius:6px;background:#eff8ff;padding:15px 17px;">
        <div style="margin-bottom:7px;"><strong>Email:</strong> {{ $user->email }}</div>
        <div style="word-break:break-all;"><strong>Temporary password:</strong> {{ $plainPassword }}</div>
    </div>

    <p style="margin:18px 0 0;">For security, change the temporary password when prompted and do not forward this message.</p>
@endsection

@extends('emails.layouts.notification', [
    'title' => 'Welcome to 3PAP PMIS',
    'preheader' => 'Your employee account has been created.',
    'actionUrl' => route('login'),
    'actionText' => 'Sign In to 3PAP PMIS',
])

@section('content')
    <p style="margin:0 0 15px;">Hello {{ $name ?: 'Colleague' }},</p>
    <p style="margin:0 0 18px;">Congratulations. Your {{ $userType ?: 'employee' }} account has been created following completion of the hiring process.</p>

    <div style="border-left:4px solid #1687ea;border-radius:6px;background:#eff8ff;padding:15px 17px;">
        <div style="margin-bottom:7px;"><strong>Email:</strong> {{ $email }}</div>
        <div style="word-break:break-all;"><strong>Temporary password:</strong> {{ $password }}</div>
    </div>

    <p style="margin:18px 0 0;">Change this temporary password when prompted and do not forward these credentials.</p>
@endsection

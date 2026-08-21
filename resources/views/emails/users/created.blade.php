@component('mail::message')
# Your 3PAP PMIS Account Has Been Created

Hello {{ $user->name ?? 'User' }},

An account has been created for you on the 3PAP PMIS Administration system.

@component('mail::panel')
Email: {{ $user->email }}

Temporary password: {{ $plainPassword }}
@endcomponent

Use these credentials to sign in. For security, you may be asked to change your password after your first login.

@component('mail::button', ['url' => route('login')])
Sign in to 3PAP PMIS
@endcomponent

Thanks,<br>
{{ config('app.name', '3PAP PMIS') }}
@endcomponent

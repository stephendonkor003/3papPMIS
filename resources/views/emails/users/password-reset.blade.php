@component('mail::message')
# Password Reset

Hello {{ $user->name ?? 'User' }},

Your 3PAP PMIS account password has been reset by an administrator.

@component('mail::panel')
Email: {{ $user->email }}

Temporary password: {{ $plainPassword }}
@endcomponent

Please sign in and change this password immediately if the system asks you to do so.

@component('mail::button', ['url' => route('login')])
Sign in to 3PAP PMIS
@endcomponent

If you did not request this reset, please contact the 3PAP PMIS administrator.

Thanks,<br>
{{ config('app.name', '3PAP PMIS') }}
@endcomponent

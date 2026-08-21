<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Thanks for signing up. Before continuing, verify your email address using the link we sent you. If the message did not arrive, you can request another one below.
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            A new verification link has been sent to the email address on your account.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>Resend Verification Email</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Log Out
            </button>
        </form>
    </div>
</x-guest-layout>

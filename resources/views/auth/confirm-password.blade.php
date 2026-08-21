<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        This is a secure area. Confirm your password before continuing.
    </div>

    <form method="POST" action="{{ url('/confirm-password') }}">
        @csrf

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                autocomplete="current-password"
                required
                autofocus
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>Confirm Password</x-primary-button>
        </div>
    </form>
</x-guest-layout>

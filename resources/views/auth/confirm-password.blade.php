<x-guest-layout>
    <div class="mb-4 text-sm text-gray-400">
        {{ __('auth.confirm_password_secure_area') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('auth.password')" class="text-gray-300" />

            <x-text-input id="password" class="block mt-1 w-full bg-black/50 border-white/20 text-white"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('auth.confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

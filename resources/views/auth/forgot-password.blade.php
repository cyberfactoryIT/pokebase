<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('messages.Forgot_your_password_No_problem_Just_let_us_know_your_email_address_and_we_will_email_you_a_password_reset_link_that_will_allow_you_to_choose_a_new_one') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('messages.Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('messages.Email_Password_Reset_Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

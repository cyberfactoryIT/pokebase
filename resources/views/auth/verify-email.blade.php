<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('messages.Thanks_for_signing_up_Before_getting_started_could_you_verify_your_email_address_by_clicking_on_the_link_we_just_emailed_to_you_If_you_didn\'t_receive_the_email_we_will_gladly_send_you_another') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('messages.A_new_verification_link_has_been_sent_to_the_email_address_you_provided_during_registration') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('messages.Resend_Verification_Email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('messages.Log_Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>

<x-guest-layout>
    <div class="flex justify-end items-center mb-4">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/10 text-gray-300 font-semibold shadow hover:bg-white/20">
            <i class="fa fa-arrow-left"></i> {{ __('auth.back') }}
        </a>
    </div>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        @if(config('organizations.enabled'))
        <!-- Organization CVR -->
        <div class="mt-4">
            <x-input-label for="organization_cvr" :value="__('auth.organization_cvr')" class="text-gray-300" />
            <x-text-input id="organization_cvr" class="block mt-1 w-full bg-black/50 border-white/20 text-white" type="text" name="organization_cvr" :value="old('organization_cvr')" required autofocus />
            <x-input-error :messages="$errors->get('organization_cvr')" class="mt-2" />
        </div>

        <!-- Organization Name -->
        <div class="mt-4">
            <x-input-label for="organization_name" :value="__('auth.organization_name')" class="text-gray-300" />
            <x-text-input id="organization_name" class="block mt-1 w-full bg-black/50 border-white/20 text-white" type="text" name="organization_name" :value="old('organization_name')" required autofocus />
            <x-input-error :messages="$errors->get('organization_name')" class="mt-2" />
        </div>

        <!-- Organization Code -->
        <div class="mt-4">
            <x-input-label for="organization_code" :value="__('auth.organization_code')" class="text-gray-300" />
            <x-text-input id="organization_code" class="block mt-1 w-full bg-black/50 border-white/20 text-white" type="text" name="organization_code" :value="old('organization_code')" required />
            <x-input-error :messages="$errors->get('organization_code')" class="mt-2" />
        </div>

        <!-- Organization Address (street, zipcode, city) -->
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-6 gap-4">
            <div class="sm:col-span-4">
                <x-input-label for="organization_address" :value="__('auth.organization_address')" class="text-gray-300" />
                <x-text-input id="organization_address" class="block mt-1 w-full bg-black/50 border-white/20 text-white" type="text" name="organization_address" :value="old('organization_address')" required />
                <x-input-error :messages="$errors->get('organization_address')" class="mt-2" />
            </div>

            <div class="sm:col-span-1">
                <x-input-label for="organization_zipcode" :value="__('auth.organization_zipcode')" class="text-gray-300" />
                <x-text-input id="organization_zipcode" class="block mt-1 w-full bg-black/50 border-white/20 text-white" type="text" name="organization_zipcode" :value="old('organization_zipcode')" required />
                <x-input-error :messages="$errors->get('organization_zipcode')" class="mt-2" />
            </div>

            <div class="sm:col-span-1">
                <x-input-label for="organization_city" :value="__('auth.organization_city')" class="text-gray-300" />
                <x-text-input id="organization_city" class="block mt-1 w-full bg-black/50 border-white/20 text-white" type="text" name="organization_city" :value="old('organization_city')" required />
                <x-input-error :messages="$errors->get('organization_city')" class="mt-2" />
            </div>
        </div>
        @else
            
        @endif

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('auth.name')" class="text-gray-300" />
            <x-text-input id="name" class="block mt-1 w-full bg-black/50 border-white/20 text-white" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('auth.email')" class="text-gray-300" />
            <x-text-input id="email" class="block mt-1 w-full bg-black/50 border-white/20 text-white" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('auth.password')" class="text-gray-300" />

            <x-text-input id="password" class="block mt-1 w-full bg-black/50 border-white/20 text-white"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('auth.confirm_password')" class="text-gray-300" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full bg-black/50 border-white/20 text-white"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Preferred Game -->
        <div class="mt-4">
            <x-input-label for="preferred_game_id" :value="__('auth.preferred_game')" class="text-gray-300" />
            <select id="preferred_game_id" name="preferred_game_id" class="block mt-1 w-full bg-black/50 border-white/20 text-white rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                <option value="">{{ __('auth.select_game') }}</option>
                @foreach($games as $game)
                    <option value="{{ $game->id }}" {{ old('preferred_game_id') == $game->id ? 'selected' : '' }}>
                        {{ $game->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('preferred_game_id')" class="mt-2" />
            <p class="mt-1 text-sm text-gray-400">{{ __('auth.preferred_game_description') }}</p>
        </div>

        <!-- Trial Code (Optional) -->
        <div class="mt-4">
            <x-input-label for="trial_code" :value="__('auth.trial_code')" class="text-gray-300" />
            <x-text-input id="trial_code" class="block mt-1 w-full bg-black/50 border-white/20 text-white uppercase" type="text" name="trial_code" :value="old('trial_code')" placeholder="{{ __('auth.trial_code_placeholder') }}" maxlength="50" />
            <x-input-error :messages="$errors->get('trial_code')" class="mt-2" />
            <p class="mt-1 text-sm text-gray-400">{{ __('auth.trial_code_description') }}</p>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-400 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" href="{{ route('login') }}">
                {{ __('auth.already_registered') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('auth.register') }}
            </x-primary-button>
        </div>
    </form>
    <script>
        (function(){
            const cvrInput = document.getElementById('organization_cvr');
            if (!cvrInput) return;
            let timer = null;
            cvrInput.addEventListener('input', function(){
                const val = this.value.replace(/\D/g,'');
                if (timer) clearTimeout(timer);
                timer = setTimeout(()=>{
                    if (val.length === 8) {
                        fetch('{{ route('company.info.lookup') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ cvr: val })
                        }).then(async r=>{
                            const text = await r.text();
                            try {
                                const data = JSON.parse(text);
                                if (data && !data.error) {
                                    if (data.name) document.getElementById('organization_name').value = data.name;
                                    if (data.address) document.getElementById('organization_address').value = data.address;
                                    if (data.zipcode) document.getElementById('organization_zipcode').value = data.zipcode;
                                    if (data.city) document.getElementById('organization_city').value = data.city;
                                } else {
                                    console.warn('Company lookup returned error', data);
                                }
                            } catch (e) {
                                console.error('Company lookup returned non-JSON response', text);
                            }
                        }).catch(err=>console.error(err));
                    }
                }, 300);
            });
        })();
    </script>
</x-guest-layout>

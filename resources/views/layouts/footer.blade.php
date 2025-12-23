<footer class="bg-[#161615] border-t border-white/15 mt-8 py-4 text-center text-gray-400">
    <div>
        {!! __('messages.copyright', ['year' => date('Y')]) !!}<br>
        {{ __('messages.footer_dummy', [
            'privacy' => __('messages.footer_privacy'),
            'terms' => __('messages.footer_terms'),
            'contact' => __('messages.footer_contact')
        ]) }}
    </div>
    <div class="mt-4 flex items-center justify-center gap-4">
        <form method="POST" action="{{ route('locale.switch') }}" class="inline-block">
            @csrf
            <select name="locale" onchange="this.form.submit()" class="px-2 py-1 rounded border-gray-300 text-gray-700">
                <option value="da" @if(app()->getLocale() == 'da') selected @endif>{{ __('messages.danish') }}</option>
                <option value="en" @if(app()->getLocale() == 'en') selected @endif>{{ __('messages.english') }}</option>
                <option value="it" @if(app()->getLocale() == 'it') selected @endif>{{ __('messages.italian') }}</option>
            </select>
        </form>
        @auth
        <div class="inline-block">
            <select id="footer-theme-selector" class="px-2 py-1 rounded border-gray-300 text-gray-700">
                <option value="dark" @if((Auth::user()->theme ?? 'dark') == 'dark') selected @endif>{{ __('messages.dark') }}</option>
                <option value="light" @if((Auth::user()->theme ?? 'dark') == 'light') selected @endif>{{ __('messages.light') }}</option>
                <option value="pokemon" @if((Auth::user()->theme ?? 'dark') == 'pokemon') selected @endif>{{ __('messages.pokemon') }}</option>
                <option value="pokemon-light" @if((Auth::user()->theme ?? 'dark') == 'pokemon-light') selected @endif>{{ __('messages.pokemon_light') }}</option>
                <option value="gameboy" @if((Auth::user()->theme ?? 'dark') == 'gameboy') selected @endif>{{ __('messages.gameboy') }}</option>
            </select>
        </div>
        @endauth
    </div>
    @auth
    <script>
        document.getElementById('footer-theme-selector')?.addEventListener('change', function() {
            const theme = this.value;
            fetch('{{ route('user.theme.update') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ theme: theme })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        });
    </script>
    @endauth
</footer>

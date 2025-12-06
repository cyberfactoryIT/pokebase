<footer class="bg-white border-t mt-8 py-4 text-center text-gray-400">
    <div>
        {!! __('messages.copyright', ['year' => date('Y')]) !!}<br>
        {{ __('messages.footer_dummy', [
            'privacy' => __('messages.footer_privacy'),
            'terms' => __('messages.footer_terms'),
            'contact' => __('messages.footer_contact')
        ]) }}
    </div>
    <div class="mt-4">
        <form method="POST" action="{{ route('locale.switch') }}" class="inline-block">
            @csrf
            <select name="locale" onchange="this.form.submit()" class="px-2 py-1 rounded border-gray-300 text-gray-700">
                <option value="da" @if(app()->getLocale() == 'da') selected @endif>{{ __('messages.danish') }}</option>
                <option value="en" @if(app()->getLocale() == 'en') selected @endif>{{ __('messages.english') }}</option>
                <option value="it" @if(app()->getLocale() == 'it') selected @endif>{{ __('messages.italian') }}</option>
            </select>
        </form>
    </div>
</footer>

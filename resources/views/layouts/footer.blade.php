<footer class="border-t border-white/10 py-12 px-6 lg:px-8 bg-black">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <!-- Logo and Brand -->
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo_basecard.svg') }}" alt="{{ config('app.name') }}" class="h-8 w-auto">
                <span class="text-xl font-bold">{{ config('app.name') }}</span>
            </div>
            
            <!-- Links -->
            <div class="flex gap-8 text-sm text-gray-400">
                <a href="{{ route('privacy') }}" class="hover:text-white transition">{{ __('footer.privacy_policy') }}</a>
                <a href="{{ route('terms') }}" class="hover:text-white transition">{{ __('footer.terms_of_service') }}</a>
                <a href="{{ route('contact') }}" class="hover:text-white transition">{{ __('footer.contact') }}</a>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="mt-8 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('footer.all_rights_reserved') }}
        </div>
    </div>
</footer>

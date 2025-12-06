<nav x-data="{ open: false }" class="bg-white border-b shadow-sm">
    @auth
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <img src="/img/logo.png" alt="Logo" class="h-8 w-auto">
                    <span class="font-bold text-lg text-gray-700">Evalua</span>
                </a>
                <div class="hidden md:flex gap-4 ml-8">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg hover:bg-gray-100 transition {{ request()->routeIs('dashboard') ? 'bg-gray-100 font-bold' : '' }}">
                        <i class="fa fa-home mr-2 text-blue-500"></i>{{ __('messages.Dashboard') }}
                    </a>
                </div>
            </div>
            <div class="hidden md:flex items-center gap-4">
                
                <div class="flex items-center gap-2">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D8ABC&color=fff&size=32" alt="Avatar" class="h-8 w-8 rounded-full">
                    <span class="text-gray-700 font-medium">{{ Auth::user()->name }}</span>
                </div>
                
                <div class="relative">
                    <button class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-600 font-medium focus:outline-none" @click="open = !open">
                        <i class="fa fa-chevron-down"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg z-10">
                        <form method="POST" action="{{ route('locale.switch') }}" class="px-4 py-2">
                            @csrf
                            <select name="locale" onchange="this.form.submit()" class="px-2 py-1 rounded border-gray-300 text-gray-700 w-full">
                                <option value="da" @if(app()->getLocale() == 'da') selected @endif>{{ __('messages.danish') }}</option>
                                <option value="en" @if(app()->getLocale() == 'en') selected @endif>{{ __('messages.english') }}</option>
                                <option value="it" @if(app()->getLocale() == 'it') selected @endif>{{ __('messages.italian') }}</option>
                            </select>
                        </form>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">{{ __('messages.Profile') }}</a>
                        @if(Auth::user()->hasRole('admin'))
                            <a href="{{ route('billing.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">{{ __('messages.Billing_Plans') }}</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">{{ __('messages.Log_Out') }}</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="md:hidden flex items-center">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <div :class="{'block': open, 'hidden': ! open}" class="md:hidden hidden">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">{{ __('messages.Dashboard') }}</a>
        </div>
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">{{ __('messages.Profile') }}</a>
                @if(Auth::user()->hasRole('admin'))
                    <a href="{{ route('billing.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">{{ __('messages.Billing_Plans') }}</a>
                @endif
                <form method="POST" action="{{ route('locale.switch') }}" class="mb-3">
                    @csrf
                    <select name="locale" onchange="this.form.submit()" class="px-2 py-1 rounded border-gray-300 text-gray-700 w-full">
                        <option value="da" @if(app()->getLocale() == 'da') selected @endif>{{ __('messages.danish') }}</option>
                        <option value="en" @if(app()->getLocale() == 'en') selected @endif>{{ __('messages.english') }}</option>
                        <option value="it" @if(app()->getLocale() == 'it') selected @endif>{{ __('messages.italian') }}</option>
                    </select>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">{{ __('messages.Log_Out') }}</button>
                </form>
            </div>
        </div>
    </div>
    @endauth
</nav>

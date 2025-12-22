<nav class="flex-1 px-4 py-6 space-y-2">
    @auth
        @if(! auth()->user()->hasRole('superadmin'))
        <a href="{{ route('tcg.expansions.index') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('tcg.expansions.*') ? 'bg-white/20 font-bold text-white' : '' }}">
            <img src="/images/logos/logo_pokemon.png" alt="Pokemon" class="w-6 h-6 object-contain">
            <span>{{ __('catalogue.expansions_title') }}</span>
        </a>
        @endif
    @endauth
    @if(auth()->user() && auth()->user()->hasRole('superadmin'))
    <div class="mt-4 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('messages.superadmin') }}</div>
        <a href="{{ route('admin.activitylog.index') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('admin.activitylog.index') ? 'bg-white/20 font-bold text-white' : '' }}">
            <i class="fa fa-history text-purple-500"></i>
            <span>{{ __('messages.nav.activity_log') }}</span>
        </a>
        @if(config('organizations.enabled'))
        <a href="{{ route('superadmin.organizations.index') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('superadmin.organizations.index') ? 'bg-white/20 font-bold text-white' : '' }}">
            <i class="fa fa-building text-green-400"></i>
            <span>{{ __('messages.nav.all_organizations') }}</span>
        </a>
        @endif
        <a href="{{ route('superadmin.plans.index') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('superadmin.plans.index') ? 'bg-white/20 font-bold text-white' : '' }}">
            <i class="fa fa-coins text-yellow-400"></i>
            <span>{{ __('messages.nav.pricing_plans') }}</span>
        </a>
        <a href="{{ route('superadmin.promotions.index') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('superadmin.promotions.index') ? 'bg-white/20 font-bold text-white' : '' }}">
            <i class="fa fa-gift text-pink-400"></i>
            <span>{{ __('messages.nav.promotions') }}</span>
        </a>
        <a href="{{ route('admin.invoices.index') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('admin.invoices.index') ? 'bg-white/20 font-bold text-white' : '' }}">
            <i class="fas fa-file-invoice text-indigo-400"></i>
            <span>{{ __('messages.nav.all_invoices') }}</span>
        </a>
        <a href="{{ route('faq.index') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 rounded-lg hover:bg-white/10 mt-4">
            <i class="fa fa-question-circle text-gray-400"></i>
            <span>{{ __('messages.nav.support_management') }}</span>
        </a>
        <a href="/superadmin/helps" class="flex items-center gap-3 px-3 py-2 text-gray-300 rounded-lg hover:bg-white/10 mt-4">
            <i class="fa fa-question-circle text-gray-500"></i>
            <span>{{ __('messages.nav.help_management') }}</span>
        </a>
    @endif
    @if(auth()->user() && auth()->user()->hasRole('admin'))
    <div class="mt-4 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('messages.admin') }}</div>
        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('users.index') ? 'bg-white/20 font-bold text-white' : '' }}">
            <i class="fa fa-users text-blue-400"></i>
            <span>{{ __('messages.nav.user_management') }}</span>
        </a>
        @if(config('organizations.enabled'))
        <a href="{{ route('admin.organization.edit') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('admin.organization.edit') ? 'bg-white/20 font-bold text-white' : '' }}">
            <i class="fa fa-building text-green-400"></i>
            <span>{{ __('messages.nav.organization_management') }}</span>
        </a>
        @endif
        <a href="{{ route('admin.activitylog.index') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('admin.activitylog.index') ? 'bg-white/20 font-bold text-white' : '' }}">
            <i class="fa fa-history text-purple-400"></i>
            <span>{{ __('messages.nav.activity_log') }}</span>
        </a>
    @endif

    @auth
        <a href="{{ route('support.index') }}" class="flex items-center gap-3 px-3 py-2 text-gray-300 rounded-lg hover:bg-white/10 mt-4">
            <i class="fa fa-question-circle text-gray-400"></i>
            <span>{{ __('messages.nav.support') }}</span>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button type="submit" class="flex items-center gap-3 px-3 py-2 text-gray-300 rounded-lg hover:bg-white/10 w-full text-left transition">
                <i class="fa fa-sign-out-alt text-red-400"></i>
                <span>{{ __('messages.nav.log_out') }}</span>
            </button>
        </form>
    @endauth
    
</nav>

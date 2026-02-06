<!-- Cookie Consent Banner -->
<div 
    x-data="cookieConsent()" 
    x-show="!hasConsent" 
    x-cloak
    class="fixed bottom-0 left-0 right-0 z-50 p-4 bg-gradient-to-r from-purple-900/95 to-indigo-900/95 backdrop-blur-lg border-t border-purple-500/30 shadow-2xl"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-full"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-full"
>
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Text Content -->
            <div class="flex-1 text-white">
                <div class="flex items-start gap-3">
                    <div class="text-3xl">🍪</div>
                    <div>
                        <h3 class="font-bold text-lg mb-1">{{ __('cookies.banner_title') }}</h3>
                        <p class="text-sm text-gray-200">
                            {{ __('cookies.banner_description') }}
                            <a href="{{ route('privacy') }}" class="underline hover:text-purple-300 transition">
                                {{ __('cookies.privacy_policy') }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Settings Button -->
                <button 
                    @click="showSettings = !showSettings"
                    class="px-4 py-2 text-sm font-medium text-white border border-white/30 rounded-lg hover:bg-white/10 transition"
                >
                    <i class="fas fa-cog mr-2"></i>
                    {{ __('cookies.customize') }}
                </button>

                <!-- Reject Button -->
                <button 
                    @click="rejectAll()"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-700 rounded-lg hover:bg-gray-600 transition"
                >
                    {{ __('cookies.reject_all') }}
                </button>

                <!-- Accept Button -->
                <button 
                    @click="acceptAll()"
                    class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg hover:from-purple-700 hover:to-indigo-700 transition shadow-lg"
                >
                    {{ __('cookies.accept_all') }}
                </button>
            </div>
        </div>

        <!-- Settings Panel (collapsible) -->
        <div 
            x-show="showSettings" 
            x-collapse
            class="mt-4 pt-4 border-t border-white/20"
        >
            <div class="grid md:grid-cols-3 gap-4">
                <!-- Necessary Cookies (always enabled) -->
                <div class="bg-white/10 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-white">
                            <i class="fas fa-shield-alt mr-2 text-green-400"></i>
                            {{ __('cookies.necessary_title') }}
                        </h4>
                        <span class="text-xs bg-green-500 text-white px-2 py-1 rounded">{{ __('cookies.always_active') }}</span>
                    </div>
                    <p class="text-xs text-gray-300">{{ __('cookies.necessary_description') }}</p>
                </div>

                <!-- Analytics Cookies -->
                <div class="bg-white/10 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-white">
                            <i class="fas fa-chart-line mr-2 text-blue-400"></i>
                            {{ __('cookies.analytics_title') }}
                        </h4>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="preferences.analytics" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>
                    <p class="text-xs text-gray-300">{{ __('cookies.analytics_description') }}</p>
                </div>

                <!-- Marketing Cookies -->
                <div class="bg-white/10 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-white">
                            <i class="fas fa-bullhorn mr-2 text-yellow-400"></i>
                            {{ __('cookies.marketing_title') }}
                        </h4>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="preferences.marketing" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>
                    <p class="text-xs text-gray-300">{{ __('cookies.marketing_description') }}</p>
                </div>
            </div>

            <!-- Save Preferences Button -->
            <div class="mt-4 text-center">
                <button 
                    @click="savePreferences()"
                    class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg hover:from-purple-700 hover:to-indigo-700 transition shadow-lg"
                >
                    {{ __('cookies.save_preferences') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function cookieConsent() {
    return {
        hasConsent: false,
        showSettings: false,
        preferences: {
            necessary: true,  // Always true
            analytics: false,
            marketing: false
        },

        init() {
            // Check if user has already given consent
            const consent = localStorage.getItem('cookieConsent');
            if (consent) {
                this.hasConsent = true;
                this.preferences = JSON.parse(consent);
                this.loadScripts();
            }
        },

        acceptAll() {
            this.preferences = {
                necessary: true,
                analytics: true,
                marketing: true
            };
            this.saveConsent();
        },

        rejectAll() {
            this.preferences = {
                necessary: true,
                analytics: false,
                marketing: false
            };
            this.saveConsent();
        },

        savePreferences() {
            this.saveConsent();
        },

        saveConsent() {
            localStorage.setItem('cookieConsent', JSON.stringify(this.preferences));
            this.hasConsent = true;
            this.showSettings = false;
            this.loadScripts();
        },

        loadScripts() {
            // Load analytics if accepted
            if (this.preferences.analytics) {
                this.loadAnalytics();
            }

            // Load marketing scripts if accepted
            if (this.preferences.marketing) {
                this.loadMarketing();
            }
        },

        loadAnalytics() {
            // Google Analytics or Plausible
            const analyticsId = '{{ config("services.analytics.id") }}';
            const analyticsType = '{{ config("services.analytics.type") }}';

            if (analyticsType === 'google' && analyticsId) {
                // Google Analytics
                if (!document.querySelector('script[src*="googletagmanager"]')) {
                    const script1 = document.createElement('script');
                    script1.async = true;
                    script1.src = `https://www.googletagmanager.com/gtag/js?id=${analyticsId}`;
                    document.head.appendChild(script1);

                    const script2 = document.createElement('script');
                    script2.innerHTML = `
                        window.dataLayer = window.dataLayer || [];
                        function gtag(){dataLayer.push(arguments);}
                        gtag('js', new Date());
                        gtag('config', '${analyticsId}', {
                            'anonymize_ip': true,
                            'cookie_flags': 'SameSite=None;Secure'
                        });
                    `;
                    document.head.appendChild(script2);
                }
            } else if (analyticsType === 'plausible' && analyticsId) {
                // Plausible Analytics
                if (!document.querySelector('script[src*="plausible"]')) {
                    const script = document.createElement('script');
                    script.defer = true;
                    script.setAttribute('data-domain', analyticsId);
                    script.src = 'https://plausible.io/js/script.js';
                    document.head.appendChild(script);
                }
            }
        },

        loadMarketing() {
            // Add marketing scripts here (Facebook Pixel, LinkedIn Insight, etc.)
            console.log('Marketing cookies accepted - load marketing scripts here');
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>

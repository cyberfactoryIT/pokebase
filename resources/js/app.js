import './bootstrap';
import './cardSearch';
import './quickAddCard';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Cookie Consent Component - must be registered before Alpine.start()
document.addEventListener('alpine:init', () => {
    Alpine.data('cookieConsent', () => ({
        hasConsent: false,
        showSettings: false,
        preferences: {
            necessary: true,
            analytics: false,
            marketing: false
        },

        init() {
            const consent = localStorage.getItem('cookieConsent');
            if (consent) {
                this.hasConsent = true;
                this.preferences = JSON.parse(consent);
                this.loadScripts();
            }
        },

        acceptAll() {
            this.preferences = { necessary: true, analytics: true, marketing: true };
            this.saveConsent();
        },

        rejectAll() {
            this.preferences = { necessary: true, analytics: false, marketing: false };
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
            if (this.preferences.analytics) this.loadAnalytics();
            if (this.preferences.marketing) this.loadMarketing();
        },

        loadAnalytics() {
            const analyticsId = window.appConfig?.analyticsId;
            const analyticsType = window.appConfig?.analyticsType;

            if (analyticsType === 'google' && analyticsId) {
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
            console.log('Marketing cookies accepted');
        }
    }));
});

Alpine.start();

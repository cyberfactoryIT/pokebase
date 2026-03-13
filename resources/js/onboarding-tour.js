import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : null;
}

async function postOnboardingStatus(path) {
    const token = getCsrfToken();
    if (!token) {
        return;
    }

    try {
        await fetch(path, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({}),
        });
    } catch (e) {
        // Silently ignore network errors - tour should not break the app
        console.error('Failed to update onboarding status', e);
    }
}

function buildSteps(translations) {
    const stepsConfig = translations?.steps || {};

    const candidates = [
        { selector: '[data-tour="dashboard"]', key: 'dashboard' },
        { selector: '[data-tour="search-cards"]', key: 'search' },
        { selector: '[data-tour="collection"]', key: 'collection' },
        { selector: '[data-tour="deck"]', key: 'deck' },
        { selector: '[data-tour="upgrade"]', key: 'upgrade' },
    ];

    const steps = [];

    for (const item of candidates) {
        const element = document.querySelector(item.selector);
        if (!element) {
            continue;
        }

        const stepTrans = stepsConfig[item.key] || {};

        steps.push({
            element,
            popover: {
                title: stepTrans.title || '',
                description: stepTrans.description || '',
                side: 'bottom',
                align: 'start',
            },
        });
    }

    return steps;
}

export function startOnboardingTour(options = {}) {
    const config = window.appConfig || {};
    const translations = config.onboardingTour || {};

    const steps = buildSteps(translations);
    if (!steps.length) {
        return;
    }

    const driverObj = driver({
        showProgress: false,
        allowClose: true,
        nextBtnText: translations.buttons?.next || 'Next',
        prevBtnText: translations.buttons?.previous || 'Previous',
        doneBtnText: translations.buttons?.done || 'Done',
        showButtons: ['next', 'previous', 'close'],
        steps,
        onNextClick: () => {
            // If this is the last step, treat as completion
            if (driverObj.isLastStep()) {
                driverObj.destroy();
                postOnboardingStatus('/onboarding-tour/completed');
            } else {
                driverObj.moveNext();
            }
        },
        onCloseClick: () => {
            driverObj.destroy();
            postOnboardingStatus('/onboarding-tour/skipped');
        },
    });

    driverObj.drive();
}

function setupOnboardingAutoStart() {
    const config = window.appConfig || {};
    if (!config.shouldAutoStartOnboardingTour) {
        return;
    }

    // Wait a bit to allow Livewire/Alpine to hydrate the DOM
    setTimeout(() => {
        startOnboardingTour({ auto: true });
    }, 600);
}

function setupOnboardingBindings() {
    // Manual trigger buttons
    document.querySelectorAll('[data-onboarding-show-tour="true"]').forEach((button) => {
        // Avoid attaching duplicate listeners
        if (button.__onboardingBound) {
            return;
        }
        button.__onboardingBound = true;

        button.addEventListener('click', (event) => {
            event.preventDefault();
            // Small delay to make sure Livewire/Alpine-rendered elements are present
            setTimeout(() => startOnboardingTour({ auto: false }), 200);
        });
    });

    setupOnboardingAutoStart();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupOnboardingBindings);
} else {
    setupOnboardingBindings();
}


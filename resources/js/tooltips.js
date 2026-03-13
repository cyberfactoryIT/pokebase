import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';

function initTooltips(root = document) {
    const elements = root.querySelectorAll('[data-tooltip]');

    elements.forEach((el) => {
        if (el._tippy) {
            return;
        }

        const content = el.getAttribute('data-tooltip');
        if (!content) {
            return;
        }

        const placement = el.getAttribute('data-tooltip-placement') || 'top';

        tippy(el, {
            content,
            placement,
            maxWidth: 260,
            theme: 'light-border',
            arrow: true,
            animation: 'shift-away-subtle',
            delay: [150, 100],
            interactive: false,
            trigger: 'mouseenter focus',
        });
    });
}

function setupLivewireHooks() {
    if (typeof window.livewire !== 'undefined') {
        document.addEventListener('livewire:navigated', () => {
            initTooltips(document);
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initTooltips(document);
        setupLivewireHooks();
    });
} else {
    initTooltips(document);
    setupLivewireHooks();
}

// Expose for manual re-init if ever needed
window.AppTooltips = {
    init: initTooltips,
};


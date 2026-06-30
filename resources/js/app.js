// import './bootstrap';
import './hover-profile';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
Alpine.plugin(collapse);

import 'flowbite';

window.Alpine = Alpine;

const sitesphereSwal = {
    getTheme() {
        const style = getComputedStyle(document.documentElement);
        return {
            background: style.getPropertyValue('--background-color').trim() || '#ffffff',
            color: style.getPropertyValue('--text-color').trim() || '#0d1b2a',
            confirmButtonColor: style.getPropertyValue('--accent-color').trim() || '#6c5ce7',
            fontFamily: style.getPropertyValue('--font-family').trim() || 'Figtree, sans-serif'
        };
    },
    async getSwal() {
        if (window.Swal) return window.Swal;
        try {
            const module = await import('https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.esm.all.min.js');
            window.Swal = module.default;
            return window.Swal;
        } catch (error) {
            console.error('Failed to load SweetAlert2:', error);
            return { 
                fire: (opt) => Promise.resolve({ isConfirmed: false }),
                mixin: () => ({ fire: () => Promise.resolve({ isConfirmed: false }) })
            };
        }
    },
    async fire(options = {}) {
        const theme = this.getTheme();
        const Swal = await this.getSwal();
        const { didOpen, ...rest } = options;
        return await Swal.fire({
            background: theme.background,
            color: theme.color,
            confirmButtonColor: theme.confirmButtonColor,
            didOpen: (popup) => {
                popup.style.fontFamily = theme.fontFamily;
                if (didOpen) didOpen(popup);
            },
            ...rest
        });
    },
    async confirm(options = {}) {
        const { didOpen, ...rest } = options;
        return await this.fire({
            icon: 'question',
            title: 'Are you sure?',
            showCancelButton: true,
            cancelButtonColor: '#d33',
            ...rest,
            didOpen: (popup) => {
                if (didOpen) didOpen(popup);
            }
        });
    },
    async toast(options = {}) {
        const theme = this.getTheme();
        const Swal = await this.getSwal();
        const { didOpen, ...rest } = options;
        return await Swal.fire({
            toast: true,
            position: options.position || window.toastPosition || 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            icon: 'success',
            background: theme.background,
            color: theme.color,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
                toast.style.fontFamily = theme.fontFamily;
                if (didOpen) didOpen(toast);
            },
            ...rest
        });
    }
};

window.sitesphereSwal = sitesphereSwal;

/**
 * Dynamic favicon: updates the browser tab icon color
 * to match the current --accent-color CSS custom property.
 */
function updateFavicon() {
    const accent = getComputedStyle(document.documentElement)
        .getPropertyValue('--accent-color').trim() || '#6c5ce7';

    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 88.5 99.5" fill="none">
        <path fill="${accent}" d="M44.5 28.75L28.75 37.25L28.75 38.75L63.25 58.5L66.5 60.75L65.75 62.5L43.75 74.25L9.75 54.25L7.75 53.25L6 54L6.25 72L43.75 93.5L46 93.25L82.5 71.75L82 50Z"/>
        <path fill="${accent}" d="M43.25 6L6.25 27.75L6.25 49L41 69.25L46.25 69.75L60.25 61.5L56.25 58L22 39L22 37.75L45 25.25L82 46.25L82.5 27.75L60.5 14L45.5 6Z"/>
    </svg>`;

    let link = document.querySelector('link[rel="icon"][type="image/svg+xml"]');
    if (!link) {
        link = document.createElement('link');
        link.rel = 'icon';
        link.type = 'image/svg+xml';
        document.head.appendChild(link);
    }
    link.href = 'data:image/svg+xml,' + encodeURIComponent(svg);
}

// Update favicon on initial load
updateFavicon();

// Watch for style attribute changes on :root (theme live preview)
const faviconObserver = new MutationObserver(() => updateFavicon());
faviconObserver.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['style'],
});

Alpine.start();

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

if (import.meta.env.VITE_PUSHER_APP_KEY) {
    import('./echo');
}

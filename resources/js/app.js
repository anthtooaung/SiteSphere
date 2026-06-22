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

Alpine.start();

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

if (import.meta.env.VITE_PUSHER_APP_KEY) {
    import('./echo');
}

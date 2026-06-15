// import './bootstrap';
import './hover-profile';

import Alpine from 'alpinejs';
import 'flowbite';

window.Alpine = Alpine;

window.sitesphereSwal = {
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
        if (window.Swal) {
            return window.Swal;
        }
        try {
            window.Swal = (await import('https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.esm.all.min.js')).default;
            return window.Swal;
        } catch (error) {
            console.error('Failed to load SweetAlert2:', error);
            return { fire: () => Promise.resolve({ isConfirmed: false }) };
        }
    },
    async fire(options = {}) {
        const theme = this.getTheme();
        const Swal = await this.getSwal();
        return await Swal.fire({
            background: theme.background,
            color: theme.color,
            confirmButtonColor: theme.confirmButtonColor,
            didOpen: (popup) => {
                popup.style.fontFamily = theme.fontFamily;
            },
            ...options
        });
    },
    async confirm(options = {}) {
        const theme = this.getTheme();
        const Swal = await this.getSwal();
        return await Swal.fire({
            icon: options.icon || 'question',
            title: options.title || 'Are you sure?',
            text: options.text || '',
            showCancelButton: true,
            cancelButtonColor: '#d33',
            background: theme.background,
            color: theme.color,
            confirmButtonColor: theme.confirmButtonColor,
            didOpen: (popup) => {
                popup.style.fontFamily = theme.fontFamily;
            },
            ...options
        });
    },
    async toast(options = {}) {
        const theme = this.getTheme();
        const Swal = await this.getSwal();
        Swal.fire({
            toast: true,
            position: options.position || window.toastPosition || 'top-end',
            showConfirmButton: false,
            timer: options.timer || 3000,
            timerProgressBar: true,
            icon: options.icon || 'success',
            title: options.title || '',
            background: theme.background,
            color: theme.color,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
                toast.style.fontFamily = theme.fontFamily;
            },
            ...options
        });
    }
};

Alpine.start();

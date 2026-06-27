import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'us3',
    forceTLS: true,
});

// Test Listener
window.Echo.channel('test-channel')
    .listen('TestRealTimeEvent', (e) => {
        console.log('Real-time message received:', e.message);
    });

// Listen for real-time notifications
const userId = document.querySelector('meta[name="user-id"]')?.content;
if (userId) {
    window.Echo.private(`notifications.${userId}`)
        .listen('.notification.created', (e) => {
            // Update notification badge count
            const badge = document.querySelector('.noti-badge, .mobile-badge');
            if (badge) {
                const current = parseInt(badge.textContent) || 0;
                badge.textContent = current + 1;
                badge.style.display = 'flex';
            }
            // Show toast notification
            if (window.sitesphereSwal) {
                window.sitesphereSwal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    icon: 'info',
                    title: e.notification.message,
                });
            }
        });
}

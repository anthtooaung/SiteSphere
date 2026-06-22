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

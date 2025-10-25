import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: 'd4276512e3ec0545685a',
  cluster: 'ap2',
  forceTLS: true,
});

// Example: dynamic user IDs
const senderId = 1;
const receiverId = 5;
const sortedIds = [senderId, receiverId].sort((a, b) => a - b).join('.');

// Listen to your private chat channel
window.Echo.private(`chat.${sortedIds}`)
    .listen('.MessageSent', (e) => {
        console.log('📩 New message received:', e.message);
    });

// Optional: Test public broadcast
window.Echo.channel('my-channel')
    .listen('.my-event', (data) => {
        alert(JSON.stringify(data));
    });

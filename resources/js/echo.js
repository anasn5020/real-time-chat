import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY || 'd4276512e3ec0545685a',
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap2',
  forceTLS: true,
  authEndpoint: '/broadcasting/auth',
  withCredentials: true, // 👈 add this line
  auth: {
    headers: {
      'X-CSRF-TOKEN': document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content'),
    },
  },
});



// Keep track of current subscription so we can unsubscribe cleanly
window.__currentChatChannel = null;

document.addEventListener('subscribe-chat-channel', (e) => {
  const channelName = e.detail.channel;
  // If already subscribed to the same channel, do nothing
  if (window.__currentChatChannel === channelName) return;

  // Unsubscribe previous channel if exists
  if (window.__currentChatChannel) {
    try {
      // Echo.leave will unsubscribe from private/public channel
      window.Echo.leave(window.__currentChatChannel);
    } catch (err) {
      console.warn('Error leaving channel', err);
    }
  }

  window.__currentChatChannel = channelName;

  // Subscribe to new private channel
  try {
window.Echo.private(channelName)
  .listen('.MessageSent', (payload) => {
    console.log("✅ Echo received:", payload); // <--- add this
    const message = payload.message ?? payload;
    Livewire.emit('messageReceivedFromEcho', message);
  });

  } catch (err) {
    console.error('Echo subscription error', err);
  }
});

<div class="flex flex-col h-full">
    <div class="p-4 border-b border-gray-200 bg-gray-50">
        @if ($receiver)
            <h3 class="text-lg font-semibold">{{ $receiver->name }}</h3>
        @else
            <h3 class="text-lg font-semibold text-gray-400">Select a user to start chatting</h3>
        @endif
    </div>

    <div id="message-container" class="flex-grow p-4 overflow-y-auto space-y-4 bg-gray-50">
        @forelse ($messages as $message)
            @php
                $isSender = $message['sender_id'] == auth()->id();
            @endphp
            <div class="flex {{ $isSender ? 'justify-end' : 'justify-start' }}">
                <div
                    class="max-w-xs lg:max-w-md p-3 rounded-xl shadow-md
                    {{ $isSender ? 'bg-indigo-500 text-white rounded-br-none' : 'bg-white text-gray-800 rounded-tl-none' }}">
                    <p class="text-sm">{{ $message['message'] }}</p>
                    <span class="text-xs opacity-75 mt-1 block text-right">
                        {{ \Carbon\Carbon::parse($message['created_at'])->format('h:i A') }}
                    </span>
                </div>
            </div>
        @empty
            @if ($receiver)
                <div class="text-center text-gray-500">Say hello to {{ $receiver->name }}!</div>
            @endif
        @endforelse
    </div>

    @if ($receiver)
        <form wire:submit.prevent="sendMessage" class="p-4 border-t border-gray-200 bg-white">
            <div class="flex">
                <input type="text" wire:model.live="messageText" placeholder="Type a message..."
                    class="flex-grow rounded-l-lg border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    required>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-r-lg hover:bg-indigo-700 disabled:bg-indigo-400"
                    @disabled(empty($messageText))>
                    Send
                </button>

            </div>
            @error('messageText')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </form>
    @endif
</div>

{{-- JavaScript for Auto-Scrolling --}}
<script>
    function scrollMessagesToBottom() {
        const container = document.getElementById('message-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    // Initial scroll when component loads/updates
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('messagesLoaded', () => {
            // Wait for the DOM to update after data load
            setTimeout(scrollMessagesToBottom, 100);
        });
        Livewire.on('messageSent', scrollMessagesToBottom);
        Livewire.on('messageReceived', () => {
            // Check if user is near the bottom before auto-scrolling on receive
            const container = document.getElementById('message-container');
            if (container && (container.scrollHeight - container.scrollTop < container.clientHeight +
                    100)) {
                scrollMessagesToBottom();
            }
        });
    });

    // Fallback for full page refresh / old Livewire events
    document.addEventListener('DOMContentLoaded', () => {
        scrollMessagesToBottom();
    });
</script>

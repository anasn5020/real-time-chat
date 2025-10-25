<div class="flex h-screen">
    <div class="w-1/4 border-r">
        @livewire('chat-list')
    </div>
    <div class="flex-1">
@livewire('chat-box', ['receiverId' => $selectedUserId], key($selectedUserId))
    </div>
</div>

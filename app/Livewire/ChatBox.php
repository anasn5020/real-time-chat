<?php

namespace App\Livewire;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ChatBox extends Component
{
   public $receiverId;
    public $messageText = '';
    public $messages = [];
    public $receiver;

    // Listener for the 'userSelected' event from ChatList
    protected $listeners = ['userSelected' => 'loadMessages'];

   public function mount($receiverId = null)
{
    if ($receiverId) {
        $this->loadMessages($receiverId);
    }
}


    public function loadMessages($userId)
    {
        Log::info("Loading messages for user: " . $userId);
        $this->receiverId = $userId;
        $this->receiver = User::find($userId);

        // Load existing messages between the two users
        $messages = Message::where(function ($query) {
            $query->where('sender_id', auth()->id())
                  ->where('receiver_id', $this->receiverId);
        })->orWhere(function ($query) {
            $query->where('sender_id', $this->receiverId)
                  ->where('receiver_id', auth()->id());
        })->with('sender') // Eager load sender for display
          ->orderBy('created_at', 'asc')
          ->get();

        $this->messages = $messages->toArray();

        // Dispatch an event to the Livewire/JS to scroll to the bottom
        $this->dispatch('messagesLoaded');
    }

    public function sendMessage()
    {
        $this->validate([
            'messageText' => 'required|string|max:1000',
            'receiverId' => 'required|exists:users,id',
        ]);

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->receiverId,
            'message' => $this->messageText,
        ]);

        // Add the message to the local array immediately (Optimistic UI)
        $message->load('sender');
        $this->messages[] = $message->toArray();

        // Clear input
        $this->messageText = '';

        // Dispatch broadcast event
        broadcast(new MessageSent($message));

        // Dispatch an event to scroll to the bottom
        $this->dispatch('messageSent');
    }

    // Listener for the broadcast event from Pusher (Real-time update)
    public function getListeners()
    {
        if (!$this->receiverId) {
            return [];
        }

        // Get the consistent private channel name
        $ids = [auth()->id(), $this->receiverId];
        sort($ids);
        $channelName = 'chat.' . implode('.', $ids);

        Log::info('Listening to channel: ' . $channelName);

        // Listen for the MessageSent event on the private channel
        return [
            "echo-private:{$channelName},MessageSent" => 'handleMessageReceived',
        ];
    }

    public function handleMessageReceived($event)
    {
        $newMessage = $event['message'];
        Log::info('handleMessageReceived to channel: ' . $newMessage);

        // Only add if the message is for the *current* conversation
        if ($newMessage['sender_id'] == $this->receiverId || $newMessage['receiver_id'] == $this->receiverId) {
            $this->messages[] = $newMessage;
            $this->dispatch('messageReceived');
        }
    }


    public function render()
    {
        return view('livewire.chat-box');
    }
}

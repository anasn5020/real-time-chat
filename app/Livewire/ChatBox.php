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

    // Listen for user selection and for events emitted from the browser/Echo
    protected $listeners = [
        'userSelected' => 'loadMessages',
        'messageReceivedFromEcho' => 'handleMessageReceived', // forwarded from JS
    ];

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

        // Load conversation messages (extract logic if you want)
        $messages = Message::where(function ($query) {
                $query->where('sender_id', auth()->id())
                      ->where('receiver_id', $this->receiverId);
            })->orWhere(function ($query) {
                $query->where('sender_id', $this->receiverId)
                      ->where('receiver_id', auth()->id());
            })->with('sender')
              ->orderBy('created_at', 'asc')
              ->get();

        $this->messages = $messages->toArray();

        // Tell the browser to subscribe to the correct channel (JS will handle unsubscribing previous)
        $ids = [auth()->id(), $this->receiverId];
        sort($ids);
        $channelName = 'chat.' . implode('.', $ids);

        $this->dispatch('subscribe-chat-channel', ['channel' => $channelName]);

        // Scroll helper
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

        $message->load('sender');
        $this->messages[] = $message->toArray();

        $this->messageText = '';

        // Broadcast to Pusher (goes to JS Echo subscribers)
        broadcast(new MessageSent($message));

        $this->dispatch('messageSent');
    }

    /**
     * Called from JS via Livewire.emit('messageReceivedFromEcho', payload)
     * payload is the message array.
     */
    public function handleMessageReceived($message)
    {
        // add only if it's for the current conversation
        if (! $this->receiverId) return;

        if ($message['sender_id'] == $this->receiverId || $message['receiver_id'] == $this->receiverId) {
            $this->messages[] = $message;
            $this->dispatch('messageReceived');
        }
    }

    public function render()
    {
        return view('livewire.chat-box');
    }
}

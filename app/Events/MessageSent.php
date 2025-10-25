<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
        // Load sender relationship for broadcasting
        $this->message->load('sender');
    }

    /**
     * Get the channels the event should broadcast on.
     * The channel name is based on the two users' IDs, sorted to keep it consistent.
     */
    public function broadcastOn(): Channel
    {
        $ids = [$this->message->sender_id, $this->message->receiver_id];
        sort($ids); // Sort to ensure channel name is consistent regardless of who sends the message

        // The channel name must be consistent for both sender and receiver.
        return new PrivateChannel('chat.' . implode('.', $ids));
    }

    /**
     * The data to broadcast.
     */
    public function broadcastWith(): array
    {
        // Broadcast the entire message object including the sender
        return [
            'message' => $this->message->toArray(),
        ];
    }
     public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}

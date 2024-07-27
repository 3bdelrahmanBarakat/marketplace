<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $receiver;
    public User $sender;
    public string $message;

    public function __construct(User $receiver, User $sender, string $message)
    {
        $this->receiver = $receiver;
        $this->sender = $sender;
        $this->message  = $message;
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $sortedIds = [$this->sender->id, $this->receiver->id];
        sort( $sortedIds );
        return [
            new PrivateChannel('chat-channel-' . implode('-', $sortedIds)),
        ];
    }

    public function broadcastAs()
    {
        return 'chatMessage';
    }
}

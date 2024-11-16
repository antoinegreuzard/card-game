<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $lobbyId;

    public function __construct(int $lobbyId)
    {
        $this->lobbyId = $lobbyId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('lobby.' . $this->lobbyId);
    }

    public function broadcastAs(): string
    {
        return 'PlayerJoined';
    }
}

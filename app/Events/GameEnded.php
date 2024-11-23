<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TurnChanged implements ShouldBroadcast
{
    public function __construct(public $lobbyId, public $currentTurn)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('lobby.' . $this->lobbyId);
    }

    public function broadcastAs(): string
    {
        return 'turnchanged';
    }

    public function broadcastWith(): array
    {
        return ['currentTurn' => $this->currentTurn];
    }
}

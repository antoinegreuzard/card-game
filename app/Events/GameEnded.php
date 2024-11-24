<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GameEnded implements ShouldBroadcast
{
    public function __construct(public $winner)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('lobby.' . $this->lobbyId);
    }

    public function broadcastAs(): string
    {
        return 'gameended';
    }

    public function broadcastWith(): array
    {
        return [
            'winner' => $this->winner,
            'message' => $this->winner . ' a gagné la partie ! 🎉',
        ];
    }
}

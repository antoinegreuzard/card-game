<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CardPlayed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $card;
    public string $player;

    public function __construct(array $card, string $player)
    {
        $this->card = $card;
        $this->player = $player;
    }

    public function broadcastOn(): array
    {
        return [new Channel('game')];
    }

    public function broadcastWith(): array
    {
        return [
            'card' => $this->card,
            'player' => $this->player,
        ];
    }
}

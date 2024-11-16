<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CardPlayed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $card;
    public string $player;

    public function __construct(array $card, string $player)
    {
        $this->card = $card;
        $this->player = $player;

        Log::info('Event CardPlayed déclenché', [
            'player' => $player,
            'card' => $card,
        ]);
    }

    public function broadcastOn(): array
    {
        Log::info('Diffusion sur le canal public "game"');
        Log::info('Diffusion sur le canal game : CardPlayed', ['card' => $this->card, 'player' => $this->player]);
        Log::info('Diffusion de l\'événement CardPlayed sur le canal game', [
            'card' => $this->card,
            'player' => $this->player,
        ]);
        return [new Channel('game')];
    }

    public function broadcastAs(): string
    {
        return 'CardPlayed';
    }

    public function broadcastWith(): array
    {
        return [
            'card' => $this->card,
            'player' => $this->player,
        ];
    }
}

<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CardPlayed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $playedCard;
    public array $opponentCard;
    public string $player;

    /**
     * Create a new event instance.
     *
     * @param array $playedCard
     * @param array $opponentCard
     * @param string $player
     */
    public function __construct(array $playedCard, array $opponentCard, string $player)
    {
        $this->playedCard = $playedCard;
        $this->opponentCard = $opponentCard;
        $this->player = $player;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('lobby.' . $this->player);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'cardplayed';
    }

    /**
     * Data to broadcast with the event.
     */
    public function broadcastWith(): array
    {
        return [
            'playedCard' => $this->playedCard,
            'opponentCard' => $this->opponentCard,
            'player' => $this->player,
        ];
    }
}

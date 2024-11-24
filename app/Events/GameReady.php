<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $lobbyId;

    /**
     * Crée un nouvel événement GameReady.
     *
     * @param int $lobbyId
     */
    public function __construct(int $lobbyId)
    {
        $this->lobbyId = $lobbyId;
    }

    /**
     * Définir le canal de diffusion.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('lobby.' . $this->lobbyId);
    }

    /**
     * Nom de l'événement de diffusion.
     */
    public function broadcastAs(): string
    {
        return 'gameready';
    }

    /**
     * Données transmises à l'événement.
     */
    public function broadcastWith(): array
    {
        return [
            'lobbyId' => $this->lobbyId,
            'status' => 'ready',
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}

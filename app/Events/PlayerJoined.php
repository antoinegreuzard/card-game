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
    public string $playerName;

    /**
     * Crée un nouvel événement PlayerJoined.
     *
     * @param int $lobbyId
     * @param string $playerName
     */
    public function __construct(int $lobbyId, string $playerName)
    {
        $this->lobbyId = $lobbyId;
        $this->playerName = $playerName;
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
        return 'playerjoined';
    }

    /**
     * Données transmises à l'événement.
     */
    public function broadcastWith(): array
    {
        return [
            'lobbyId' => $this->lobbyId,
            'playerName' => $this->playerName,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}

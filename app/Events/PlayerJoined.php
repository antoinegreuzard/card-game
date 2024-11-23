<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PlayerJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $lobbyId;

    public function __construct(int $lobbyId)
    {
        $this->lobbyId = $lobbyId;
        Log::info("Événement PlayerJoined déclenché pour le salon : {$lobbyId}");
    }

    public function broadcastOn(): Channel
    {
        Log::info("Diffusion de l'événement sur le canal : lobby.{$this->lobbyId}");
        return new Channel('lobby.' . $this->lobbyId);
    }

    public function broadcastAs(): string
    {
        return 'playerjoined';
    }

    public function broadcastWith(): array
    {
        $payload = [
            'lobbyId' => $this->lobbyId,
            'timestamp' => now(),
        ];

        Log::info('Payload envoyé avec PlayerJoined :', $payload);

        return $payload;
    }
}

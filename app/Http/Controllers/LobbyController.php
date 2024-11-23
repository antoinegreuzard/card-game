<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use App\Events\PlayerJoined;
use Illuminate\Support\Facades\Log;

class LobbyController extends Controller
{
    public function createGame(Request $request): \Illuminate\Http\JsonResponse
    {
        $game = Game::create([
            'player1_id' => auth()->id(),
            'status' => 'waiting',
        ]);

        return response()->json(['lobbyId' => $game->id]);
    }

    public function joinGame(Request $request): \Illuminate\Http\JsonResponse
    {
        $lobbyId = $request->input('lobbyId');
        $game = Game::find($lobbyId);

        if (!$game || $game->status !== 'waiting') {
            return response()->json(['success' => false, 'message' => 'Le salon est introuvable ou déjà en cours.']);
        }

        $game->update([
            'player2_id' => auth()->id(),
            'status' => 'ready',
        ]);

        broadcast(new PlayerJoined($game->id))->toOthers();

        Log::info("État du jeu mis à jour :", [
            'player1_id' => $game->player1_id,
            'player2_id' => $game->player2_id,
            'status' => $game->status,
        ]);

        return response()->json(['success' => true]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function status($id): \Illuminate\Http\JsonResponse
    {
        $game = Game::with('cards')->find($id);

        return response()->json($game);
    }

    public function endGame($id): \Illuminate\Http\JsonResponse
    {
        $game = Game::find($id);

        if (!$game) {
            return response()->json(['error' => 'Partie introuvable'], 404);
        }

        if ($game->status === 'finished') {
            return response()->json(['message' => 'La partie est déjà terminée']);
        }

        $game->update(['status' => 'finished']);
        return response()->json(['success' => true, 'message' => 'Game ended']);
    }

    public function leaveGame(Request $request): \Illuminate\Http\JsonResponse
    {
        $game = Game::where('player1_id', $request->user()->id)
            ->orWhere('player2_id', $request->user()->id)
            ->first();

        if ($game) {
            $game->update(['status' => 'waiting']);
        }

        return response()->json(['success' => true, 'message' => 'Player left the game']);
    }
}

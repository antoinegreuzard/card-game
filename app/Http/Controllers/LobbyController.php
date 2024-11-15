<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class LobbyController extends Controller
{
    public function createGame(Request $request): \Illuminate\Http\JsonResponse
    {
        $game = Game::create([
            'player1_id' => auth()->id(),
        ]);
        return response()->json($game);
    }

    public function joinGame(Request $request): \Illuminate\Http\JsonResponse
    {
        $game = Game::where('status', 'waiting')->first();
        if ($game) {
            $game->update([
                'player2_id' => auth()->id(),
                'status' => 'ready'
            ]);
        }
        return response()->json($game);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class GameController extends Controller
{
    public function show($id): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $game = Game::with('cards')->find($id);

        if (!$game) {
            return abort(404, 'Partie introuvable');
        }

        // Vérifier si l'utilisateur est bien un des joueurs
        $userId = auth()->id();
        if ($userId !== $game->player1_id && $userId !== $game->player2_id) {
            return Redirect::route('lobby')->with('error', 'Vous ne faites pas partie de cette partie.');
        }

        // Vérifier si la partie est en cours ou en attente
        if ($game->status === 'finished') {
            return Redirect::route('lobby')->with('error', 'La partie est déjà terminée.');
        }

        return Inertia::render('GameBoard', [
            'lobbyId' => $id,
            'player1' => $game->player1_id,
            'player2' => $game->player2_id,
            'status' => $game->status,
        ]);
    }

    public function status($id): \Illuminate\Http\JsonResponse
    {
        $game = Game::find($id);

        if (!$game) {
            return response()->json(['error' => 'Partie introuvable'], 404);
        }


        return response()->json([
            'status' => $game->status,
            'player1' => $game->player1_id,
            'player2' => $game->player2_id,
        ]);
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
        return response()->json(['success' => true, 'message' => 'La partie est terminée']);
    }

    public function leaveGame(Request $request): \Illuminate\Http\JsonResponse
    {
        $game = Game::where('player1_id', $request->user()->id)
            ->orWhere('player2_id', $request->user()->id)
            ->first();

        if ($game) {
            $game->update(['status' => 'waiting']);
        }

        return response()->json(['success' => true, 'message' => 'Joueur a quitté la partie']);
    }
}

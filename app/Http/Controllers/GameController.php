<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class GameController extends Controller
{
    /**
     * Affiche une partie spécifique.
     */
    public function show($id): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $game = Game::find($id);

        if (!$game) {
            return abort(404, 'Partie introuvable');
        }

        // Vérifie si la partie est terminée
        if ($game->status === 'finished') {
            return Redirect::route('lobby')->with('error', 'La partie est déjà terminée.');
        }

        // Rendu de la page du jeu
        return Inertia::render('GameBoard', [
            'lobbyId' => $id,
            'player1' => $game->player1_id ?? 'Joueur 1',
            'player2' => $game->player2_id ?? 'Joueur 2',
            'status' => $game->status,
        ]);
    }

    /**
     * Retourne l'état actuel de la partie.
     */
    public function getGameState($lobbyId): \Illuminate\Http\JsonResponse
    {
        $game = Game::find($lobbyId);

        if (!$game) {
            return response()->json(['error' => 'Partie introuvable'], 404);
        }

        return response()->json([
            'playerDeck' => json_decode($game->player1_deck, true) ?? [],
            'opponentDeck' => json_decode($game->player2_deck, true) ?? [],
            'playedCards' => json_decode($game->played_cards, true) ?? [],
            'status' => $game->status,
            'message' => $game->status === 'ready' ? 'La partie est prête à commencer' : 'En attente d\'un autre joueur...',
        ]);
    }

    /**
     * Retourne le statut de la partie.
     */
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

    /**
     * Termine une partie.
     */
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

        return response()->json([
            'success' => true,
            'message' => 'La partie est terminée',
        ]);
    }

    /**
     * Permet à un joueur de quitter une partie.
     */
    public function leaveGame(Request $request): \Illuminate\Http\JsonResponse
    {
        $lobbyId = $request->input('lobbyId');
        $game = Game::find($lobbyId);

        if (!$game) {
            return response()->json(['error' => 'Partie introuvable'], 404);
        }

        if ($game->player1_id === $request->input('pseudo')) {
            $game->update(['player1_id' => null, 'status' => 'waiting']);
        } elseif ($game->player2_id === $request->input('pseudo')) {
            $game->update(['player2_id' => null, 'status' => 'waiting']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Le joueur a quitté la partie, en attente de nouveaux joueurs.',
        ]);
    }
}

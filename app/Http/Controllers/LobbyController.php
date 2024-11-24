<?php

namespace App\Http\Controllers;

use App\Events\GameReady;
use App\Models\Game;
use Illuminate\Http\Request;
use App\Events\PlayerJoined;
use Illuminate\Support\Facades\Log;

class LobbyController extends Controller
{
    /**
     * Crée un nouveau jeu (lobby).
     */
    public function createGame(Request $request): \Illuminate\Http\JsonResponse
    {
        $pseudo = $request->input('pseudo', 'Joueur Anonyme');

        try {
            $game = Game::create([
                'player1_id' => $pseudo, // Sauvegarde du pseudo du créateur
                'status' => 'waiting',
            ]);

            Log::info("Nouveau jeu créé :", [
                'game_id' => $game->id,
                'player1' => $pseudo,
                'status' => $game->status,
            ]);

            return response()->json(['lobbyId' => $game->id]);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la création du jeu :", ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Impossible de créer le jeu.'], 500);
        }
    }

    /**
     * Rejoint un jeu existant (lobby).
     */
    public function joinGame(Request $request): \Illuminate\Http\JsonResponse
    {
        $lobbyId = $request->input('lobbyId');
        $pseudo = $request->input('pseudo', 'Joueur Anonyme');
        $game = Game::find($lobbyId);

        if (!$game) {
            return response()->json(['success' => false, 'message' => 'Le salon est introuvable.'], 404);
        }

        if ($game->status === 'ready') {
            return response()->json(['success' => false, 'message' => 'Le salon est déjà en cours.'], 400);
        }

        try {
            // Vérifier et assigner le joueur manquant
            if (!$game->player2_id) {
                $game->update([
                    'player2_id' => $pseudo,
                    'status' => 'ready', // Met à jour le statut une fois les deux joueurs connectés
                ]);

                broadcast(new PlayerJoined($game->id, $pseudo))->toOthers();
                broadcast(new GameReady($game->id))->toOthers();

                Log::info("Événement PlayerJoined diffusé pour le salon ID: {$game->id} avec {$pseudo} comme joueur 2.");
            } else {
                return response()->json(['success' => false, 'message' => 'Le salon est complet.'], 400);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la mise à jour du jeu :", ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Impossible de rejoindre le jeu.'], 500);
        }
    }
}

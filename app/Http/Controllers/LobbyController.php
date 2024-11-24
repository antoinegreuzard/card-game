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
                'player1_id' => $pseudo,
                'status' => 'waiting',
                'player1_deck' => json_encode($this->generateDeck()),
                'player2_deck' => json_encode([]), // Deck vide pour le joueur 2
                'played_cards' => json_encode([]),
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

        if (!$game || $game->status === 'ready') {
            return response()->json(['success' => false, 'message' => 'Le salon est complet ou introuvable.'], 400);
        }

        try {
            // Vérifier et assigner le joueur manquant
            if (!$game->player2_id) {
                // Initialisation du deck pour le deuxième joueur

                $game->update([
                    'player2_id' => $pseudo,
                    'player2_deck' => json_encode($this->generateDeck()),
                    'status' => 'ready', // Met à jour le statut une fois les deux joueurs connectés
                ]);

                // Diffusion des événements pour informer les joueurs
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

    private function generateDeck(): array
    {
        $suits = ['HEARTS', 'DIAMONDS', 'CLUBS', 'SPADES'];
        $values = array_merge(range(2, 10), ['JACK', 'QUEEN', 'KING', 'ACE']);
        $deck = [];

        foreach ($suits as $suit) {
            foreach ($values as $value) {
                $deck[] = ['value' => $value, 'suit' => $suit];
            }
        }

        shuffle($deck);
        return array_slice($deck, 0, 26); // 26 cartes par joueur
    }
}

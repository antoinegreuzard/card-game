<?php

namespace App\Http\Controllers;

use App\Events\CardPlayed;
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
            'playerDeck' => json_decode($game->player1_deck),
            'opponentDeck' => json_decode($game->player2_deck),
            'playedCards' => json_decode($game->played_cards),
            'status' => $game->status,
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

    public function playCard(Request $request): \Illuminate\Http\JsonResponse
    {
        $lobbyId = $request->input('lobbyId');
        $player = $request->input('player');

        $game = Game::find($lobbyId);

        if (!$game) {
            return response()->json(['error' => 'Partie introuvable'], 404);
        }

        $playerDeck = json_decode($game->player1_deck, true);
        $opponentDeck = json_decode($game->player2_deck, true);

        if (empty($playerDeck) || empty($opponentDeck)) {
            return response()->json(['error' => 'Aucune carte disponible pour jouer.'], 400);
        }

        // Jouer la première carte de chaque deck
        $playedCard = array_shift($playerDeck);
        $opponentCard = array_shift($opponentDeck);

        // Comparer les valeurs des cartes
        $playerValue = $this->getCardValue($playedCard['value']);
        $opponentValue = $this->getCardValue($opponentCard['value']);

        if ($playerValue > $opponentValue) {
            $playerDeck[] = $playedCard;
            $playerDeck[] = $opponentCard;
        } elseif ($opponentValue > $playerValue) {
            $opponentDeck[] = $playedCard;
            $opponentDeck[] = $opponentCard;
        }

        // Mettre à jour les decks
        $game->update([
            'player1_deck' => json_encode($playerDeck),
            'player2_deck' => json_encode($opponentDeck),
            'played_cards' => json_encode([$playedCard, $opponentCard]),
        ]);

        // Diffuser l'événement TurnChanged
        broadcast(new \App\Events\TurnChanged($lobbyId, $player === $game->player1_id ? $game->player2_id : $game->player1_id));

        // Vérifier si le jeu est terminé
        if (empty($playerDeck) || empty($opponentDeck)) {
            $winner = empty($playerDeck) ? $game->player2_id : $game->player1_id;
            $game->update(['status' => 'finished']);
            broadcast(new \App\Events\GameEnded($winner));

            return response()->json(['success' => true, 'winner' => $winner]);
        }

        return response()->json(['success' => true]);
    }

    private function getCardValue(string $value): int
    {
        if (is_numeric($value)) {
            return (int)$value;
        }

        $values = ['JACK' => 11, 'QUEEN' => 12, 'KING' => 13, 'ACE' => 14];
        return $values[$value] ?? 0;
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

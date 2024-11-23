<?php

namespace App\Http\Controllers;

use App\Events\CardPlayed;
use App\Models\Card;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function playCard(Request $request): \Illuminate\Http\JsonResponse
    {
        $card = Card::find($request->card_id);

        if (!$card) {
            return response()->json(['error' => 'Carte introuvable'], 404);
        }

        $player = $request->user()->name;
        $card->update(['is_played' => true]);

        // Diffusion de l'événement CardPlayed
        event(new CardPlayed($card->toArray(), $player));

        return response()->json(['success' => true, 'card' => $card]);
    }
}

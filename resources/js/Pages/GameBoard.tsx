import {useEffect, useState} from 'react';
import {Card} from '@/types';
import CardComponent from '@/Components/Card';
import axios from 'axios';
import {Head} from '@inertiajs/react';

export default function GameBoard({lobbyId, playerPseudo}: { lobbyId: string, playerPseudo: string }) {
    const [playerDeck, setPlayerDeck] = useState<Card[]>([]);
    const [opponentDeck, setOpponentDeck] = useState<Card[]>([]);
    const [playedCards, setPlayedCards] = useState<Card[]>([]);
    const [message, setMessage] = useState('');
    const [history, setHistory] = useState<string[]>([]);
    const [isPlayerTurn, setIsPlayerTurn] = useState(true);
    const [gameReady, setGameReady] = useState(false);

    // Initialiser la partie
    const initializeGame = async () => {
        const {data} = await axios.get('https://deckofcardsapi.com/api/deck/new/shuffle/?deck_count=1');
        const deckId = data.deck_id;
        const drawResponse = await axios.get(`https://deckofcardsapi.com/api/deck/${deckId}/draw/?count=52`);
        const cards = drawResponse.data.cards;

        setPlayerDeck(cards.slice(0, 26));
        setOpponentDeck(cards.slice(26, 52));
        setMessage('La partie commence !');
        setGameReady(true);
    };

    // Écouter l'événement PlayerJoined
    useEffect(() => {
        const channel = window.Echo.channel(`lobby.${lobbyId}`);

        channel.listen('.PlayerJoined', () => {
            console.log('Un joueur a rejoint le salon, la partie peut commencer.');
            initializeGame();
        });

        return () => {
            window.Echo.leaveChannel(`lobby.${lobbyId}`);
        };
    }, [lobbyId]);

    const playCard = () => {
        if (!isPlayerTurn || playerDeck.length === 0) return;

        const playerCard = playerDeck[0];
        const opponentCard = opponentDeck[0];

        setPlayedCards([playerCard, opponentCard]);
        setPlayerDeck(playerDeck.slice(1));
        setOpponentDeck(opponentDeck.slice(1));

        const playerValue = getCardValue(playerCard.value);
        const opponentValue = getCardValue(opponentCard.value);

        if (playerValue > opponentValue) {
            setMessage(`Vous gagnez ce tour avec ${playerCard.value} contre ${opponentCard.value}`);
            setHistory((prev) => [
                ...prev,
                `Vous gagnez : ${playerCard.value} de ${playerCard.suit} contre ${opponentCard.value} de ${opponentCard.suit}`,
            ]);
            setPlayerDeck((prev) => [...prev, playerCard, opponentCard]);
        } else if (playerValue < opponentValue) {
            setMessage(`L'adversaire gagne ce tour avec ${opponentCard.value} contre ${playerCard.value}`);
            setHistory((prev) => [
                ...prev,
                `L'adversaire gagne : ${opponentCard.value} de ${opponentCard.suit} contre ${playerCard.value} de ${playerCard.suit}`,
            ]);
            setOpponentDeck((prev) => [...prev, playerCard, opponentCard]);
        } else {
            setMessage('Égalité !');
            setHistory((prev) => [
                ...prev,
                `Égalité : ${playerCard.value} contre ${opponentCard.value}`,
            ]);
        }

        setIsPlayerTurn(!isPlayerTurn);
    };

    const getCardValue = (value: string) => {
        if (['JACK', 'QUEEN', 'KING'].includes(value)) return 11 + ['JACK', 'QUEEN', 'KING'].indexOf(value);
        if (value === 'ACE') return 14;
        return parseInt(value);
    };

    return (
        <>
            <Head title={`Jeu de Bataille - Salon ${lobbyId}`}/>
            <div className="bg-gray-800 text-white min-h-screen p-4">
                <h1 className="text-3xl font-bold mb-4">Jeu de Bataille - Salon {lobbyId}</h1>

                {gameReady ? (
                    <>
                        <div className="flex justify-center gap-4 mb-4">
                            {playedCards.map((card, index) => (
                                <CardComponent key={index} card={card}/>
                            ))}
                        </div>

                        <button
                            className={`px-4 py-2 rounded ${isPlayerTurn ? 'bg-blue-600' : 'bg-gray-500'} transition`}
                            onClick={playCard}
                            disabled={!isPlayerTurn}
                        >
                            Jouer votre carte
                        </button>

                        <div className="mt-4">
                            <h2>Historique</h2>
                            <div className="bg-gray-700 p-4 rounded-lg">
                                <ul>
                                    {history.map((entry, index) => (
                                        <li key={index}>{entry}</li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                    </>
                ) : (
                    <div>En attente d'un autre joueur...</div>
                )}

                <div className="mt-4">
                    <p>{message}</p>
                </div>
            </div>
        </>
    );
}

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
        try {
            const {data} = await axios.get(`/game/${lobbyId}/state`);
            console.log('🔄 Données initiales du jeu :', data);

            if (!data.playerDeck || !data.opponentDeck) {
                setMessage('Erreur : Les decks ne sont pas initialisés correctement.');
                return;
            }

            setPlayerDeck(data.playerDeck);
            setOpponentDeck(data.opponentDeck);
            setPlayedCards([]);
            setMessage('La partie commence !');
            setGameReady(data.status === 'ready');
        } catch (error) {
            console.error('❌ Erreur lors de l\'initialisation du jeu :', error);
            setMessage('Impossible de démarrer la partie.');
        }
    };

    // Écouter l'événement PlayerJoined
    useEffect(() => {
        const channel = window.Echo.channel(`lobby.${lobbyId}`);

        channel.listen('.playerjoined', () => {
            console.log('Un joueur a rejoint le salon, la partie peut commencer.');
            initializeGame();
        });

        channel.listen('.turnchanged', (data: { currentTurn: string; }) => {
            console.log('🔄 Tour changé', data);
            setIsPlayerTurn(data.currentTurn === playerPseudo);
        });

        channel.listen('.cardplayed', (data: { card: Card; }) => {
            console.log('Carte jouée :', data);
            setPlayedCards((prev) => [...prev, data.card]);
        });

        return () => {
            window.Echo.leaveChannel(`lobby.${lobbyId}`);
        };
    }, [lobbyId]);

    const playCard = async () => {
        if (!isPlayerTurn || playerDeck.length === 0) return;

        try {
            const response = await axios.post(`/game/${lobbyId}/play`, {card: playerDeck[0]});
            if (!response.data.success) {
                setMessage('Erreur lors de votre tour. Réessayez.');
                return;
            }

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
        } catch (error) {
            console.error('❌ Erreur lors de la tentative de jouer une carte :', error);
            setMessage('Impossible de jouer pour l\'instant.');
        }
    };

    const getCardValue = (value: string) => {
        if (['JACK', 'QUEEN', 'KING'].includes(value)) return 11 + ['JACK', 'QUEEN', 'KING'].indexOf(value);
        if (value === 'ACE') return 14;
        return parseInt(value);
    };

    const checkGameEnd = () => {
        if (playerDeck.length === 0 || opponentDeck.length === 0) {
            const winner = playerDeck.length > 0 ? 'Vous gagnez ! 🎉' : 'L\'adversaire gagne ! 😢';
            setMessage(winner);
            setGameReady(false); // Désactiver le jeu
        }
    };

    useEffect(() => {
        if (!gameReady) {
            setMessage('En attente d\'un autre joueur...');
            return;
        }

        if (playerDeck.length === 0 || opponentDeck.length === 0) {
            checkGameEnd();
        }
    }, [gameReady, playerDeck, opponentDeck]);

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

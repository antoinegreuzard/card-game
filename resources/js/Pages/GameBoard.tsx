import {useEffect, useState} from 'react';
import {Card} from '@/types'; // Assurez-vous que le type Card est défini correctement
import CardComponent from '@/Components/Card';
import axios from 'axios';
import {Head} from '@inertiajs/react';

export default function GameBoard({lobbyId, playerPseudo}: { lobbyId: string; playerPseudo: string }) {
    const [playerDeck, setPlayerDeck] = useState<Card[]>([]);
    const [opponentDeck, setOpponentDeck] = useState<Card[]>([]);
    const [playedCards, setPlayedCards] = useState<Card[]>([]);
    const [message, setMessage] = useState('En attente d\'un autre joueur...');
    const [history, setHistory] = useState<string[]>([]);
    const [isPlayerTurn, setIsPlayerTurn] = useState(false); // False par défaut
    const [gameReady, setGameReady] = useState(false); // Indique si le jeu est prêt à démarrer

    // Fonction d'initialisation du jeu
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
            setIsPlayerTurn(data.currentTurn === playerPseudo); // Détermine si c'est le tour du joueur
        } catch (error) {
            console.error('❌ Erreur lors de l\'initialisation du jeu :', error);
            setMessage('Impossible de démarrer la partie.');
        }
    };

    useEffect(() => {
        const checkGameStatus = async () => {
            try {
                const {data} = await axios.get(`/game/status/${lobbyId}`);
                if (data.status === 'ready') {
                    setGameReady(true);
                    setMessage('La partie est prête à commencer.');
                }
            } catch (error) {
                console.error('Erreur lors de la vérification du statut du jeu:', error);
            }
        };

        if (!gameReady) {
            checkGameStatus();
        }
    }, [gameReady, lobbyId]);

    // Écoute les événements en temps réel
    useEffect(() => {
        const channel = window.Echo.channel(`lobby.${lobbyId}`);

        // Événement lorsqu'un joueur rejoint
        channel.listen('.playerjoined', (data: any) => {
            console.log('🔔 Événement PlayerJoined reçu', data);
            setMessage(`${data.playerName} a rejoint le salon, redirection...`);
            window.location.href = `/game/${lobbyId}`;
        });

        // Événement pour gérer le changement de tour
        channel.listen('.turnchanged', (data: { currentTurn: string }) => {
            console.log('🔄 Tour changé', data);
            setIsPlayerTurn(data.currentTurn === playerPseudo);
        });

        // Événement pour gérer les cartes jouées
        channel.listen('.cardplayed', (data: { card: Card }) => {
            console.log('Carte jouée :', data);
            setPlayedCards((prev) => [...prev, data.card]);
        });

        // Nettoyage lors du démontage du composant
        return () => {
            window.Echo.leaveChannel(`lobby.${lobbyId}`);
        };
    }, [lobbyId, playerPseudo]);

    // Fonction pour jouer une carte
    const playCard = async () => {
        if (!isPlayerTurn || playerDeck.length === 0) {
            setMessage('Ce n\'est pas votre tour ou votre deck est vide.');
            return;
        }

        try {
            const response = await axios.post(`/game/${lobbyId}/play`, {card: playerDeck[0]});
            if (!response.data.success) {
                setMessage('Erreur lors de votre tour. Réessayez.');
                return;
            }

            const playerCard = playerDeck[0];
            const opponentCard = opponentDeck[0];

            // Mise à jour des états après avoir joué
            setPlayedCards([playerCard, opponentCard]);
            setPlayerDeck(playerDeck.slice(1));
            setOpponentDeck(opponentDeck.slice(1));

            // Calcul des résultats du tour
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

    // Fonction pour obtenir la valeur d'une carte
    const getCardValue = (value: string) => {
        if (['JACK', 'QUEEN', 'KING'].includes(value)) return 11 + ['JACK', 'QUEEN', 'KING'].indexOf(value);
        if (value === 'ACE') return 14;
        return parseInt(value, 10);
    };

    // Vérifie si le jeu est terminé
    const checkGameEnd = () => {
        if (playerDeck.length === 0 || opponentDeck.length === 0) {
            const winner = playerDeck.length > 0 ? 'Vous gagnez ! 🎉' : 'L\'adversaire gagne ! 😢';
            setMessage(winner);
            setGameReady(false); // Désactiver le jeu
        }
    };

    // Effet pour vérifier la fin du jeu
    useEffect(() => {
        if (gameReady && (playerDeck.length === 0 || opponentDeck.length === 0)) {
            checkGameEnd();
        }
    }, [gameReady, playerDeck, opponentDeck]);

    // Rendu du composant
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
                    <div>{message}</div>
                )}
            </div>
        </>
    );
}

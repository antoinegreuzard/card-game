import {useEffect, useState} from 'react';
import {PageProps} from '@/types';
import {Head} from '@inertiajs/react';
import {Card} from '@/types';

export default function GameBoard({auth}: PageProps) {
    const [cards, setCards] = useState<Card[]>([]);
    const [message, setMessage] = useState<string>('');

    useEffect(() => {
        console.log('Connexion au canal Pusher...');
        const channel = window.Echo.channel('game');

        // Essaie d'écouter l'événement avec son nom complet
        channel.listen('.App\\Events\\CardPlayed', (event: { card: Card; player: string }) => {
            console.log('Événement reçu via Pusher :', event);
            if (event.card && event.player) {
                setCards((prevCards) => [...prevCards, event.card]);
                setMessage(`${event.player} a joué ${event.card.value} de ${event.card.suit}`);
            } else {
                console.error('Événement invalide', event);
            }
        });

        channel.listen('.App\\Events\\CardPlayed', (event: any) => {
            console.log('CardPlayed reçu :', event);
        });

        channel.listenForWhisper('CardPlayed', (event: any) => {
            console.log('Whisper reçu :', event);
        });

        channel.listen('.pusher:subscription_error', (error: any) => {
            console.error('Erreur d\'abonnement :', error);
        });


        return () => {
            console.log('Déconnexion du canal Pusher.');
            window.Echo.leaveChannel('game');
        };
    }, []);

    return (
        <>
            <Head title="Game Board"/>
            <div className="game-board">
                <h1>Partie en Cours</h1>
                <div className="cards">
                    {cards.map((card, index) => (
                        <div key={index} className="card">
                            {card.value} de {card.suit}
                        </div>
                    ))}
                </div>
                <p>{message}</p>
            </div>
        </>
    );
}

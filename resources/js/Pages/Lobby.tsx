import {useState, useEffect} from 'react';
import {Head, usePage} from '@inertiajs/react';
import axios from 'axios';

export default function Lobby() {
    const [lobbyId, setLobbyId] = useState('');
    const [pseudo, setPseudo] = useState('');
    const [isCreating, setIsCreating] = useState(false);
    const [error, setError] = useState('');
    const [message, setMessage] = useState('');
    const {errors} = usePage().props;

    useEffect(() => {
        if (errors && errors.error) {
            setError(errors.error);
        }
    }, [errors]);

    useEffect(() => {
        if (lobbyId) {
            const channel = window.Echo.channel(`lobby.${lobbyId}`);
            console.log(`✅ Abonné au canal lobby.${lobbyId}`);

            channel.listen('.playerjoined', (data: any) => {
                console.log('🔔 Événement PlayerJoined reçu', data);
                setMessage(`${data.playerName} a rejoint le salon.`);
            });

            channel.listen('.gameready', (data: any) => {
                console.log('🔔 Événement GameReady reçu', data);
                setMessage('La partie est prête. Redirection...');
                window.location.href = `/game/${lobbyId}`;
            });

            return () => {
                window.Echo.leaveChannel(`lobby.${lobbyId}`);
            };
        }
    }, [lobbyId]);

    const createLobby = async () => {
        if (!pseudo) {
            alert('Veuillez entrer un pseudo.');
            return;
        }
        setIsCreating(true);
        setError('');
        setMessage('');

        try {
            const response = await axios.post('/lobby/create', {pseudo});
            const {lobbyId} = response.data;
            if (lobbyId) {
                console.log(`Salon créé avec succès. Redirection vers /game/${lobbyId}`);
                window.location.href = `/game/${lobbyId}`;
            } else {
                setError('Erreur : impossible de créer le salon.');
                setIsCreating(false);
            }
        } catch (error) {
            setError('Une erreur est survenue lors de la création du salon.');
            console.error(error);
            setIsCreating(false);
        }
    };

    const joinLobby = async () => {
        if (!pseudo || !lobbyId) {
            alert('Veuillez entrer un pseudo et un ID de salon.');
            return;
        }
        setError('');
        setMessage('');

        try {
            const response = await axios.post('/lobby/join', {pseudo, lobbyId});
            if (response.data.success) {
                console.log(`Redirection vers /game/${lobbyId}`);
                window.location.href = `/game/${lobbyId}`;
            } else {
                setError(response.data.message || 'Impossible de rejoindre le salon.');
            }
        } catch (error) {
            console.error('Erreur lors de la tentative de rejoindre le salon :', error);
            setError('Une erreur est survenue lors de la tentative de rejoindre le salon.');
        }
    };

    return (
        <>
            <Head title="Jeu de Bataille - Lobby"/>
            <div className="bg-gray-800 text-white min-h-screen flex flex-col items-center justify-center p-4">
                <h1 className="text-3xl font-bold mb-4">Jeu de Bataille</h1>
                <input
                    type="text"
                    placeholder="Votre pseudo"
                    value={pseudo}
                    onChange={(e) => setPseudo(e.target.value)}
                    className="mb-4 p-2 rounded bg-gray-700 text-white w-64"
                />

                {isCreating ? (
                    <div className="text-lg">
                        Salon créé avec succès ! ID du salon : <span className="font-bold">{lobbyId}</span>
                    </div>
                ) : (
                    <>
                        <button
                            className="mb-2 px-4 py-2 bg-blue-600 rounded hover:bg-blue-700 w-64"
                            onClick={createLobby}
                        >
                            Créer un Salon
                        </button>
                        <input
                            type="text"
                            placeholder="ID du salon"
                            value={lobbyId}
                            onChange={(e) => setLobbyId(e.target.value)}
                            className="mb-4 p-2 rounded bg-gray-700 text-white w-64"
                        />
                        <button
                            className="px-4 py-2 bg-green-600 rounded hover:bg-green-700 w-64"
                            onClick={joinLobby}
                        >
                            Rejoindre un Salon
                        </button>
                    </>
                )}

                {message && (
                    <div className="mt-4 p-2 bg-green-600 rounded">
                        {message}
                    </div>
                )}

                {error && (
                    <div className="mt-4 p-2 bg-red-600 rounded">
                        {error}
                    </div>
                )}
            </div>
        </>
    );
}

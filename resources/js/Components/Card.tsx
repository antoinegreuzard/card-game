import {Card} from '@/types';

interface CardProps {
    card: Card;
}

export default function CardComponent({card}: CardProps) {
    return (
        <div
            className="card bg-white text-black p-2 rounded-lg shadow-md hover:shadow-lg transition transform hover:scale-105">
            <img src={card.image} alt={`${card.value} de ${card.suit}`} className="w-full h-auto rounded"/>
            <div className="text-center mt-2">
                <span className="font-bold">{card.value}</span> de <span className="italic">{card.suit}</span>
            </div>
        </div>
    );
}

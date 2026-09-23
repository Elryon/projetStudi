<?php

namespace App\Enum;

enum Statut: string
{
    case Attente = 'attente';
    case Accepte = 'accepte';
    case Preparation = 'preparation';
    case Livraison = 'livraison';
    case Livre = 'livre';
    case AttenteRetourMateriel = 'AttenteRetourMateriel';
    case Termine = 'termine';
    case Annule = 'annule';
    
    public function getLabel(): string
    {
        return match ($this) {
            self::Attente => 'En attente',
            self::Accepte => 'Acceptée',
            self::Preparation => 'En préparation',
            self::Livraison => 'En livraison',
            self::Livre => 'Livrée',
            self::AttenteRetourMateriel => 'En Attente de Retour Materiel',
            self::Termine => 'Terminée',
            self::Annule => 'Annulé'
        };
    }
}
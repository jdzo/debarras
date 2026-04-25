<?php

namespace App\Domain\Enum;

enum StatutChantier: string
{
    case EN_ATTENTE = 'en_attente';
    case EN_PREPARATION = 'en_preparation';
    case EN_COURS = 'en_cours';
    case TERMINE = 'termine';
    case ANNULE = 'annule';
}

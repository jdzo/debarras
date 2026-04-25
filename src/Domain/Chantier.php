<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Enum\StatutChantier;
use DomainException;

class Chantier
{
    public StatutChantier $statut;

    public function __construct(
        public ChantierId $id,
        public ChantierNom $nom,
    ) {
        $this->statut = StatutChantier::EN_ATTENTE;
    }

    public static function create(
        ChantierId $id,
        ChantierNom $nom,
    ): self {
        return new self(id: $id, nom: $nom);
    }

    public function demarrer(): self
    {
        if (StatutChantier::EN_PREPARATION !== $this->statut) {
            throw new DomainException('Le chantier ne peut pas être démarré');
        }

        $chantier = new self($this->id, $this->nom);
        $chantier->statut = StatutChantier::EN_COURS;

        return $chantier;
    }
}

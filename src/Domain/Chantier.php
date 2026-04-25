<?php

namespace App\Domain;

use App\Domain\Enum\StatutChantier;

class Chantier
{
    public StatutChantier $statut;

    /**
     * @param ChantierId $id
     * @param ChantierNom $nom
     */
    public function __construct(
        public ChantierId  $id,
        public ChantierNom $nom,
    )
    {
        $this->statut = StatutChantier::EN_ATTENTE;
    }

    /**
     * @param ChantierId $id
     * @param ChantierNom $nom
     * @return self
     */
    public static function create(
        ChantierId  $id,
        ChantierNom $nom,

    ): self
    {
        return new Chantier(id: $id, nom: $nom);
    }

    public function demarrer(): self
    {
        if ($this->statut !== StatutChantier::EN_PREPARATION) {
            throw new \DomainException("Le chantier ne peut pas être démarré");
        }

        $chantier = new self($this->id, $this->nom);
        $chantier->statut = StatutChantier::EN_COURS;

        return $chantier;
    }
}

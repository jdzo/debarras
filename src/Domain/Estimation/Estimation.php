<?php

declare(strict_types=1);

namespace App\Domain\Estimation;

use App\Domain\Estimation\Event\EstimationCreee;
use App\Domain\Estimation\Service\CalculateurPrix;
use App\Domain\Estimation\ValueObject\Accessibilite;
use App\Domain\Estimation\ValueObject\Coordonnees;
use App\Domain\Estimation\ValueObject\FourchetteEstimation;
use App\Domain\Estimation\ValueObject\NiveauEncombrement;
use App\Domain\Estimation\ValueObject\NiveauSalete;
use App\Domain\Estimation\ValueObject\Options;
use App\Domain\Estimation\ValueObject\StatutEstimation;
use App\Domain\Estimation\ValueObject\Superficie;
use App\Domain\Estimation\ValueObject\TypeDeBien;
use App\Domain\Estimation\ValueObject\ZoneTarifaire;

class Estimation
{
    private array $domainEvents = [];

    private function __construct(
        private EstimationId $id,
        private TypeDeBien $typeDeBien,
        private Superficie $superficie,
        private NiveauEncombrement $encombrement,
        private NiveauSalete $salete,
        private Accessibilite $accessibilite,
        private Options $options,
        private Coordonnees $coordonnees,
        private FourchetteEstimation $fourchette,
        private StatutEstimation $statut,
        private \DateTimeImmutable $createdAt,
        private string $accessToken,
        private ?string $commentaire = null,
        private array $photos = [],
    ) {
    }

    public static function creer(
        EstimationId $id,
        TypeDeBien $typeDeBien,
        Superficie $superficie,
        NiveauEncombrement $encombrement,
        NiveauSalete $salete,
        Accessibilite $accessibilite,
        Options $options,
        Coordonnees $coordonnees,
        CalculateurPrix $calculateur,
        ?string $commentaire = null,
        array $photos = [],
    ): self {
        $zone = ZoneTarifaire::fromCodePostal($coordonnees->codePostal);

        $fourchette = $calculateur->calculer(
            $superficie,
            $encombrement,
            $salete,
            $accessibilite,
            $options,
            $zone,
        );

        $estimation = new self(
            id: $id,
            typeDeBien: $typeDeBien,
            superficie: $superficie,
            encombrement: $encombrement,
            salete: $salete,
            accessibilite: $accessibilite,
            options: $options,
            coordonnees: $coordonnees,
            fourchette: $fourchette,
            statut: StatutEstimation::NOUVELLE,
            createdAt: new \DateTimeImmutable(),
            accessToken: bin2hex(random_bytes(32)),
            commentaire: $commentaire,
            photos: $photos,
        );

        $estimation->domainEvents[] = new EstimationCreee($id, $estimation->createdAt);

        return $estimation;
    }

    /**
     * Reconstitue une Estimation depuis la persistance (sans événement ni recalcul).
     */
    public static function reconstituer(
        EstimationId $id,
        TypeDeBien $typeDeBien,
        Superficie $superficie,
        NiveauEncombrement $encombrement,
        NiveauSalete $salete,
        Accessibilite $accessibilite,
        Options $options,
        Coordonnees $coordonnees,
        FourchetteEstimation $fourchette,
        StatutEstimation $statut,
        \DateTimeImmutable $createdAt,
        string $accessToken,
        ?string $commentaire = null,
        array $photos = [],
    ): self {
        return new self(
            id: $id,
            typeDeBien: $typeDeBien,
            superficie: $superficie,
            encombrement: $encombrement,
            salete: $salete,
            accessibilite: $accessibilite,
            options: $options,
            coordonnees: $coordonnees,
            fourchette: $fourchette,
            statut: $statut,
            createdAt: $createdAt,
            accessToken: $accessToken,
            commentaire: $commentaire,
            photos: $photos,
        );
    }

    public function marquerContactee(): void
    {
        if ($this->statut !== StatutEstimation::NOUVELLE) {
            throw new \DomainException("Seule une estimation nouvelle peut être marquée comme contactée");
        }

        $this->statut = StatutEstimation::CONTACTEE;
    }

    public function envoyerDevis(): void
    {
        if (!in_array($this->statut, [StatutEstimation::NOUVELLE, StatutEstimation::CONTACTEE], true)) {
            throw new \DomainException("Le devis ne peut être envoyé qu'à une estimation nouvelle ou contactée");
        }

        $this->statut = StatutEstimation::DEVIS_ENVOYE;
    }

    public function accepter(): void
    {
        if ($this->statut !== StatutEstimation::DEVIS_ENVOYE) {
            throw new \DomainException("Seule une estimation avec devis envoyé peut être acceptée");
        }

        $this->statut = StatutEstimation::ACCEPTEE;
    }

    public function refuser(): void
    {
        if ($this->statut === StatutEstimation::ACCEPTEE) {
            throw new \DomainException("Une estimation acceptée ne peut pas être refusée");
        }

        $this->statut = StatutEstimation::REFUSEE;
    }

    public function expirer(): void
    {
        if (in_array($this->statut, [StatutEstimation::ACCEPTEE, StatutEstimation::REFUSEE], true)) {
            throw new \DomainException("Cette estimation ne peut pas expirer");
        }

        $this->statut = StatutEstimation::EXPIREE;
    }

    public function recalculerFourchette(CalculateurPrix $calculateur): void
    {
        $zone = ZoneTarifaire::fromCodePostal($this->coordonnees->codePostal);

        $this->fourchette = $calculateur->calculer(
            $this->superficie,
            $this->encombrement,
            $this->salete,
            $this->accessibilite,
            $this->options,
            $zone,
        );
    }

    // Getters
    public function id(): EstimationId
    {
        return $this->id;
    }

    public function typeDeBien(): TypeDeBien
    {
        return $this->typeDeBien;
    }

    public function superficie(): Superficie
    {
        return $this->superficie;
    }

    public function encombrement(): NiveauEncombrement
    {
        return $this->encombrement;
    }

    public function salete(): NiveauSalete
    {
        return $this->salete;
    }

    public function accessibilite(): Accessibilite
    {
        return $this->accessibilite;
    }

    public function options(): Options
    {
        return $this->options;
    }

    public function coordonnees(): Coordonnees
    {
        return $this->coordonnees;
    }

    public function fourchette(): FourchetteEstimation
    {
        return $this->fourchette;
    }

    public function statut(): StatutEstimation
    {
        return $this->statut;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function accessToken(): string
    {
        return $this->accessToken;
    }

    public function commentaire(): ?string
    {
        return $this->commentaire;
    }

    public function photos(): array
    {
        return $this->photos;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}

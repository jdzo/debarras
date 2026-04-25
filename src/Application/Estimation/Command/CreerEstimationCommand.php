<?php

declare(strict_types=1);

namespace App\Application\Estimation\Command;

final readonly class CreerEstimationCommand
{
    public function __construct(
        public string $typeDeBien,
        public string $superficie,
        public string $encombrement,
        public string $salete,
        public string $accessibilite,
        public bool $optionNettoyage = false,
        public bool $optionDesinfection = false,
        public bool $optionDemontage = false,
        public string $nom = '',
        public string $telephone = '',
        public string $email = '',
        public ?string $adresse = null,
        public ?string $codePostal = null,
        public ?string $ville = null,
        public ?string $commentaire = null,
        public array $photos = [],
        public ?string $utmSource = null,
        public ?string $utmMedium = null,
        public ?string $utmCampaign = null,
        public ?string $utmTerm = null,
        public ?string $utmContent = null,
        public ?string $referrer = null,
        public ?string $landingPage = null,
    ) {
    }
}

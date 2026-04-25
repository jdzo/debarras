<?php

declare(strict_types=1);

namespace App\Application\Lead\Command;

final readonly class CreerLeadCommand
{
    public function __construct(
        public string $nom,
        public string $telephone,
        public ?string $email = null,
        public string $typeCapture = 'rappel_gratuit',
        public ?string $typeDeBien = null,
        public ?string $superficie = null,
        public ?string $encombrement = null,
        public ?string $salete = null,
        public ?int $prixEstime = null,
        public ?string $estimationId = null,
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

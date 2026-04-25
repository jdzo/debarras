<?php

declare(strict_types=1);

namespace App\Application\Lead\Query;

final readonly class LeadResult
{
    public function __construct(
        public string $id,
        public string $nom,
        public string $telephone,
        public ?string $email,
        public string $typeCapture,
        public string $score,
        public string $scoreCouleur,
        public string $statut,
        public string $statutCouleur,
        public ?string $estimationId,
        public array $source,
        public \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $relancedAt,
        public ?\DateTimeImmutable $contactedAt,
    ) {
    }
}

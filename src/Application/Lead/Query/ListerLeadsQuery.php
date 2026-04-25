<?php

declare(strict_types=1);

namespace App\Application\Lead\Query;

final readonly class ListerLeadsQuery
{
    public function __construct(
        public ?string $statut = null,
        public ?string $score = null,
        public ?string $recherche = null,
        public int $page = 1,
        public int $limit = 20,
    ) {
    }
}

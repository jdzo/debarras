<?php

declare(strict_types=1);

namespace App\Domain\Estimation\Event;

use App\Domain\Estimation\EstimationId;
use DateTimeImmutable;

final readonly class EstimationCreee
{
    public function __construct(
        public EstimationId $estimationId,
        public DateTimeImmutable $createdAt,
    ) {
    }
}

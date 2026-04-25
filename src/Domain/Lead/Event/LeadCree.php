<?php

declare(strict_types=1);

namespace App\Domain\Lead\Event;

use App\Domain\Lead\LeadId;
use App\Domain\Lead\ValueObject\ScoreLead;
use App\Domain\Lead\ValueObject\TypeCapture;

final readonly class LeadCree
{
    public function __construct(
        public LeadId $leadId,
        public ScoreLead $score,
        public TypeCapture $typeCapture,
        public \DateTimeImmutable $createdAt,
    ) {
    }
}

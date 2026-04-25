<?php

declare(strict_types=1);

namespace App\Domain\Lead;

interface LeadRepository
{
    public function save(Lead $lead): void;

    public function findById(LeadId $id): ?Lead;

    public function nextId(): LeadId;

    /** @return Lead[] */
    public function findLeadsARelancer(\DateTimeImmutable $avant): array;
}

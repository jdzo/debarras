<?php

namespace App\Domain;

use Ramsey\Uuid\Uuid;
final class ChantierId
{
    public function __construct(private string $value)
    {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException("Invalid ChantierId");
        }
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

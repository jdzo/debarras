<?php

declare(strict_types=1);

namespace App\Domain;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

final class ChantierId
{
    public function __construct(private string $value)
    {
        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException('Invalid ChantierId');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }
}

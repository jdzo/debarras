<?php

namespace App\Domain;

class ChantierNom
{
    public function __construct(private string $value)
    {
        if (strlen($value) < 3) {
            throw new \InvalidArgumentException("Nom trop court");
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

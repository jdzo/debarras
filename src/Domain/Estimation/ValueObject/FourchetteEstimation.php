<?php

declare(strict_types=1);

namespace App\Domain\Estimation\ValueObject;

use InvalidArgumentException;

final readonly class FourchetteEstimation
{
    public function __construct(
        public int $prixMin,
        public int $prixMax,
    ) {
        if ($prixMin < 0) {
            throw new InvalidArgumentException('Le prix minimum ne peut pas être négatif');
        }

        if ($prixMax < $prixMin) {
            throw new InvalidArgumentException('Le prix maximum doit être supérieur ou égal au prix minimum');
        }
    }

    public static function fromPrixBase(int $prixBase, float $marge = 0.15): self
    {
        $prixMin = (int) round($prixBase * (1 - $marge));
        $prixMax = (int) round($prixBase * (1 + $marge));

        return new self($prixMin, $prixMax);
    }

    public function prixMoyen(): int
    {
        return (int) round(($this->prixMin + $this->prixMax) / 2);
    }

    public function formatte(): string
    {
        return sprintf('%d€ - %d€', $this->prixMin, $this->prixMax);
    }

    public function toArray(): array
    {
        return [
            'prix_min' => $this->prixMin,
            'prix_max' => $this->prixMax,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            prixMin: $data['prix_min'],
            prixMax: $data['prix_max'],
        );
    }
}

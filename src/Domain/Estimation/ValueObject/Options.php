<?php

declare(strict_types=1);

namespace App\Domain\Estimation\ValueObject;

final readonly class Options
{
    public const PRIX_NETTOYAGE = 150;
    public const PRIX_DESINFECTION = 200;
    public const PRIX_DEMONTAGE = 100;

    public function __construct(
        public bool $nettoyage = false,
        public bool $desinfection = false,
        public bool $demontage = false,
    ) {
    }

    public static function create(
        bool $nettoyage = false,
        bool $desinfection = false,
        bool $demontage = false,
    ): self {
        return new self($nettoyage, $desinfection, $demontage);
    }

    public static function aucune(): self
    {
        return new self();
    }

    public function total(): int
    {
        $total = 0;

        if ($this->nettoyage) {
            $total += self::PRIX_NETTOYAGE;
        }

        if ($this->desinfection) {
            $total += self::PRIX_DESINFECTION;
        }

        if ($this->demontage) {
            $total += self::PRIX_DEMONTAGE;
        }

        return $total;
    }

    public function toArray(): array
    {
        return [
            'nettoyage' => $this->nettoyage,
            'desinfection' => $this->desinfection,
            'demontage' => $this->demontage,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            nettoyage: $data['nettoyage'] ?? false,
            desinfection: $data['desinfection'] ?? false,
            demontage: $data['demontage'] ?? false,
        );
    }
}

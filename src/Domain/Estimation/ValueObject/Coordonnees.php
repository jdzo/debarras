<?php

declare(strict_types=1);

namespace App\Domain\Estimation\ValueObject;

final readonly class Coordonnees
{
    public function __construct(
        public string $nom,
        public string $telephone,
        public string $email,
        public ?string $adresse = null,
        public ?string $codePostal = null,
        public ?string $ville = null,
    ) {
        if (empty(trim($nom))) {
            throw new \InvalidArgumentException("Le nom est obligatoire");
        }

        if (empty(trim($telephone))) {
            throw new \InvalidArgumentException("Le téléphone est obligatoire");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("L'email n'est pas valide");
        }
    }

    public static function create(
        string $nom,
        string $telephone,
        string $email,
        ?string $adresse = null,
        ?string $codePostal = null,
        ?string $ville = null,
    ): self {
        return new self(
            nom: trim($nom),
            telephone: self::normaliserTelephone($telephone),
            email: strtolower(trim($email)),
            adresse: $adresse ? trim($adresse) : null,
            codePostal: $codePostal ? trim($codePostal) : null,
            ville: $ville ? trim($ville) : null,
        );
    }

    private static function normaliserTelephone(string $telephone): string
    {
        return preg_replace('/[^0-9+]/', '', trim($telephone));
    }

    public function adresseComplete(): ?string
    {
        if (!$this->adresse) {
            return null;
        }

        $parts = array_filter([
            $this->adresse,
            trim(($this->codePostal ?? '') . ' ' . ($this->ville ?? '')),
        ]);

        return implode(', ', $parts);
    }

    public function toArray(): array
    {
        return [
            'nom' => $this->nom,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'adresse' => $this->adresse,
            'code_postal' => $this->codePostal,
            'ville' => $this->ville,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            nom: $data['nom'],
            telephone: $data['telephone'],
            email: $data['email'],
            adresse: $data['adresse'] ?? null,
            codePostal: $data['code_postal'] ?? null,
            ville: $data['ville'] ?? null,
        );
    }
}

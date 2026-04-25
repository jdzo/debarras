<?php

declare(strict_types=1);

namespace App\Domain\Lead\ValueObject;

use InvalidArgumentException;

final readonly class ContactLead
{
    public function __construct(
        public string $nom,
        public string $telephone,
        public ?string $email = null,
    ) {
        if ('' === trim($nom)) {
            throw new InvalidArgumentException('Le nom est obligatoire');
        }
        if ('' === trim($telephone)) {
            throw new InvalidArgumentException('Le téléphone est obligatoire');
        }
        $digitsOnly = preg_replace('/[^0-9]/', '', $telephone);
        if (strlen($digitsOnly) < 9) {
            throw new InvalidArgumentException('Le numéro de téléphone doit contenir au moins 9 chiffres');
        }
        if (null !== $email && !filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("L'email n'est pas valide");
        }
    }

    public function toArray(): array
    {
        return [
            'nom' => $this->nom,
            'telephone' => $this->telephone,
            'email' => $this->email,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            nom: $data['nom'],
            telephone: $data['telephone'],
            email: $data['email'] ?? null,
        );
    }
}

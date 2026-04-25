<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'estimation')]
class EstimationEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    public string $id;

    #[ORM\Column(type: 'string', length: 30)]
    public string $typeDeBien;

    #[ORM\Column(type: 'string', length: 20)]
    public string $superficie;

    #[ORM\Column(type: 'string', length: 30)]
    public string $encombrement;

    #[ORM\Column(type: 'string', length: 20)]
    public string $salete;

    #[ORM\Column(type: 'string', length: 30)]
    public string $accessibilite;

    #[ORM\Column(type: 'boolean')]
    public bool $optionNettoyage = false;

    #[ORM\Column(type: 'boolean')]
    public bool $optionDesinfection = false;

    #[ORM\Column(type: 'boolean')]
    public bool $optionDemontage = false;

    #[ORM\Column(type: 'string', length: 255)]
    public string $coordNom;

    #[ORM\Column(type: 'string', length: 30)]
    public string $coordTelephone;

    #[ORM\Column(type: 'string', length: 255)]
    public string $coordEmail;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    public ?string $coordAdresse = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    public ?string $coordCodePostal = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    public ?string $coordVille = null;

    #[ORM\Column(type: 'integer')]
    public int $prixMin;

    #[ORM\Column(type: 'integer')]
    public int $prixMax;

    #[ORM\Column(type: 'string', length: 20)]
    public string $statut;

    #[ORM\Column(type: 'string', length: 64)]
    public string $accessToken;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $commentaire = null;

    #[ORM\Column(type: 'json')]
    public array $photos = [];
}

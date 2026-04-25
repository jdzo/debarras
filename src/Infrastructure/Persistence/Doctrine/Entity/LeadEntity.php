<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'lead')]
#[ORM\Index(name: 'idx_lead_statut_created', columns: ['statut', 'created_at'])]
#[ORM\Index(name: 'idx_lead_score', columns: ['score'])]
class LeadEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    public string $id;

    #[ORM\Column(type: 'string', length: 255)]
    public string $nom;

    #[ORM\Column(type: 'string', length: 30)]
    public string $telephone;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    public ?string $email = null;

    #[ORM\Column(type: 'string', length: 30)]
    public string $typeCapture;

    #[ORM\Column(type: 'string', length: 10)]
    public string $score;

    #[ORM\Column(type: 'string', length: 20)]
    public string $statut;

    #[ORM\Column(type: 'guid', nullable: true)]
    public ?string $estimationId = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    public ?string $utmSource = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    public ?string $utmMedium = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    public ?string $utmCampaign = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    public ?string $utmTerm = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    public ?string $utmContent = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    public ?string $referrer = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    public ?string $landingPage = null;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $relancedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $contactedAt = null;
}

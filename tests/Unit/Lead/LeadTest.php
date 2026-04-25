<?php

declare(strict_types=1);

namespace App\Tests\Unit\Lead;

use App\Domain\Lead\Event\LeadCree;
use App\Domain\Lead\Lead;
use App\Domain\Lead\LeadId;
use App\Domain\Lead\ValueObject\ContactLead;
use App\Domain\Lead\ValueObject\ScoreLead;
use App\Domain\Lead\ValueObject\SourceTracking;
use App\Domain\Lead\ValueObject\StatutLead;
use App\Domain\Lead\ValueObject\TypeCapture;
use DomainException;
use PHPUnit\Framework\TestCase;

class LeadTest extends TestCase
{
    public function testCreerEmetUnEvenement(): void
    {
        $lead = $this->creerLead();
        $events = $lead->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(LeadCree::class, $events[0]);
    }

    public function testCreerAvecStatutNouveau(): void
    {
        $lead = $this->creerLead();
        $this->assertSame(StatutLead::NOUVEAU, $lead->statut());
    }

    public function testMarquerContacte(): void
    {
        $lead = $this->creerLead();
        $lead->marquerContacte();

        $this->assertSame(StatutLead::CONTACTE, $lead->statut());
        $this->assertNotNull($lead->contactedAt());
    }

    public function testConvertir(): void
    {
        $lead = $this->creerLead();
        $lead->marquerContacte();
        $lead->convertir();

        $this->assertSame(StatutLead::CONVERTI, $lead->statut());
    }

    public function testConvertirSansContactEchoue(): void
    {
        $lead = $this->creerLead();

        $this->expectException(DomainException::class);
        $lead->convertir();
    }

    public function testPerdre(): void
    {
        $lead = $this->creerLead();
        $lead->perdre();

        $this->assertSame(StatutLead::PERDU, $lead->statut());
    }

    public function testPerdreUnConvertiEchoue(): void
    {
        $lead = $this->creerLead();
        $lead->marquerContacte();
        $lead->convertir();

        $this->expectException(DomainException::class);
        $lead->perdre();
    }

    public function testEnregistrerRelance(): void
    {
        $lead = $this->creerLead();
        $this->assertNull($lead->relancedAt());

        $lead->enregistrerRelance();
        $this->assertNotNull($lead->relancedAt());
    }

    public function testPullDomainEventsVideApresAppel(): void
    {
        $lead = $this->creerLead();
        $lead->pullDomainEvents();

        $this->assertEmpty($lead->pullDomainEvents());
    }

    private function creerLead(ScoreLead $score = ScoreLead::WARM): Lead
    {
        return Lead::creer(
            id: LeadId::generate(),
            contact: new ContactLead('Jean Dupont', '0612345678', 'jean@example.com'),
            typeCapture: TypeCapture::ESTIMATION_COMPLETE,
            source: SourceTracking::empty(),
            score: $score,
        );
    }
}

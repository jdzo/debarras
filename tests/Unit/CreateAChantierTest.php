<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Domain\Chantier;
use App\Domain\ChantierId;
use App\Domain\ChantierNom;
use App\Domain\Enum\StatutChantier;
use PHPUnit\Framework\TestCase;

class CreateAChantierTest extends TestCase
{
    public function testCreateAChantier(): void
    {
        $chantier = Chantier::create(
            ChantierId::generate(),
            new ChantierNom('Mon premier chantier')
        );

        $this->assertEquals('Mon premier chantier', $chantier->nom);
    }

    public function testDemarrerAChantier(): void
    {
        $chantier = Chantier::create(
            ChantierId::generate(),
            new ChantierNom('Mon premier chantier')
        );

        // Le chantier doit être en EN_PREPARATION avant de pouvoir être démarré
        $chantier->statut = StatutChantier::EN_PREPARATION;

        $chantier = $chantier->demarrer();

        $this->assertEquals(StatutChantier::EN_COURS, $chantier->statut);
    }
}

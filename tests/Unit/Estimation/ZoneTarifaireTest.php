<?php

declare(strict_types=1);

namespace App\Tests\Unit\Estimation;

use App\Domain\Estimation\ValueObject\ZoneTarifaire;
use PHPUnit\Framework\TestCase;

class ZoneTarifaireTest extends TestCase
{
    public function testParisEstIleDeFrance(): void
    {
        $zone = ZoneTarifaire::fromCodePostal('75011');
        $this->assertSame(ZoneTarifaire::ILE_DE_FRANCE, $zone);
        $this->assertSame(1.3, $zone->coefficient());
    }

    public function testLyonEstGrandeVille(): void
    {
        $zone = ZoneTarifaire::fromCodePostal('69001');
        $this->assertSame(ZoneTarifaire::GRANDE_VILLE, $zone);
        $this->assertSame(1.15, $zone->coefficient());
    }

    public function testCodePostalInconnuEstProvince(): void
    {
        $zone = ZoneTarifaire::fromCodePostal('24000');
        $this->assertSame(ZoneTarifaire::PROVINCE, $zone);
        $this->assertSame(1.0, $zone->coefficient());
    }

    public function testCodePostalNullEstProvince(): void
    {
        $zone = ZoneTarifaire::fromCodePostal(null);
        $this->assertSame(ZoneTarifaire::PROVINCE, $zone);
    }

    public function testCodePostalVideEstProvince(): void
    {
        $zone = ZoneTarifaire::fromCodePostal('');
        $this->assertSame(ZoneTarifaire::PROVINCE, $zone);
    }

    public function testTousDepartementsIdf(): void
    {
        foreach (['75', '77', '78', '91', '92', '93', '94', '95'] as $dept) {
            $zone = ZoneTarifaire::fromCodePostal($dept . '000');
            $this->assertSame(ZoneTarifaire::ILE_DE_FRANCE, $zone, "Dept $dept devrait etre IDF");
        }
    }
}

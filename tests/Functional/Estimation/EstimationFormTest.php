<?php

declare(strict_types=1);

namespace App\Tests\Functional\Estimation;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EstimationFormTest extends WebTestCase
{
    public function testFormulairePageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/estimation');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testFormulaireContientCsrfToken(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/estimation');

        $this->assertGreaterThan(0, $crawler->filter('input[name="_token"]')->count());
    }

    public function testSoumettreEstimationValide(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/estimation');
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $client->request('POST', '/estimation', [
            '_token' => $token,
            'type_de_bien' => 'maison',
            'superficie' => '50_100',
            'encombrement' => 'meuble_normal',
            'salete' => 'propre',
            'accessibilite' => 'rdc',
            'nom' => 'Jean Test',
            'telephone' => '0612345678',
            'email' => 'jean@test.fr',
            'code_postal' => '35000',
        ]);

        $this->assertResponseRedirects();
        $location = $client->getResponse()->headers->get('Location');
        $this->assertStringContainsString('/estimation/', $location);

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testResultatAfficheForchettePrix(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/estimation');
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $client->request('POST', '/estimation', [
            '_token' => $token,
            'type_de_bien' => 'appartement',
            'superficie' => '100_200',
            'encombrement' => 'tres_encombre',
            'salete' => 'sale',
            'accessibilite' => 'etage_sans_ascenseur',
            'option_nettoyage' => '1',
            'nom' => 'Marie Test',
            'telephone' => '0698765432',
            'email' => 'marie@test.fr',
        ]);

        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $pageText = $crawler->text();
        $this->assertMatchesRegularExpression('/\d+\s*€/', $pageText);
    }

    public function testApercuPrixJson(): void
    {
        $client = static::createClient();

        $client->request('POST', '/estimation/apercu', [
            'superficie' => '50_100',
            'encombrement' => 'meuble_normal',
            'salete' => 'propre',
            'accessibilite' => 'rdc',
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('prix_min', $data);
        $this->assertArrayHasKey('prix_max', $data);
        $this->assertArrayHasKey('formatte', $data);
        $this->assertGreaterThan(0, $data['prix_min']);
        $this->assertGreaterThan($data['prix_min'], $data['prix_max']);
    }

    public function testApercuPrixDonneesIncompletes(): void
    {
        $client = static::createClient();

        $client->request('POST', '/estimation/apercu', [
            'superficie' => '50_100',
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testApercuPrixAvecCodePostalIdf(): void
    {
        $client = static::createClient();

        $client->request('POST', '/estimation/apercu', [
            'superficie' => '50_100',
            'encombrement' => 'meuble_normal',
            'salete' => 'propre',
            'accessibilite' => 'rdc',
            'code_postal' => '75001',
        ]);

        $this->assertResponseIsSuccessful();
        $dataIdf = json_decode($client->getResponse()->getContent(), true);

        $client->request('POST', '/estimation/apercu', [
            'superficie' => '50_100',
            'encombrement' => 'meuble_normal',
            'salete' => 'propre',
            'accessibilite' => 'rdc',
            'code_postal' => '35000',
        ]);

        $dataProvince = json_decode($client->getResponse()->getContent(), true);

        $this->assertGreaterThan($dataProvince['prix_min'], $dataIdf['prix_min']);
    }

    public function testEstimationSansTokenCsrf(): void
    {
        $client = static::createClient();
        $client->request('GET', '/estimation');

        $client->request('POST', '/estimation', [
            'type_de_bien' => 'maison',
            'superficie' => '50_100',
            'encombrement' => 'meuble_normal',
            'nom' => 'Jean Test',
            'telephone' => '0612345678',
            'email' => 'jean@test.fr',
        ]);

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertTrue(in_array($statusCode, [401, 403], true), "Expected 401 or 403, got $statusCode");
    }
}

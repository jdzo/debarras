<?php

declare(strict_types=1);

namespace App\Tests\Functional\Navigation;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NavigationTest extends WebTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('publicPagesProvider')]
    public function testPublicPageReturns200(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public static function publicPagesProvider(): iterable
    {
        yield 'accueil' => ['/'];
        yield 'estimation' => ['/estimation'];
        yield 'contact' => ['/contact'];
        yield 'services' => ['/services'];
        yield 'service maison' => ['/services/maison'];
        yield 'service appartement' => ['/services/appartement'];
        yield 'service diogene' => ['/services/diogene'];
        yield 'service succession' => ['/services/succession'];
        yield 'ville rennes' => ['/debarras/rennes'];
        yield 'ville vitre' => ['/debarras/vitre'];
        yield 'sitemap' => ['/sitemap.xml'];
    }

    public function testNavbarContainsLinks(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $nav = $crawler->filter('.navbar');
        $this->assertSelectorTextContains('.nav-logo', 'ClearWay');
        $this->assertGreaterThan(0, $nav->filter('a[href="/services"]')->count());
        $this->assertGreaterThan(0, $nav->filter('a[href="/contact"]')->count());
        $this->assertGreaterThan(0, $nav->filter('a[href="/estimation"]')->count());
    }

    public function testFooterContainsNavigationAndZones(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $footer = $crawler->filter('.site-footer');
        $this->assertStringContainsString('ClearWay', $footer->text());
        $this->assertGreaterThan(0, $footer->filter('a[href="/debarras/rennes"]')->count());
        $this->assertGreaterThan(0, $footer->filter('a[href="/debarras/vitre"]')->count());
        $this->assertGreaterThan(0, $footer->filter('.footer-col')->count(), "Footer HTML:\n" . $footer->html());
    }

    public function testStickyCta(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $stickyCta = $crawler->filter('.sticky-cta');
        $this->assertGreaterThan(0, $stickyCta->count());
        $this->assertStringContainsString('/estimation', $stickyCta->attr('href'));
    }

    public function testWhatsAppButton(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertGreaterThan(0, $crawler->filter('.whatsapp-btn')->count());
    }

    public function test404OnUnknownPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/page-inexistante-xyz');

        $this->assertResponseStatusCodeSame(404);
    }

    public function test404OnUnknownService(): void
    {
        $client = static::createClient();
        $client->request('GET', '/services/inconnu');

        $this->assertResponseStatusCodeSame(404);
    }

    public function test404OnUnknownVille(): void
    {
        $client = static::createClient();
        $client->request('GET', '/debarras/marseille');

        $this->assertResponseStatusCodeSame(404);
    }
}

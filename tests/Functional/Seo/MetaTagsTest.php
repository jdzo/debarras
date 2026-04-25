<?php

declare(strict_types=1);

namespace App\Tests\Functional\Seo;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MetaTagsTest extends WebTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('publicPagesProvider')]
    public function testTitlePresent(string $url): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        $title = $crawler->filter('title')->text();
        $this->assertNotEmpty($title);
        $this->assertStringContainsString('ClearWay', $title);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('publicPagesProvider')]
    public function testMetaDescriptionPresent(string $url): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        $meta = $crawler->filter('meta[name="description"]');
        $this->assertGreaterThan(0, $meta->count(), "meta description missing on $url");
        $this->assertNotEmpty($meta->attr('content'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('publicPagesProvider')]
    public function testOpenGraphTags(string $url): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        $this->assertGreaterThan(0, $crawler->filter('meta[property="og:title"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('meta[property="og:description"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('meta[property="og:url"]')->count());
        $this->assertEquals('fr_FR', $crawler->filter('meta[property="og:locale"]')->attr('content'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('publicPagesProvider')]
    public function testCanonicalLink(string $url): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        $canonical = $crawler->filter('link[rel="canonical"]');
        $this->assertGreaterThan(0, $canonical->count(), "canonical link missing on $url");
        $this->assertNotEmpty($canonical->attr('href'));
    }

    public function testJsonLdOnAccueil(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $jsonLd = $crawler->filter('script[type="application/ld+json"]');
        $this->assertGreaterThan(0, $jsonLd->count());

        $data = json_decode($jsonLd->text(), true);
        $this->assertEquals('LocalBusiness', $data['@type']);
        $this->assertStringContainsString('ClearWay', $data['name']);
    }

    public function testSitemapXmlIsValid(): void
    {
        $client = static::createClient();
        $client->request('GET', '/sitemap.xml');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/xml');

        $content = $client->getResponse()->getContent();
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('<loc>', $content);
    }

    public static function publicPagesProvider(): iterable
    {
        yield 'accueil' => ['/'];
        yield 'estimation' => ['/estimation'];
        yield 'contact' => ['/contact'];
        yield 'services' => ['/services'];
        yield 'service maison' => ['/services/maison'];
        yield 'ville rennes' => ['/debarras/rennes'];
    }
}

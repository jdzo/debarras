<?php

declare(strict_types=1);

namespace App\UI\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{
    private const SERVICES = ['maison', 'appartement', 'diogene', 'succession', 'demenagement', 'logement-insalubre', 'cave-grenier'];
    private const ARTICLES = ['vider-maison-apres-deces'];

    #[Route('/sitemap.xml', name: 'sitemap', defaults: ['_format' => 'xml'])]
    public function index(): Response
    {
        $today = date('Y-m-d');

        $urls = [
            ['loc' => $this->generateUrl('accueil', [], UrlGeneratorInterface::ABSOLUTE_URL), 'priority' => '1.0', 'lastmod' => $today],
            ['loc' => $this->generateUrl('estimation_formulaire', [], UrlGeneratorInterface::ABSOLUTE_URL), 'priority' => '0.9', 'lastmod' => $today],
            ['loc' => $this->generateUrl('services', [], UrlGeneratorInterface::ABSOLUTE_URL), 'priority' => '0.8', 'lastmod' => $today],
            ['loc' => $this->generateUrl('contact', [], UrlGeneratorInterface::ABSOLUTE_URL), 'priority' => '0.7', 'lastmod' => $today],
        ];

        foreach (self::SERVICES as $slug) {
            $urls[] = [
                'loc' => $this->generateUrl('services_detail', ['slug' => $slug], UrlGeneratorInterface::ABSOLUTE_URL),
                'priority' => '0.8',
                'lastmod' => $today,
            ];
        }

        foreach (VilleController::getVillesSlugs() as $ville) {
            $urls[] = [
                'loc' => $this->generateUrl('ville_page', ['ville' => $ville], UrlGeneratorInterface::ABSOLUTE_URL),
                'priority' => '0.8',
                'lastmod' => $today,
            ];

            foreach (VilleController::getServicesSlugs() as $service) {
                $urls[] = [
                    'loc' => $this->generateUrl('ville_service_page', ['service' => $service, 'ville' => $ville], UrlGeneratorInterface::ABSOLUTE_URL),
                    'priority' => '0.7',
                    'lastmod' => $today,
                ];
            }
        }

        $urls[] = [
            'loc' => $this->generateUrl('qui_sommes_nous', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'priority' => '0.5',
            'lastmod' => $today,
        ];

        $urls[] = [
            'loc' => $this->generateUrl('mentions_legales', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'priority' => '0.3',
            'lastmod' => $today,
        ];

        $urls[] = [
            'loc' => $this->generateUrl('blog', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'priority' => '0.7',
            'lastmod' => $today,
        ];

        foreach (self::ARTICLES as $slug) {
            $urls[] = [
                'loc' => $this->generateUrl('blog_article', ['slug' => $slug], UrlGeneratorInterface::ABSOLUTE_URL),
                'priority' => '0.7',
                'lastmod' => $today,
            ];
        }

        $response = new Response(
            $this->renderView('sitemap.xml.twig', ['urls' => $urls]),
            200,
            ['Content-Type' => 'application/xml'],
        );

        $response->setSharedMaxAge(3600);

        return $response;
    }
}

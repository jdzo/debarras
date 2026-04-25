<?php

declare(strict_types=1);

namespace App\UI\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class ServicesController extends AbstractController
{
    private const SERVICES = [
        'succession' => [
            'titre' => 'Débarras succession',
            'icon' => '📋',
            'description' => 'Vidage de biens suite à un décès ou une succession. Nous traitons les affaires avec soin et respect, en coordination avec les familles.',
            'details' => [
                'Tri des objets de valeur et souvenirs',
                'Mise de côté des documents importants',
                'Don aux associations des objets récupérables',
                'Coordination avec notaire ou famille',
                'Remise en état du logement',
            ],
        ],
        'diogene' => [
            'titre' => 'Débarras Diogène',
            'icon' => '⚠️',
            'description' => 'Intervention spécialisée pour les logements touchés par le syndrome de Diogène. Équipe formée, discrétion et respect garantis.',
            'details' => [
                'Équipe formée aux situations sensibles',
                'Équipement de protection adapté',
                'Désinfection et traitement anti-nuisibles',
                'Nettoyage approfondi après débarras',
                'Intervention discrète et respectueuse',
                'Coordination avec les services sociaux si nécessaire',
            ],
        ],
        'demenagement' => [
            'titre' => 'Débarras déménagement',
            'icon' => '🚚',
            'description' => 'Évacuation de vos encombrants avant, pendant ou après un déménagement. Rapide, efficace, sans stress.',
            'details' => [
                'Évacuation de tous les encombrants',
                'Intervention avant ou après déménagement',
                'Démontage des meubles non conservés',
                'Nettoyage du logement après vidage',
                'Recyclage et don des objets récupérables',
            ],
        ],
        'logement-insalubre' => [
            'titre' => 'Débarras logement insalubre',
            'icon' => '🏚️',
            'description' => 'Remise en état complète de logements insalubres. Évacuation, nettoyage approfondi, désinfection.',
            'details' => [
                'Évacuation complète des encombrants et déchets',
                'Nettoyage approfondi de toutes les pièces',
                'Désinfection et traitement anti-nuisibles',
                'Remise en état pour relocation ou vente',
                'Intervention discrète et rapide',
            ],
        ],
        'cave-grenier' => [
            'titre' => 'Débarras cave, grenier & garage',
            'icon' => '📦',
            'description' => 'Vidage complet de caves, greniers et garages encombrés depuis des années. On évacue tout, même les objets lourds.',
            'details' => [
                'Vidage complet cave, grenier ou garage',
                'Évacuation des objets lourds et volumineux',
                'Tri et recyclage des matériaux',
                'Nettoyage après intervention',
                'Accès difficiles pris en charge',
            ],
        ],
        'maison' => [
            'titre' => 'Débarras maison',
            'icon' => '🏠',
            'description' => 'Vidage complet de votre maison, du grenier à la cave. Nous prenons en charge le tri, l\'évacuation et le nettoyage de toutes les pièces.',
            'details' => [
                'Tri et évacuation de tous les encombrants',
                'Démontage des meubles volumineux',
                'Nettoyage après intervention',
                'Recyclage et valorisation des objets récupérables',
                'Intervention rapide sous 48h',
            ],
        ],
        'appartement' => [
            'titre' => 'Débarras appartement',
            'icon' => '🏢',
            'description' => 'Débarras complet de votre appartement, quelle que soit la taille ou l\'étage. Nous gérons l\'accessibilité et la logistique d\'évacuation.',
            'details' => [
                'Prise en charge de l\'acheminement (escaliers, ascenseur)',
                'Protection des parties communes',
                'Évacuation de tous types d\'objets',
                'Nettoyage des pièces vidées',
                'Discrétion assurée vis-à-vis du voisinage',
            ],
        ],
    ];

    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    #[Route('/services', name: 'services')]
    public function index(): Response
    {
        return $this->render('services/index.html.twig', [
            'services' => self::SERVICES,
        ]);
    }

    #[Route('/services/{slug}', name: 'services_detail')]
    public function detail(string $slug): Response
    {
        if (!isset(self::SERVICES[$slug])) {
            throw $this->createNotFoundException('Service introuvable');
        }

        $template = "services/{$slug}.html.twig";

        if (!$this->twig->getLoader()->exists($template)) {
            $template = 'services/detail.html.twig';
        }

        return $this->render($template, [
            'slug' => $slug,
            'service' => self::SERVICES[$slug],
            'autres_services' => array_diff_key(self::SERVICES, [$slug => true]),
        ]);
    }
}

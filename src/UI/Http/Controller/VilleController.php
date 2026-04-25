<?php

declare(strict_types=1);

namespace App\UI\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VilleController extends AbstractController
{
    private const VILLES = [
        'vitre' => [
            'nom' => 'Vitré',
            'departement' => 'Ille-et-Vilaine (35)',
            'region' => 'Bretagne',
            'population' => '18 000',
            'description' => 'Besoin d\'un débarras à Vitré ? ClearWay intervient rapidement pour vider votre maison, appartement ou local dans tout le Pays de Vitré.',
            'zone' => 'Vitré et ses environs : Châteaubourg, Argentré-du-Plessis, Janzé, La Guerche-de-Bretagne',
            'specificites' => [
                'Intervention rapide dans tout le Pays de Vitré',
                'Connaissance du tissu local et des accès',
                'Prise en charge des successions et départs en maison de retraite',
                'Coordination avec les notaires et agences immobilières locales',
            ],
        ],
        'fougeres' => [
            'nom' => 'Fougères',
            'departement' => 'Ille-et-Vilaine (35)',
            'region' => 'Bretagne',
            'population' => '20 000',
            'description' => 'Service de débarras professionnel à Fougères et dans le Pays de Fougères. Maison, appartement, cave, grenier — nous évacuons tout.',
            'zone' => 'Fougères et alentours : Louvigné-du-Désert, Saint-Brice-en-Coglès, Antrain, Lécousse',
            'specificites' => [
                'Intervention dans tout le Pays de Fougères',
                'Débarras de maisons anciennes et corps de ferme',
                'Évacuation de greniers et caves humides',
                'Tri et valorisation des objets récupérables',
            ],
        ],
        'laval' => [
            'nom' => 'Laval',
            'departement' => 'Mayenne (53)',
            'region' => 'Pays de la Loire',
            'population' => '54 000',
            'description' => 'Débarras professionnel à Laval et en Mayenne. Estimation gratuite, intervention sous 48h pour vider votre bien.',
            'zone' => 'Laval et toute la Mayenne : Château-Gontier, Mayenne, Évron, Craon',
            'specificites' => [
                'Couverture de tout le département de la Mayenne',
                'Intervention sous 48h sur Laval et agglomération',
                'Spécialiste des successions et ventes immobilières',
                'Tarifs compétitifs adaptés au marché local',
            ],
        ],
        'rennes' => [
            'nom' => 'Rennes',
            'departement' => 'Ille-et-Vilaine (35)',
            'region' => 'Bretagne',
            'population' => '225 000',
            'description' => 'Débarras maison et appartement à Rennes et dans toute l\'agglomération rennaise. Devis gratuit, intervention rapide, prix transparents.',
            'zone' => 'Rennes Métropole : Cesson-Sévigné, Chantepie, Bruz, Pacé, Saint-Grégoire, Betton, Thorigné-Fouillard',
            'specificites' => [
                'Intervention dans toute Rennes Métropole',
                'Gestion des contraintes d\'accès en centre-ville',
                'Protection des parties communes en immeuble',
                'Débarras Diogène avec équipe spécialisée',
            ],
        ],
        'saint-malo' => [
            'nom' => 'Saint-Malo',
            'departement' => 'Ille-et-Vilaine (35)',
            'region' => 'Bretagne',
            'population' => '47 000',
            'description' => 'Service de débarras à Saint-Malo et sur la Côte d\'Émeraude. Résidences principales et secondaires, successions, locations saisonnières.',
            'zone' => 'Saint-Malo et Côte d\'Émeraude : Dinard, Dinan, Cancale, Combourg, Dol-de-Bretagne',
            'specificites' => [
                'Spécialiste résidences secondaires et locations',
                'Intervention sur toute la Côte d\'Émeraude',
                'Débarras après succession dans les maisons de bord de mer',
                'Gestion des accès difficiles (intra-muros, ruelles)',
            ],
        ],
    ];

    private const SERVICES = [
        'maison' => ['titre' => 'Débarras maison', 'icon' => '🏠'],
        'appartement' => ['titre' => 'Débarras appartement', 'icon' => '🏢'],
        'diogene' => ['titre' => 'Débarras Diogène', 'icon' => '⚠️'],
        'succession' => ['titre' => 'Débarras succession', 'icon' => '📋'],
        'demenagement' => ['titre' => 'Débarras déménagement', 'icon' => '🚚'],
        'logement-insalubre' => ['titre' => 'Débarras logement insalubre', 'icon' => '🏚️'],
        'cave-grenier' => ['titre' => 'Débarras cave grenier', 'icon' => '📦'],
    ];

    #[Route('/debarras/{ville}', name: 'ville_page', priority: -1)]
    public function ville(string $ville): Response
    {
        if (!isset(self::VILLES[$ville])) {
            throw $this->createNotFoundException('Ville introuvable');
        }

        return $this->render('ville/page.html.twig', [
            'slug' => $ville,
            'ville' => self::VILLES[$ville],
            'services' => self::SERVICES,
            'autres_villes' => array_diff_key(self::VILLES, [$ville => true]),
        ]);
    }

    #[Route('/debarras-{service}/{ville}', name: 'ville_service_page')]
    public function villeService(string $service, string $ville): Response
    {
        if (!isset(self::VILLES[$ville]) || !isset(self::SERVICES[$service])) {
            throw $this->createNotFoundException('Page introuvable');
        }

        return $this->render('ville/service.html.twig', [
            'villeSlug' => $ville,
            'serviceSlug' => $service,
            'ville' => self::VILLES[$ville],
            'service' => self::SERVICES[$service],
            'autres_villes' => array_diff_key(self::VILLES, [$ville => true]),
        ]);
    }

    public static function getVillesSlugs(): array
    {
        return array_keys(self::VILLES);
    }

    public static function getServicesSlugs(): array
    {
        return array_keys(self::SERVICES);
    }
}

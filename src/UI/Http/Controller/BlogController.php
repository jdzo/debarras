<?php

declare(strict_types=1);

namespace App\UI\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    private const ARTICLES = [
        'vider-maison-apres-deces' => [
            'titre' => 'Comment faire vider une maison après un décès : guide complet',
            'description' => 'Toutes les étapes pour vider la maison d\'un proche décédé : démarches, tri, obligations légales et choix du prestataire.',
            'date' => '2026-03-25',
            'temps_lecture' => '8 min',
        ],
    ];

    #[Route('/blog', name: 'blog')]
    public function index(): Response
    {
        return $this->render('blog/index.html.twig', [
            'articles' => self::ARTICLES,
        ]);
    }

    #[Route('/blog/{slug}', name: 'blog_article')]
    public function article(string $slug): Response
    {
        if (!isset(self::ARTICLES[$slug])) {
            throw $this->createNotFoundException('Article introuvable');
        }

        return $this->render("blog/{$slug}.html.twig", [
            'slug' => $slug,
            'article' => self::ARTICLES[$slug],
            'autres_articles' => array_diff_key(self::ARTICLES, [$slug => true]),
        ]);
    }
}

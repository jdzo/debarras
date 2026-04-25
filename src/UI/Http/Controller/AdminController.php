<?php

declare(strict_types=1);

namespace App\UI\Http\Controller;

use App\Application\Estimation\Query\ConsulterEstimationHandler;
use App\Application\Estimation\Query\ConsulterEstimationQuery;
use App\Application\Estimation\Query\EstimationResult;
use App\Application\Estimation\Query\ListerEstimationsHandler;
use App\Application\Estimation\Query\ListerEstimationsQuery;
use App\Application\Lead\Query\ListerLeadsHandler;
use App\Application\Lead\Query\ListerLeadsQuery;
use App\Domain\Estimation\EstimationId;
use App\Domain\Estimation\EstimationRepository;
use App\Domain\Estimation\ValueObject\StatutEstimation;
use App\Domain\Lead\LeadId;
use App\Domain\Lead\LeadRepository;
use App\Domain\Lead\ValueObject\ScoreLead;
use App\Domain\Lead\ValueObject\StatutLead;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function dashboard(Request $request, ListerEstimationsHandler $handler): Response
    {
        $statut = $request->query->get('statut');
        $recherche = mb_substr($request->query->getString('q'), 0, 100);
        $page = max(1, $request->query->getInt('page', 1));

        $result = $handler(new ListerEstimationsQuery(
            statut: $statut ?: null,
            recherche: $recherche ?: null,
            page: $page,
        ));

        $statsResult = $handler(new ListerEstimationsQuery(limit: 10000));
        $stats = $this->computeStats($statsResult['estimations']);

        return $this->render('admin/dashboard.html.twig', [
            'estimations' => $result['estimations'],
            'total' => $result['total'],
            'pages' => $result['pages'],
            'page' => $page,
            'statut_filtre' => $statut,
            'recherche' => $recherche,
            'statuts' => StatutEstimation::cases(),
            'stats' => $stats,
        ]);
    }

    #[Route('/export-csv', name: 'admin_export_csv')]
    public function exportCsv(ListerEstimationsHandler $handler): StreamedResponse
    {
        $result = $handler(new ListerEstimationsQuery(limit: 10000));

        return new StreamedResponse(static function () use ($result): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Nom', 'Telephone', 'Email', 'Type', 'Superficie', 'Estimation', 'Statut']);

            foreach ($result['estimations'] as $estimation) {
                fputcsv($handle, [
                    $estimation->createdAt->format('d/m/Y H:i'),
                    $estimation->coordonnees['nom'],
                    $estimation->coordonnees['telephone'],
                    $estimation->coordonnees['email'],
                    $estimation->typeDeBien,
                    $estimation->superficie,
                    $estimation->fourchette,
                    $estimation->statut,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="estimations_' . date('Y-m-d') . '.csv"',
        ]);
    }

    #[Route('/leads', name: 'admin_leads')]
    public function leads(Request $request, ListerLeadsHandler $handler): Response
    {
        $result = $handler(new ListerLeadsQuery(
            statut: $request->query->get('statut') ?: null,
            score: $request->query->get('score') ?: null,
            recherche: mb_substr($request->query->getString('q'), 0, 100) ?: null,
            page: max(1, $request->query->getInt('page', 1)),
        ));

        $rechercheLeads = mb_substr($request->query->getString('q'), 0, 100);

        return $this->render('admin/leads.html.twig', [
            'leads' => $result['leads'],
            'total' => $result['total'],
            'pages' => $result['pages'],
            'page' => max(1, $request->query->getInt('page', 1)),
            'statut_filtre' => $request->query->get('statut'),
            'score_filtre' => $request->query->get('score'),
            'recherche' => $rechercheLeads,
            'statuts' => StatutLead::cases(),
            'scores' => ScoreLead::cases(),
        ]);
    }

    #[Route('/lead/{id}/statut', name: 'admin_lead_statut', methods: ['POST'])]
    public function changerStatutLead(string $id, Request $request, LeadRepository $leadRepository): Response
    {
        if (!$this->isCsrfTokenValid('admin_statut', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $lead = $leadRepository->findById(LeadId::fromString($id));

        if (null === $lead) {
            throw $this->createNotFoundException('Lead introuvable');
        }

        match ($request->request->getString('action')) {
            'contacter' => $lead->marquerContacte(),
            'convertir' => $lead->convertir(),
            'perdre' => $lead->perdre(),
            default => throw $this->createNotFoundException('Action inconnue'),
        };

        $leadRepository->save($lead);

        $this->addFlash('success', 'Statut du lead mis à jour.');

        return $this->redirectToRoute('admin_leads');
    }

    #[Route('/estimation/{id}', name: 'admin_estimation_detail')]
    public function detail(string $id, ConsulterEstimationHandler $handler): Response
    {
        $estimation = $handler(new ConsulterEstimationQuery($id));

        if (null === $estimation) {
            throw $this->createNotFoundException('Estimation introuvable');
        }

        return $this->render('admin/estimation_detail.html.twig', [
            'estimation' => $estimation,
        ]);
    }

    #[Route('/estimation/{id}/statut', name: 'admin_estimation_statut', methods: ['POST'])]
    public function changerStatut(
        string $id,
        Request $request,
        EstimationRepository $repository,
    ): Response {
        if (!$this->isCsrfTokenValid('admin_statut', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $estimation = $repository->findById(EstimationId::fromString($id));

        if (null === $estimation) {
            throw $this->createNotFoundException('Estimation introuvable');
        }

        $action = $request->request->getString('action');

        match ($action) {
            'contacter' => $estimation->marquerContactee(),
            'envoyer_devis' => $estimation->envoyerDevis(),
            'accepter' => $estimation->accepter(),
            'refuser' => $estimation->refuser(),
            'expirer' => $estimation->expirer(),
            default => throw $this->createNotFoundException('Action inconnue'),
        };

        $repository->save($estimation);

        $this->addFlash('success', 'Statut mis à jour avec succès.');

        return $this->redirectToRoute('admin_estimation_detail', ['id' => $id]);
    }

    /**
     * @param EstimationResult[] $estimations
     *
     * @return array{total: int, nouvelles: int, acceptees: int, ca_potentiel: int}
     */
    private function computeStats(array $estimations): array
    {
        $nouvelles = 0;
        $acceptees = 0;
        $caPotentiel = 0;

        foreach ($estimations as $estimation) {
            if ('info' === $estimation->statutCouleur) {
                ++$nouvelles;
            }
            if ('success' === $estimation->statutCouleur) {
                ++$acceptees;
                $caPotentiel += $estimation->prixMin;
            }
        }

        return [
            'total' => count($estimations),
            'nouvelles' => $nouvelles,
            'acceptees' => $acceptees,
            'ca_potentiel' => $caPotentiel,
        ];
    }
}

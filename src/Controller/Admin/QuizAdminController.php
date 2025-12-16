<?php

namespace App\Controller\Admin;

use App\Entity\QuizSession;
use App\Repository\CategoryRepository;
use App\Service\QuizSessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/quiz')]
class QuizAdminController extends AbstractController
{
    public function __construct(
        private readonly QuizSessionService $quizSessionService,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    #[Route('', name: 'admin_quiz_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 20;

        // Filters
        $status = $request->query->get('status');
        $categoryParam = $request->query->get('category');
        $categoryId = $categoryParam !== null && $categoryParam !== '' ? (int) $categoryParam : null;
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');

        $result = $this->quizSessionService->findPaginated(
            $page,
            $limit,
            $status,
            $categoryId,
            $dateFrom,
            $dateTo
        );

        $stats = $this->quizSessionService->getStatistics();

        return $this->render('admin/quiz/index.html.twig', [
            'sessions' => $result['sessions'],
            'stats' => $stats,
            'categories' => $this->categoryRepository->findAll(),
            'filters' => [
                'status' => $status,
                'category' => $categoryId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $result['total'],
                'pages' => $result['pages'],
            ],
        ]);
    }

    #[Route('/bulk-delete', name: 'admin_quiz_bulk_delete', methods: ['POST'])]
    public function bulkDelete(Request $request): Response
    {
        $ids = $request->request->all('ids');

        if ($this->isCsrfTokenValid('bulk_delete', $request->request->get('_token')) && !empty($ids)) {
            $deleted = $this->quizSessionService->bulkDelete($ids);
            $this->addFlash('success', sprintf('%d quiz session(s) deleted successfully.', $deleted));
        }

        return $this->redirectToRoute('admin_quiz_index');
    }

    #[Route('/cleanup/abandoned', name: 'admin_quiz_cleanup_abandoned', methods: ['POST'])]
    public function cleanupAbandoned(Request $request): Response
    {
        if ($this->isCsrfTokenValid('cleanup_abandoned', $request->request->get('_token'))) {
            $deleted = $this->quizSessionService->cleanupAbandoned(7);
            $this->addFlash('success', sprintf('%d abandoned session(s) cleaned up.', $deleted));
        }

        return $this->redirectToRoute('admin_quiz_index');
    }

    #[Route('/{id}', name: 'admin_quiz_show', methods: ['GET'])]
    public function show(QuizSession $session): Response
    {
        return $this->render('admin/quiz/show.html.twig', [
            'session' => $session,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_quiz_delete', methods: ['POST'])]
    public function delete(Request $request, QuizSession $session): Response
    {
        if ($this->isCsrfTokenValid('delete' . $session->getId(), $request->request->get('_token'))) {
            $this->quizSessionService->delete($session);
            $this->addFlash('success', 'Quiz session deleted successfully.');
        }

        return $this->redirectToRoute('admin_quiz_index');
    }
}

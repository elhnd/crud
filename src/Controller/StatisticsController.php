<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\RevisionStrategyService;
use App\Service\StatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/statistics')]
class StatisticsController extends AbstractController
{
    public function __construct(
        private readonly StatisticsService $statisticsService,
        private readonly CategoryRepository $categoryRepository,
        private readonly RevisionStrategyService $revisionStrategyService,
    ) {
    }

    #[Route('', name: 'statistics_dashboard')]
    public function dashboard(): Response
    {
        $stats = $this->statisticsService->getDashboardStats();
        $categories = $this->categoryRepository->findAll();

        return $this->render('statistics/dashboard.html.twig', [
            'stats' => $stats,
            'categories' => $categories,
        ]);
    }

    #[Route('/category/{id}', name: 'statistics_category')]
    public function category(Category $category): Response
    {
        $stats = $this->statisticsService->getCategoryDetailedStats($category);

        return $this->render('statistics/category.html.twig', [
            'category' => $category,
            'stats' => $stats,
        ]);
    }

    #[Route('/weak-areas', name: 'statistics_weak_areas')]
    public function weakAreas(): Response
    {
        $stats = $this->statisticsService->getDashboardStats();
        $mostFailed = $this->statisticsService->getMostFailedQuestions(20);

        return $this->render('statistics/weak_areas.html.twig', [
            'weakAreas' => $stats['weakAreas'],
            'mostFailedQuestions' => $mostFailed,
        ]);
    }

    #[Route('/progress', name: 'statistics_progress')]
    public function progress(): Response
    {
        return $this->render('statistics/progress.html.twig');
    }

    #[Route('/revision-strategy', name: 'statistics_revision_strategy')]
    public function revisionStrategy(): Response
    {
        $strategy = $this->revisionStrategyService->getRevisionStrategy();

        return $this->render('statistics/revision_strategy.html.twig', [
            'strategy' => $strategy,
        ]);
    }

    // API endpoints for charts

    #[Route('/api/category-success-rate', name: 'api_category_success_rate')]
    public function apiCategorySuccessRate(): JsonResponse
    {
        $data = $this->statisticsService->getCategorySuccessRateChartData();

        return $this->json($data);
    }

    #[Route('/api/subcategory-success-rate/{id?}', name: 'api_subcategory_success_rate')]
    public function apiSubcategorySuccessRate(?Category $category = null): JsonResponse
    {
        $data = $this->statisticsService->getSubcategorySuccessRateChartData($category);

        return $this->json($data);
    }

    #[Route('/api/progress-over-time', name: 'api_progress_over_time')]
    public function apiProgressOverTime(): JsonResponse
    {
        $data = $this->statisticsService->getProgressChartData(30);

        return $this->json($data);
    }

    #[Route('/api/score-progression', name: 'api_score_progression')]
    public function apiScoreProgression(): JsonResponse
    {
        $data = $this->statisticsService->getScoreProgressionChartData(30);

        return $this->json($data);
    }

    #[Route('/api/mistake-distribution', name: 'api_mistake_distribution')]
    public function apiMistakeDistribution(): JsonResponse
    {
        $data = $this->statisticsService->getMistakeDistributionChartData();

        return $this->json($data);
    }

    #[Route('/api/attempts-by-category', name: 'api_attempts_by_category')]
    public function apiAttemptsByCategory(): JsonResponse
    {
        $data = $this->statisticsService->getAttemptsByCategory();

        return $this->json($data);
    }
}

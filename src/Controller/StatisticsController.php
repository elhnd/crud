<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Question;
use App\Repository\CategoryRepository;
use App\Repository\UserAnswerRepository;
use App\Service\ClaudeAIService;
use App\Service\RevisionStrategyService;
use App\Service\StatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/statistics')]
class StatisticsController extends AbstractController
{
    public function __construct(
        private readonly StatisticsService $statisticsService,
        private readonly CategoryRepository $categoryRepository,
        private readonly RevisionStrategyService $revisionStrategyService,
        private readonly UserAnswerRepository $userAnswerRepository,
        private readonly ClaudeAIService $claudeAIService,
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

    #[Route('/question/{id}', name: 'statistics_question_detail')]
    public function questionDetail(Question $question, Request $request): Response
    {
        $questionStats = $this->userAnswerRepository->getQuestionStats($question);
        $aiExplanation = $this->claudeAIService->getExplanation($question);

        // Get backUrl from query parameter, or use referer, or default to weak areas
        $backUrl = $request->query->get('backUrl');
        if (!$backUrl) {
            $referer = $request->headers->get('referer');
            $backUrl = $referer ?: $this->generateUrl('statistics_weak_areas');
        }

        return $this->render('statistics/question_detail.html.twig', [
            'question' => $question,
            'stats' => $questionStats,
            'aiExplanation' => $aiExplanation,
            'backUrl' => $backUrl,
        ]);
    }

    #[Route('/question/{id}/generate-explanation', name: 'statistics_generate_explanation', methods: ['POST'])]
    public function generateExplanation(Question $question, Request $request): Response
    {
        $locale = $request->request->get('locale', 'en');
        $forceRegenerate = $request->request->getBoolean('regenerate', false);

        // Get backUrl to maintain it through redirects
        $backUrl = $request->request->get('backUrl');
        $redirectParams = ['id' => $question->getId()];
        if ($backUrl) {
            $redirectParams['backUrl'] = $backUrl;
        }

        // Check API connectivity first
        if (!$this->claudeAIService->isApiReachable()) {
            $this->addFlash('error', 'Cannot connect to Claude API. Please check your network connection or try again later.');
            return $this->redirectToRoute('statistics_question_detail', $redirectParams);
        }

        $explanation = $this->claudeAIService->generateExplanation($question, $locale, $forceRegenerate);

        if ($explanation === null) {
            $error = $this->claudeAIService->getLastError() ?? 'Unknown error';
            $this->addFlash('error', 'Failed to generate explanation: ' . $error);
        } else {
            $this->addFlash('success', 'Explanation generated successfully!');
        }

        return $this->redirectToRoute('statistics_question_detail', $redirectParams);
    }

    #[Route('/api/question/{id}/generate-explanation', name: 'api_generate_explanation', methods: ['POST'])]
    public function apiGenerateExplanation(Question $question, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $locale = $data['locale'] ?? 'en';
        $forceRegenerate = $data['regenerate'] ?? false;

        $startTime = microtime(true);

        // Check API connectivity first
        if (!$this->claudeAIService->isApiReachable()) {
            return $this->json([
                'success' => false,
                'error' => 'Cannot connect to Claude API. Please check your network connection or try again later.',
                'duration' => round(microtime(true) - $startTime, 2)
            ], 503);
        }

        $explanation = $this->claudeAIService->generateExplanation($question, $locale, $forceRegenerate);
        $duration = round(microtime(true) - $startTime, 2);

        if ($explanation === null) {
            $error = $this->claudeAIService->getLastError() ?? 'Unknown error';
            return $this->json([
                'success' => false,
                'error' => $error,
                'duration' => $duration
            ], 500);
        }

        return $this->json([
            'success' => true,
            'explanation' => [
                'content' => $explanation->getContent(),
                'locale' => $explanation->getLocale(),
                'generatedAt' => $explanation->getGeneratedAt()->format('M d, Y'),
                'modelUsed' => $explanation->getModelUsed(),
                'tokensUsed' => $explanation->getTokensUsed()
            ],
            'duration' => $duration
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

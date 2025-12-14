<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\QuizSessionRepository;
use App\Repository\SubcategoryRepository;
use App\Repository\UserAnswerRepository;
use App\Repository\UserRepository;

class StatisticsService
{
    public function __construct(
        private readonly QuizSessionRepository $quizSessionRepository,
        private readonly UserAnswerRepository $userAnswerRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly SubcategoryRepository $subcategoryRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * Get comprehensive dashboard statistics
     * @return array<string, mixed>
     */
    public function getDashboardStats(): array
    {
        $user = $this->userRepository->findOrCreateDefaultUser();

        $overall = $this->quizSessionRepository->getOverallStats($user);
        $byCategory = $this->quizSessionRepository->getStatsByCategory($user);
        // Use answer-based weak/strong areas for more accurate statistics
        $weakAreas = $this->userAnswerRepository->getWeakAreasByAnswers($user);
        $strongAreas = $this->userAnswerRepository->getStrongAreasByAnswers($user);
        $recentSessions = $this->quizSessionRepository->findCompletedByUser($user, 10);

        return [
            'overall' => $overall,
            'byCategory' => $byCategory,
            'weakAreas' => $weakAreas,
            'strongAreas' => $strongAreas,
            'recentSessions' => $recentSessions,
        ];
    }

    /**
     * Get data for bar chart (success rate by category)
     * @return array{labels: array<string>, data: array<float>, colors: array<string>}
     */
    public function getCategorySuccessRateChartData(): array
    {
        $user = $this->userRepository->findOrCreateDefaultUser();
        $stats = $this->userAnswerRepository->getSuccessRateByCategory($user);

        $labels = [];
        $data = [];
        $colors = [];

        $colorPalette = [
            '#4F46E5', // indigo
            '#10B981', // emerald
            '#F59E0B', // amber
            '#EF4444', // red
            '#8B5CF6', // violet
            '#06B6D4', // cyan
        ];

        foreach ($stats as $index => $stat) {
            $labels[] = $stat['categoryName'];
            $data[] = round($stat['successRate'], 1);
            $colors[] = $colorPalette[$index % count($colorPalette)];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
        ];
    }

    /**
     * Get data for subcategory bar chart
     * @return array{labels: array<string>, data: array<float>, colors: array<string>}
     */
    public function getSubcategorySuccessRateChartData(?Category $category = null): array
    {
        $user = $this->userRepository->findOrCreateDefaultUser();
        $stats = $this->userAnswerRepository->getSuccessRateBySubcategory($user, $category);

        $labels = [];
        $data = [];
        $colors = [];

        $colorPalette = [
            '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4',
            '#EC4899', '#84CC16', '#14B8A6', '#F97316', '#6366F1', '#22C55E',
        ];

        foreach ($stats as $index => $stat) {
            $labels[] = $stat['subcategoryName'];
            $data[] = round($stat['successRate'], 1);
            $colors[] = $colorPalette[$index % count($colorPalette)];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
        ];
    }

    /**
     * Get data for line chart (progress over time)
     * @return array{labels: array<string>, data: array<float>}
     */
    public function getProgressChartData(int $days = 30): array
    {
        $user = $this->userRepository->findOrCreateDefaultUser();
        $dailyScores = $this->quizSessionRepository->getDailyScores($user, $days);

        $labels = [];
        $data = [];

        foreach ($dailyScores as $day) {
            $labels[] = $day['date'];
            $data[] = round($day['averageScore'], 1);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get data for score progression (individual sessions)
     * @return array{labels: array<string>, data: array<float>, categories: array<string>}
     */
    public function getScoreProgressionChartData(int $days = 30): array
    {
        $user = $this->userRepository->findOrCreateDefaultUser();
        $progression = $this->quizSessionRepository->getScoreProgression($user, $days);

        $labels = [];
        $data = [];
        $categories = [];

        foreach ($progression as $item) {
            $labels[] = $item['date'];
            $data[] = round($item['score'], 1);
            $categories[] = $item['categoryName'] ?? 'Random';
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'categories' => $categories,
        ];
    }

    /**
     * Get data for pie chart (mistake distribution)
     * @return array{labels: array<string>, data: array<int>, colors: array<string>}
     */
    public function getMistakeDistributionChartData(): array
    {
        $user = $this->userRepository->findOrCreateDefaultUser();
        $distribution = $this->userAnswerRepository->getMistakeDistribution($user);

        $labels = [];
        $data = [];
        $colors = [];

        $colorPalette = [
            '#EF4444', '#F59E0B', '#FBBF24', '#A3E635', '#22C55E',
            '#14B8A6', '#06B6D4', '#3B82F6', '#6366F1', '#8B5CF6',
        ];

        foreach ($distribution as $index => $item) {
            $labels[] = $item['categoryName'];
            $data[] = $item['mistakeCount'];
            $colors[] = $colorPalette[$index % count($colorPalette)];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
        ];
    }

    /**
     * Get detailed category statistics
     * @return array<string, mixed>
     */
    public function getCategoryDetailedStats(Category $category): array
    {
        $user = $this->userRepository->findOrCreateDefaultUser();

        $subcategoryStats = $this->quizSessionRepository->getStatsBySubcategory($user, $category);
        $successRates = $this->userAnswerRepository->getSuccessRateBySubcategory($user, $category);

        return [
            'category' => $category,
            'subcategoryStats' => $subcategoryStats,
            'successRates' => $successRates,
        ];
    }

    /**
     * Get most failed questions
     * @return array<array{questionId: int, questionText: string, wrongCount: int, totalAttempts: int, failureRate: float}>
     */
    public function getMostFailedQuestions(int $limit = 10): array
    {
        $user = $this->userRepository->findOrCreateDefaultUser();

        return $this->userAnswerRepository->getMostFailedQuestions($user, $limit);
    }

    /**
     * Get attempts count by category
     * @return array<array{categoryName: string, attemptCount: int}>
     */
    public function getAttemptsByCategory(): array
    {
        $user = $this->userRepository->findOrCreateDefaultUser();
        $stats = $this->quizSessionRepository->getStatsByCategory($user);

        return array_map(function ($stat) {
            return [
                'categoryName' => $stat['categoryName'],
                'attemptCount' => $stat['totalSessions'],
            ];
        }, $stats);
    }
}

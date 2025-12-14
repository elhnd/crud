<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\QuizSessionRepository;
use App\Repository\UserRepository;
use App\Service\StatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        CategoryRepository $categoryRepository,
        QuizSessionRepository $quizSessionRepository,
        UserRepository $userRepository,
        StatisticsService $statisticsService,
    ): Response {
        $categories = $categoryRepository->findAllWithSubcategories();
        $user = $userRepository->findOrCreateDefaultUser();
        $recentSessions = $quizSessionRepository->findCompletedByUser($user, 5);
        $overallStats = $quizSessionRepository->getOverallStats($user);
        $inProgressSession = $quizSessionRepository->findInProgressByUser($user);

        return $this->render('home/index.html.twig', [
            'categories' => $categories,
            'recentSessions' => $recentSessions,
            'overallStats' => $overallStats,
            'inProgressSession' => $inProgressSession,
        ]);
    }
}

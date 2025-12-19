<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizSessionRepository;
use App\Repository\SubcategoryRepository;
use App\Repository\UserAnswerRepository;
use App\Repository\UserRepository;

/**
 * Service to generate personalized revision strategy for certification preparation
 */
class RevisionStrategyService
{
    // Certification exam topics weights (based on official Symfony certification)
    private const CERTIFICATION_TOPICS = [
        'PHP' => ['weight' => 10, 'subtopics' => ['OOP', 'PHP Basics', 'Interfaces & Traits', 'PSR', 'Namespaces']],
        'HTTP' => ['weight' => 10, 'subtopics' => ['HTTP', 'HttpFoundation', 'HttpKernel']],
        'Symfony Architecture' => ['weight' => 15, 'subtopics' => ['Architecture', 'Controllers', 'Routing', 'Configuration']],
        'Controllers' => ['weight' => 10, 'subtopics' => ['Controllers', 'Routing']],
        'Routing' => ['weight' => 10, 'subtopics' => ['Routing']],
        'Templating with Twig' => ['weight' => 10, 'subtopics' => ['Twig']],
        'Forms' => ['weight' => 10, 'subtopics' => ['Forms', 'Validation']],
        'Data Validation' => ['weight' => 5, 'subtopics' => ['Validation']],
        'Dependency Injection' => ['weight' => 10, 'subtopics' => ['Dependency Injection', 'Services']],
        'Security' => ['weight' => 10, 'subtopics' => ['Security', 'PasswordHasher']],
        'HTTP Caching' => ['weight' => 5, 'subtopics' => ['Cache', 'HTTP']],
        'Console' => ['weight' => 5, 'subtopics' => ['Console']],
        'Automated Tests' => ['weight' => 5, 'subtopics' => ['Testing']],
        'Miscellaneous' => ['weight' => 5, 'subtopics' => ['Event Dispatcher', 'Serializer', 'Messenger', 'Mailer', 'Translation']],
    ];

    public function __construct(
        private readonly QuizSessionRepository $quizSessionRepository,
        private readonly UserAnswerRepository $userAnswerRepository,
        private readonly QuestionRepository $questionRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly SubcategoryRepository $subcategoryRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * Get comprehensive revision strategy
     * @return array<string, mixed>
     */
    public function getRevisionStrategy(): array
    {
        $user = $this->userRepository->findOrCreateDefaultUser();

        $overallStats = $this->getOverallReadiness($user);
        $topicAnalysis = $this->analyzeTopicCoverage($user);
        $priorityTopics = $this->getPriorityTopics($user);
        $weeklyPlan = $this->generateWeeklyPlan($priorityTopics);
        $dailyGoals = $this->generateDailyGoals($user);
        $estimatedReadiness = $this->calculateReadinessScore($user);
        $recommendations = $this->getPersonalizedRecommendations($user);
        $practiceStats = $this->getPracticeStatistics($user);

        return [
            'overallStats' => $overallStats,
            'topicAnalysis' => $topicAnalysis,
            'priorityTopics' => $priorityTopics,
            'weeklyPlan' => $weeklyPlan,
            'dailyGoals' => $dailyGoals,
            'readinessScore' => $estimatedReadiness,
            'recommendations' => $recommendations,
            'practiceStats' => $practiceStats,
            'certificationTopics' => self::CERTIFICATION_TOPICS,
        ];
    }

    /**
     * Get overall readiness statistics
     */
    private function getOverallReadiness(User $user): array
    {
        $overall = $this->quizSessionRepository->getOverallStats($user);
        $certificationSessions = $this->quizSessionRepository->findCertificationSessionsByUser($user);

        $certificationScores = [];
        foreach ($certificationSessions as $session) {
            if ($session->getTotalQuestions() > 0) {
                $score = ($session->getCorrectAnswers() / $session->getTotalQuestions()) * 100;
                $certificationScores[] = $score;
            }
        }

        $averageCertScore = !empty($certificationScores) ? array_sum($certificationScores) / count($certificationScores) : 0;
        $bestCertScore = !empty($certificationScores) ? max($certificationScores) : 0;
        $passingThreshold = 70; // Symfony certification passing score

        return [
            'totalQuizzes' => $overall['totalQuizzes'] ?? 0,
            'totalQuestions' => $overall['totalQuestions'] ?? 0,
            'overallSuccessRate' => $overall['successRate'] ?? 0,
            'certificationAttempts' => count($certificationSessions),
            'averageCertScore' => round($averageCertScore, 1),
            'bestCertScore' => round($bestCertScore, 1),
            'passingThreshold' => $passingThreshold,
            'isReadyForCert' => $bestCertScore >= $passingThreshold,
        ];
    }

    /**
     * Analyze coverage of certification topics
     */
    private function analyzeTopicCoverage(User $user): array
    {
        $subcategoryStats = $this->userAnswerRepository->getSuccessRateBySubcategory($user);
        
        // Build a map of subcategory performance (total answers and success rate)
        $subcatPerformance = [];
        foreach ($subcategoryStats as $stat) {
            $subcatPerformance[$stat['subcategoryName']] = [
                'successRate' => $stat['successRate'],
                'total' => $stat['total'],  // Total answers (with repetitions)
                'subcategoryId' => $stat['subcategoryId'],
            ];
        }

        // Get UNIQUE questions seen per subcategory (for coverage calculation)
        $uniqueQuestionsBySubcat = $this->userAnswerRepository->getUniqueQuestionsCountBySubcategory($user);

        // Get total questions per subcategory (available in database)
        $questionCounts = $this->questionRepository->countBySubcategory();
        $subcategories = $this->subcategoryRepository->findAll();
        
        // Build maps by subcategory name
        $subcatQuestionCounts = [];
        $subcatIdByName = [];
        foreach ($subcategories as $sub) {
            $subcatQuestionCounts[$sub->getName()] = $questionCounts[$sub->getId()] ?? 0;
            $subcatIdByName[$sub->getName()] = $sub->getId();
        }

        $topicAnalysis = [];
        foreach (self::CERTIFICATION_TOPICS as $topic => $info) {
            $subtopicData = [];
            $totalWeight = 0;
            $weightedScore = 0;
            $totalUniqueAttempted = 0;
            $totalAvailable = 0;

            foreach ($info['subtopics'] as $subtopic) {
                $performance = $subcatPerformance[$subtopic] ?? null;
                $subcatId = $subcatIdByName[$subtopic] ?? null;
                
                $available = $subcatQuestionCounts[$subtopic] ?? 0;
                $totalAnswers = $performance['total'] ?? 0;  // For success rate calculation
                $uniqueSeen = $subcatId ? ($uniqueQuestionsBySubcat[$subcatId] ?? 0) : 0;  // For coverage
                $successRate = $performance['successRate'] ?? 0;

                // Coverage = unique questions seen / total available (not repetitions!)
                $coverage = $available > 0 ? min(100, round(($uniqueSeen / $available) * 100, 1)) : 0;

                $subtopicData[] = [
                    'name' => $subtopic,
                    'successRate' => round($successRate, 1),
                    'attempted' => $totalAnswers,  // Total answers for display
                    'uniqueSeen' => $uniqueSeen,   // Unique questions seen
                    'available' => $available,
                    'coverage' => $coverage,
                ];

                if ($totalAnswers > 0) {
                    $weightedScore += $successRate;
                    $totalWeight++;
                }
                $totalUniqueAttempted += $uniqueSeen;
                $totalAvailable += $available;
            }

            $averageScore = $totalWeight > 0 ? $weightedScore / $totalWeight : 0;
            // Topic coverage = unique questions seen across all subtopics / total available
            $coverage = $totalAvailable > 0 ? min(100, ($totalUniqueAttempted / $totalAvailable) * 100) : 0;

            $status = 'not-started';
            if ($totalUniqueAttempted > 0) {
                if ($averageScore >= 80) {
                    $status = 'strong';
                } elseif ($averageScore >= 60) {
                    $status = 'moderate';
                } else {
                    $status = 'weak';
                }
            }

            $topicAnalysis[$topic] = [
                'weight' => $info['weight'],
                'subtopics' => $subtopicData,
                'averageScore' => round($averageScore, 1),
                'coverage' => round($coverage, 1),
                'status' => $status,
                'priority' => $this->calculatePriority($info['weight'], $averageScore, $coverage),
            ];
        }

        // Sort by priority (highest first)
        uasort($topicAnalysis, fn($a, $b) => $b['priority'] <=> $a['priority']);

        return $topicAnalysis;
    }

    /**
     * Calculate priority score for a topic
     */
    private function calculatePriority(int $weight, float $score, float $coverage): float
    {
        // Priority formula: high weight + low score + low coverage = high priority
        $scoreDeficit = 100 - $score;
        $coverageDeficit = 100 - $coverage;
        
        return ($weight * 2) + ($scoreDeficit * 0.5) + ($coverageDeficit * 0.3);
    }

    /**
     * Get priority topics that need attention
     */
    private function getPriorityTopics(User $user): array
    {
        $weakAreas = $this->userAnswerRepository->getWeakAreasByAnswers($user, 70.0);
        $mostFailed = $this->userAnswerRepository->getMostFailedQuestions($user, 10);

        // Get subcategories not yet attempted
        $attemptedSubcats = [];
        $stats = $this->userAnswerRepository->getSuccessRateBySubcategory($user);
        foreach ($stats as $stat) {
            $attemptedSubcats[$stat['subcategoryId']] = true;
        }

        $allSubcategories = $this->subcategoryRepository->findAll();
        $notAttempted = [];
        foreach ($allSubcategories as $subcat) {
            if (!isset($attemptedSubcats[$subcat->getId()])) {
                $notAttempted[] = [
                    'id' => $subcat->getId(),
                    'name' => $subcat->getName(),
                    'category' => $subcat->getCategory()->getName(),
                    'documentationUrl' => $subcat->getDocumentationUrl(),
                ];
            }
        }

        return [
            'weakAreas' => $weakAreas,
            'mostFailedQuestions' => $mostFailed,
            'notAttemptedSubcategories' => $notAttempted,
        ];
    }

    /**
     * Generate a weekly revision plan
     */
    private function generateWeeklyPlan(array $priorityTopics): array
    {
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $plan = [];

        // Mix weak areas with not attempted topics
        $focusTopics = array_merge(
            array_slice($priorityTopics['weakAreas'], 0, 7),
            array_slice($priorityTopics['notAttemptedSubcategories'], 0, 7)
        );

        foreach ($days as $index => $day) {
            $dayPlan = [
                'day' => $day,
                'focus' => null,
                'activities' => [],
            ];

            if ($index < 5) { // Weekdays
                if (isset($focusTopics[$index])) {
                    $topic = $focusTopics[$index];
                    $dayPlan['focus'] = $topic['name'] ?? $topic['subcategoryName'] ?? 'Révision générale';
                    $dayPlan['activities'] = [
                        ['type' => 'study', 'duration' => 30, 'description' => 'Lecture de la documentation'],
                        ['type' => 'practice', 'duration' => 30, 'description' => 'Quiz ciblé (15-20 questions)'],
                        ['type' => 'review', 'duration' => 15, 'description' => 'Révision des erreurs'],
                    ];
                } else {
                    $dayPlan['focus'] = 'Révision générale';
                    $dayPlan['activities'] = [
                        ['type' => 'practice', 'duration' => 45, 'description' => 'Quiz mixte (25 questions)'],
                        ['type' => 'review', 'duration' => 15, 'description' => 'Révision des concepts difficiles'],
                    ];
                }
            } elseif ($index === 5) { // Saturday - Exam simulation
                $dayPlan['focus'] = 'Simulation d\'examen';
                $dayPlan['activities'] = [
                    ['type' => 'exam', 'duration' => 90, 'description' => 'Examen blanc certification (75 questions)'],
                    ['type' => 'review', 'duration' => 30, 'description' => 'Analyse détaillée des résultats'],
                ];
            } else { // Sunday - Light revision
                $dayPlan['focus'] = 'Révision légère';
                $dayPlan['activities'] = [
                    ['type' => 'review', 'duration' => 30, 'description' => 'Relecture des notes de la semaine'],
                    ['type' => 'rest', 'duration' => 0, 'description' => 'Repos et détente'],
                ];
            }

            $plan[] = $dayPlan;
        }

        return $plan;
    }

    /**
     * Generate daily goals based on current performance
     */
    private function generateDailyGoals(User $user): array
    {
        $overall = $this->quizSessionRepository->getOverallStats($user);
        $successRate = $overall['successRate'] ?? 0;

        $goals = [];

        // Questions per day goal
        if ($successRate < 50) {
            $goals[] = [
                'icon' => 'book',
                'title' => 'Étude approfondie',
                'target' => '1-2 heures de lecture de documentation par jour',
                'reason' => 'Renforcer les bases avant la pratique intensive',
            ];
            $goals[] = [
                'icon' => 'tasks',
                'title' => 'Pratique modérée',
                'target' => '20-30 questions par jour',
                'reason' => 'Consolider les connaissances progressivement',
            ];
        } elseif ($successRate < 70) {
            $goals[] = [
                'icon' => 'tasks',
                'title' => 'Pratique régulière',
                'target' => '40-50 questions par jour',
                'reason' => 'Améliorer le taux de réussite vers 70%+',
            ];
            $goals[] = [
                'icon' => 'exclamation-triangle',
                'title' => 'Focus sur les erreurs',
                'target' => 'Revoir chaque question ratée',
                'reason' => 'Identifier et corriger les lacunes',
            ];
        } else {
            $goals[] = [
                'icon' => 'tasks',
                'title' => 'Maintien du niveau',
                'target' => '30-40 questions par jour',
                'reason' => 'Maintenir la préparation sans surmenage',
            ];
            $goals[] = [
                'icon' => 'certificate',
                'title' => 'Simulations régulières',
                'target' => '2-3 examens blancs par semaine',
                'reason' => 'Se préparer aux conditions réelles',
            ];
        }

        $goals[] = [
            'icon' => 'clock',
            'title' => 'Gestion du temps',
            'target' => 'Moins de 1 minute par question',
            'reason' => 'L\'examen dure 90 min pour 75 questions',
        ];

        return $goals;
    }

    /**
     * Calculate overall readiness score (0-100)
     */
    private function calculateReadinessScore(User $user): array
    {
        $overall = $this->quizSessionRepository->getOverallStats($user);
        $successRate = $overall['successRate'] ?? 0;

        // Coverage score: based on UNIQUE questions seen vs total questions in database
        // This measures how many different questions you've practiced, not repetitions
        $totalQuestionsInDb = $this->questionRepository->count([]);
        $seenQuestionIds = $this->userAnswerRepository->getSeenQuestionIds($user);
        $uniqueQuestionsSeen = count($seenQuestionIds);
        $coverageScore = $totalQuestionsInDb > 0 ? min(100, ($uniqueQuestionsSeen / $totalQuestionsInDb) * 100) : 0;

        // Performance score: uses ALL answers (including repetitions) for accurate success rate
        $performanceScore = $successRate;

        // Consistency score: measures how evenly practice is spread across all categories
        // Based on the coefficient of variation of attempts per category
        $categoryStats = $this->userAnswerRepository->getSuccessRateByCategory($user);
        $allCategories = $this->categoryRepository->findAll();
        $totalCategories = count($allCategories);
        
        if ($totalCategories > 0 && count($categoryStats) > 0) {
            // Get attempts per category (including 0 for unpracticed categories)
            $attemptsByCategory = array_fill(0, $totalCategories, 0);
            $categoryIndex = 0;
            $practisedCategoriesMap = [];
            
            foreach ($categoryStats as $stat) {
                $practisedCategoriesMap[$stat['categoryId']] = $stat['total'];
            }
            
            foreach ($allCategories as $category) {
                $attemptsByCategory[$categoryIndex] = $practisedCategoriesMap[$category->getId()] ?? 0;
                $categoryIndex++;
            }
            
            // Calculate how many categories have been practiced
            $categoriesPracticed = count(array_filter($attemptsByCategory, fn($v) => $v > 0));
            $categoryPracticeRatio = $categoriesPracticed / $totalCategories;
            
            // Calculate the balance of practice among practiced categories
            $practicedAttempts = array_filter($attemptsByCategory, fn($v) => $v > 0);
            if (count($practicedAttempts) > 1) {
                $mean = array_sum($practicedAttempts) / count($practicedAttempts);
                $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $practicedAttempts)) / count($practicedAttempts);
                $stdDev = sqrt($variance);
                $coefficientOfVariation = $mean > 0 ? ($stdDev / $mean) : 0;
                // Lower CV = more balanced, CV of 0 = perfect balance, CV of 1+ = very unbalanced
                $balanceScore = max(0, 100 - ($coefficientOfVariation * 50));
            } else {
                $balanceScore = count($practicedAttempts) > 0 ? 50 : 0;
            }
            
            // Combine: practice across categories (50%) + balance within practiced (50%)
            $consistencyScore = ($categoryPracticeRatio * 100 * 0.5) + ($balanceScore * 0.5);
        } else {
            $consistencyScore = 0;
        }

        // Weighted final score
        $finalScore = ($coverageScore * 0.2) + ($performanceScore * 0.6) + ($consistencyScore * 0.2);

        $status = 'not-ready';
        $message = 'Continuez à pratiquer régulièrement.';

        if ($finalScore >= 85) {
            $status = 'ready';
            $message = 'Vous êtes prêt pour la certification !';
        } elseif ($finalScore >= 70) {
            $status = 'almost-ready';
            $message = 'Encore quelques efforts et vous serez prêt.';
        } elseif ($finalScore >= 50) {
            $status = 'in-progress';
            $message = 'Bonne progression, continuez ainsi.';
        }

        return [
            'score' => round($finalScore, 1),
            'coverageScore' => round($coverageScore, 1),
            'performanceScore' => round($performanceScore, 1),
            'consistencyScore' => round($consistencyScore, 1),
            'status' => $status,
            'message' => $message,
        ];
    }

    /**
     * Get personalized recommendations
     */
    private function getPersonalizedRecommendations(User $user): array
    {
        $recommendations = [];
        $overall = $this->quizSessionRepository->getOverallStats($user);
        $weakAreas = $this->userAnswerRepository->getWeakAreasByAnswers($user, 70.0);
        $strongAreas = $this->userAnswerRepository->getStrongAreasByAnswers($user, 80.0);

        // Recommendation based on total practice
        $totalQuestions = $overall['totalQuestions'] ?? 0;
        if ($totalQuestions < 100) {
            $recommendations[] = [
                'type' => 'practice',
                'priority' => 'high',
                'title' => 'Plus de pratique nécessaire',
                'description' => 'Vous n\'avez répondu qu\'à ' . $totalQuestions . ' questions. Visez au moins 500 questions pour une bonne préparation.',
                'action' => 'Commencez un quiz maintenant',
                'actionUrl' => '/quiz/start',
            ];
        }

        // Recommendation for weak areas
        if (count($weakAreas) > 3) {
            $weakNames = array_slice(array_column($weakAreas, 'name'), 0, 3);
            $recommendations[] = [
                'type' => 'focus',
                'priority' => 'high',
                'title' => 'Zones à améliorer',
                'description' => 'Concentrez-vous sur : ' . implode(', ', $weakNames),
                'action' => 'Voir les zones faibles',
                'actionUrl' => '/statistics/weak-areas',
            ];
        }

        // Recommendation for certification mode
        $certSessions = $this->quizSessionRepository->findCertificationSessionsByUser($user);
        if (count($certSessions) < 3) {
            $recommendations[] = [
                'type' => 'exam',
                'priority' => 'medium',
                'title' => 'Essayez le mode certification',
                'description' => 'Les simulations d\'examen sont essentielles pour se préparer aux conditions réelles.',
                'action' => 'Lancer un examen blanc',
                'actionUrl' => '/quiz/start?mode=certification',
            ];
        }

        // Recommendation based on success rate
        $successRate = $overall['successRate'] ?? 0;
        if ($successRate > 0 && $successRate < 70) {
            $recommendations[] = [
                'type' => 'study',
                'priority' => 'high',
                'title' => 'Renforcez vos connaissances',
                'description' => 'Votre taux de réussite est de ' . round($successRate, 1) . '%. Étudiez la documentation Symfony pour améliorer ce score.',
                'action' => 'Documentation Symfony',
                'actionUrl' => 'https://symfony.com/doc/current/index.html',
            ];
        }

        // Positive reinforcement
        if (count($strongAreas) > 5) {
            $recommendations[] = [
                'type' => 'success',
                'priority' => 'low',
                'title' => 'Excellent travail !',
                'description' => 'Vous maîtrisez ' . count($strongAreas) . ' sujets avec plus de 80% de réussite. Continuez ainsi !',
                'action' => null,
                'actionUrl' => null,
            ];
        }

        // Sort by priority
        usort($recommendations, function($a, $b) {
            $priorities = ['high' => 0, 'medium' => 1, 'low' => 2];
            return $priorities[$a['priority']] <=> $priorities[$b['priority']];
        });

        return $recommendations;
    }

    /**
     * Get practice statistics for display
     */
    private function getPracticeStatistics(User $user): array
    {
        $overall = $this->quizSessionRepository->getOverallStats($user);
        $totalQuestionsInDb = $this->questionRepository->count([]);
        $certificationQuestions = $this->questionRepository->count(['isCertification' => true]);
        
        $seenQuestionIds = $this->userAnswerRepository->getSeenQuestionIds($user);
        $seenCount = count($seenQuestionIds);
        $questionsAttempted = $overall['totalQuestions'] ?? 0;

        return [
            'totalQuestionsInDb' => $totalQuestionsInDb,
            'certificationQuestionsInDb' => $certificationQuestions,
            'questionsAttempted' => $questionsAttempted,
            'uniqueQuestionsAttempted' => $seenCount,
            // Coverage based on UNIQUE questions seen vs total available (not repetitions)
            'coveragePercentage' => $totalQuestionsInDb > 0 ? round(($seenCount / $totalQuestionsInDb) * 100, 1) : 0,
            'averageTimePerQuestion' => 45, // TODO: Calculate from actual data
        ];
    }
}

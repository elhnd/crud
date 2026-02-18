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
    // Certification exam topics based on official Symfony certification
    // Source: https://certification.symfony.com/exams/symfony.html
    // 75 questions, 14 official topics, 90 minutes — total weights = 100%
    // All 68 DB subcategories are mapped to ensure complete Topic Analysis coverage
    private const CERTIFICATION_TOPICS = [
        'PHP' => ['weight' => 7, 'inExam' => true, 'subtopics' => [
            'OOP', 'PHP Basics', 'Interfaces & Traits', 'Functions', 'Closures',
            'Exceptions', 'Typing & Strict Types', 'Arrays', 'Data Format & Types',
            'Strings', 'JSON', 'XML', 'DOM', 'Namespaces', 'SPL', 'I/O',
        ]],
        'HTTP' => ['weight' => 5, 'inExam' => true, 'subtopics' => [
            'HTTP', 'HttpClient', 'Session',
        ]],
        'Symfony Architecture' => ['weight' => 8, 'inExam' => true, 'subtopics' => [
            'HttpFoundation', 'Architecture', 'Configuration', 'Event Dispatcher', 'PSR',
        ]],
        'Controllers' => ['weight' => 7, 'inExam' => true, 'subtopics' => [
            'Controllers', 'HttpKernel', 'FrameworkBundle',
        ]],
        'Routing' => ['weight' => 7, 'inExam' => true, 'subtopics' => ['Routing']],
        'Templating with Twig' => ['weight' => 7, 'inExam' => true, 'subtopics' => ['Twig', 'Assets']],
        'Forms' => ['weight' => 8, 'inExam' => true, 'subtopics' => ['Forms', 'OptionsResolver']],
        'Data Validation' => ['weight' => 6, 'inExam' => true, 'subtopics' => ['Validation', 'Validator']],
        'Dependency Injection' => ['weight' => 8, 'inExam' => true, 'subtopics' => ['Dependency Injection', 'Services']],
        'Security' => ['weight' => 8, 'inExam' => true, 'subtopics' => ['Security', 'PasswordHasher']],
        'Messenger' => ['weight' => 5, 'inExam' => true, 'subtopics' => ['Messenger']],
        'Console' => ['weight' => 5, 'inExam' => true, 'subtopics' => ['Console']],
        'Automated Tests' => ['weight' => 7, 'inExam' => true, 'subtopics' => [
            'Testing', 'BrowserKit', 'DomCrawler', 'CssSelector',
        ]],
        'Miscellaneous' => ['weight' => 12, 'inExam' => true, 'subtopics' => [
            'Cache', 'HttpCache', 'Clock', 'Filesystem', 'Finder',
            'Mailer', 'Mime', 'Process', 'PropertyAccess', 'Runtime',
            'Serializer', 'Dotenv', 'ErrorHandler', 'Expression Language',
            'VarDumper', 'Translation', 'Miscellaneous',
        ]],
        'Not in Official Exam' => ['weight' => 0, 'inExam' => false, 'subtopics' => [
            'Doctrine', 'AssetMapper', 'Lock', 'Intl', 'Inflector',
            'VarExporter', 'Yaml',
        ]],
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
                'inExam' => $info['inExam'],
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
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
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
                    $dayPlan['focus'] = $topic['name'] ?? $topic['subcategoryName'] ?? 'General revision';
                    $dayPlan['activities'] = [
                        ['type' => 'study', 'duration' => 30, 'description' => 'Reading documentation'],
                        ['type' => 'practice', 'duration' => 30, 'description' => 'Targeted quiz (15-20 questions)'],
                        ['type' => 'review', 'duration' => 15, 'description' => 'Review errors'],
                    ];
                } else {
                    $dayPlan['focus'] = 'General revision';
                    $dayPlan['activities'] = [
                        ['type' => 'practice', 'duration' => 45, 'description' => 'Mixed quiz (25 questions)'],
                        ['type' => 'review', 'duration' => 15, 'description' => 'Review difficult concepts'],
                    ];
                }
            } elseif ($index === 5) { // Saturday - Exam simulation
                $dayPlan['focus'] = 'Exam simulation';
                $dayPlan['activities'] = [
                    ['type' => 'exam', 'duration' => 90, 'description' => 'Mock certification exam (75 questions)'],
                    ['type' => 'review', 'duration' => 30, 'description' => 'Detailed results analysis'],
                ];
            } else { // Sunday - Light revision
                $dayPlan['focus'] = 'Light revision';
                $dayPlan['activities'] = [
                    ['type' => 'review', 'duration' => 30, 'description' => 'Re-reading weekly notes'],
                    ['type' => 'rest', 'duration' => 0, 'description' => 'Rest and relaxation'],
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
                'title' => 'In-depth study',
                'target' => '1-2 hours of documentation reading per day',
                'reason' => 'Strengthen fundamentals before intensive practice',
            ];
            $goals[] = [
                'icon' => 'tasks',
                'title' => 'Moderate practice',
                'target' => '20-30 questions per day',
                'reason' => 'Gradually consolidate knowledge',
            ];
        } elseif ($successRate < 70) {
            $goals[] = [
                'icon' => 'tasks',
                'title' => 'Regular practice',
                'target' => '40-50 questions per day',
                'reason' => 'Improve success rate to 70%+',
            ];
            $goals[] = [
                'icon' => 'exclamation-triangle',
                'title' => 'Focus on errors',
                'target' => 'Review each failed question',
                'reason' => 'Identify and correct gaps',
            ];
        } else {
            $goals[] = [
                'icon' => 'tasks',
                'title' => 'Maintain level',
                'target' => '30-40 questions per day',
                'reason' => 'Maintain preparation without burnout',
            ];
            $goals[] = [
                'icon' => 'certificate',
                'title' => 'Regular simulations',
                'target' => '2-3 mock exams per week',
                'reason' => 'Prepare for real exam conditions',
            ];
        }

        $goals[] = [
            'icon' => 'clock',
            'title' => 'Time management',
            'target' => 'Less than 1 minute per question',
            'reason' => 'The exam is 90 min for 75 questions',
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
        $message = 'Keep practicing regularly.';

        if ($finalScore >= 85) {
            $status = 'ready';
            $message = 'You are ready for the certification!';
        } elseif ($finalScore >= 70) {
            $status = 'almost-ready';
            $message = 'A few more efforts and you will be ready.';
        } elseif ($finalScore >= 50) {
            $status = 'in-progress';
            $message = 'Good progress, keep it up.';
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
                'title' => 'More practice needed',
                'description' => 'You have only answered ' . $totalQuestions . ' questions. Aim for at least 500 questions for good preparation.',
                'action' => 'Start a quiz now',
                'actionRoute' => 'quiz_start_form',
                'actionRouteParams' => [],
                'actionUrl' => null,
            ];
        }

        // Recommendation for weak areas
        if (count($weakAreas) > 3) {
            $weakNames = array_slice(array_column($weakAreas, 'name'), 0, 3);
            $recommendations[] = [
                'type' => 'focus',
                'priority' => 'high',
                'title' => 'Areas to improve',
                'description' => 'Focus on: ' . implode(', ', $weakNames),
                'action' => 'View weak areas',
                'actionRoute' => 'statistics_weak_areas',
                'actionRouteParams' => [],
                'actionUrl' => null,
            ];
        }

        // Recommendation for certification mode
        $certSessions = $this->quizSessionRepository->findCertificationSessionsByUser($user);
        if (count($certSessions) < 3) {
            $recommendations[] = [
                'type' => 'exam',
                'priority' => 'medium',
                'title' => 'Try certification mode',
                'description' => 'Exam simulations are essential to prepare for real conditions.',
                'action' => 'Start a mock exam',
                'actionRoute' => 'quiz_certification_exam',
                'actionRouteParams' => [],
                'actionUrl' => null,
            ];
        }

        // Recommendation based on success rate
        $successRate = $overall['successRate'] ?? 0;
        if ($successRate > 0 && $successRate < 70) {
            $recommendations[] = [
                'type' => 'study',
                'priority' => 'high',
                'title' => 'Strengthen your knowledge',
                'description' => 'Your success rate is ' . round($successRate, 1) . '%. Study the Symfony documentation to improve this score.',
                'action' => 'Symfony Documentation',
                'actionRoute' => null,
                'actionRouteParams' => [],
                'actionUrl' => 'https://symfony.com/doc/current/index.html',
            ];
        }

        // Positive reinforcement
        if (count($strongAreas) > 5) {
            $recommendations[] = [
                'type' => 'success',
                'priority' => 'low',
                'title' => 'Excellent work!',
                'description' => 'You have mastered ' . count($strongAreas) . ' topics with over 80% success rate. Keep it up!',
                'action' => null,
                'actionRoute' => null,
                'actionRouteParams' => [],
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

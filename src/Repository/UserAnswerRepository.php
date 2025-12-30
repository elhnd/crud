<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Subcategory;
use App\Entity\User;
use App\Entity\UserAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserAnswer>
 */
class UserAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAnswer::class);
    }

    /**
     * Get failure statistics by question
     * @return array<array{questionId: int, questionText: string, wrongCount: int, totalAttempts: int, failureRate: float}>
     */
    public function getMostFailedQuestions(User $user, int $limit = 10): array
    {
        $results = $this->createQueryBuilder('ua')
            ->leftJoin('ua.quizSession', 'qs')
            ->leftJoin('ua.question', 'q')
            ->select(
                'IDENTITY(ua.question) as questionId',
                'q.text as questionText',
                'SUM(CASE WHEN ua.isCorrect = false THEN 1 ELSE 0 END) as wrongCount',
                'COUNT(ua.id) as totalAttempts'
            )
            ->where('qs.user = :user')
            ->setParameter('user', $user)
            ->groupBy('ua.question', 'q.text')
            ->having('wrongCount > 0')
            ->orderBy('wrongCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(function ($row) {
            $totalAttempts = (int)$row['totalAttempts'];
            $wrongCount = (int)$row['wrongCount'];
            
            return [
                'questionId' => (int)$row['questionId'],
                'questionText' => $row['questionText'],
                'wrongCount' => $wrongCount,
                'totalAttempts' => $totalAttempts,
                'failureRate' => $totalAttempts > 0 ? ($wrongCount / $totalAttempts) * 100 : 0,
            ];
        }, $results);
    }

    /**
     * Get success rate by category
     * @return array<array{categoryId: int, categoryName: string, correct: int, total: int, successRate: float}>
     */
    public function getSuccessRateByCategory(User $user): array
    {
        $results = $this->createQueryBuilder('ua')
            ->leftJoin('ua.quizSession', 'qs')
            ->leftJoin('ua.question', 'q')
            ->leftJoin('q.category', 'c')
            ->select(
                'IDENTITY(q.category) as categoryId',
                'c.name as categoryName',
                'SUM(CASE WHEN ua.isCorrect = true THEN 1 ELSE 0 END) as correct',
                'COUNT(ua.id) as total'
            )
            ->where('qs.user = :user')
            ->setParameter('user', $user)
            ->groupBy('q.category', 'c.name')
            ->getQuery()
            ->getResult();

        return array_map(function ($row) {
            $total = (int)$row['total'];
            $correct = (int)$row['correct'];
            
            return [
                'categoryId' => (int)$row['categoryId'],
                'categoryName' => $row['categoryName'],
                'correct' => $correct,
                'total' => $total,
                'successRate' => $total > 0 ? ($correct / $total) * 100 : 0,
            ];
        }, $results);
    }

    /**
     * Get success rate by subcategory
     * @return array<array{subcategoryId: int, subcategoryName: string, categoryName: string, correct: int, total: int, successRate: float}>
     */
    public function getSuccessRateBySubcategory(User $user, ?Category $category = null): array
    {
        $qb = $this->createQueryBuilder('ua')
            ->leftJoin('ua.quizSession', 'qs')
            ->leftJoin('ua.question', 'q')
            ->leftJoin('q.subcategory', 's')
            ->leftJoin('q.category', 'c')
            ->select(
                'IDENTITY(q.subcategory) as subcategoryId',
                's.name as subcategoryName',
                'c.name as categoryName',
                'SUM(CASE WHEN ua.isCorrect = true THEN 1 ELSE 0 END) as correct',
                'COUNT(ua.id) as total'
            )
            ->where('qs.user = :user')
            ->setParameter('user', $user)
            ->groupBy('q.subcategory', 's.name', 'c.name');

        if ($category) {
            $qb->andWhere('q.category = :category')
                ->setParameter('category', $category);
        }

        $results = $qb->getQuery()->getResult();

        return array_map(function ($row) {
            $total = (int)$row['total'];
            $correct = (int)$row['correct'];
            
            return [
                'subcategoryId' => (int)$row['subcategoryId'],
                'subcategoryName' => $row['subcategoryName'],
                'categoryName' => $row['categoryName'],
                'correct' => $correct,
                'total' => $total,
                'successRate' => $total > 0 ? ($correct / $total) * 100 : 0,
            ];
        }, $results);
    }

    /**
     * Get weak areas based on individual answers (only subcategories with low success rate)
     * @return array<array{name: string, type: string, averageScore: float, totalAttempts: int, documentationUrl: ?string}>
     */
    public function getWeakAreasByAnswers(User $user, float $threshold = 70.0): array
    {
        // Get only weak subcategories based on individual answers
        $results = $this->createQueryBuilder('ua')
            ->innerJoin('ua.quizSession', 'qs')
            ->innerJoin('ua.question', 'q')
            ->leftJoin('q.subcategory', 's')
            ->innerJoin('q.category', 'c')
            ->select(
                'IDENTITY(q.subcategory) as subcategoryId',
                'c.name as categoryName',
                's.name as subcategoryName',
                's.documentationUrl as documentationUrl',
                "'subcategory' as type",
                '(SUM(CASE WHEN ua.isCorrect = true THEN 1 ELSE 0 END) * 100.0 / COUNT(ua.id)) as averageScore',
                'COUNT(ua.id) as totalAttempts'
            )
            ->where('qs.user = :user')
            ->andWhere('q.subcategory IS NOT NULL')
            ->setParameter('user', $user)
            ->groupBy('q.subcategory', 's.name', 'c.name', 's.documentationUrl')
            ->having('averageScore < :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('averageScore', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(function ($row) {
            return [
                'subcategoryId' => (int)$row['subcategoryId'],
                'name' => $row['categoryName'] . ' - ' . $row['subcategoryName'],
                'type' => $row['type'],
                'averageScore' => (float)$row['averageScore'],
                'totalAttempts' => (int)$row['totalAttempts'],
                'documentationUrl' => $row['documentationUrl'],
            ];
        }, $results);
    }

    /**
     * Get strong areas based on individual answers (categories/subcategories with high success rate)
     * @return array<array{name: string, type: string, averageScore: float, totalAttempts: int}>
     */
    public function getStrongAreasByAnswers(User $user, float $threshold = 80.0): array
    {
        // Get strong categories based on individual answers
        $categoryResults = $this->createQueryBuilder('ua')
            ->leftJoin('ua.quizSession', 'qs')
            ->leftJoin('ua.question', 'q')
            ->leftJoin('q.category', 'c')
            ->select(
                'c.name as name',
                "'category' as type",
                'SUM(CASE WHEN ua.isCorrect = true THEN 1 ELSE 0 END) * 100.0 / COUNT(ua.id) as averageScore',
                'COUNT(ua.id) as totalAttempts'
            )
            ->where('qs.user = :user')
            ->setParameter('user', $user)
            ->groupBy('q.category', 'c.name')
            ->having('SUM(CASE WHEN ua.isCorrect = true THEN 1 ELSE 0 END) * 100.0 / COUNT(ua.id) >= ' . $threshold)
            ->orderBy('averageScore', 'DESC')
            ->getQuery()
            ->getResult();

        // Map category results to proper format
        $categoryResults = array_map(function ($row) {
            return [
                'name' => $row['name'],
                'type' => 'category',
                'averageScore' => (float)$row['averageScore'],
                'totalAttempts' => (int)$row['totalAttempts'],
            ];
        }, $categoryResults);

        // Get strong subcategories based on individual answers
        $subcategoryResults = $this->createQueryBuilder('ua')
            ->leftJoin('ua.quizSession', 'qs')
            ->leftJoin('ua.question', 'q')
            ->leftJoin('q.subcategory', 's')
            ->leftJoin('q.category', 'c')
            ->select(
                'c.name as categoryName',
                's.name as subcategoryName',
                "'subcategory' as type",
                'SUM(CASE WHEN ua.isCorrect = true THEN 1 ELSE 0 END) * 100.0 / COUNT(ua.id) as averageScore',
                'COUNT(ua.id) as totalAttempts'
            )
            ->where('qs.user = :user')
            ->andWhere('q.subcategory IS NOT NULL')
            ->setParameter('user', $user)
            ->groupBy('q.subcategory', 's.name', 'c.name')
            ->having('SUM(CASE WHEN ua.isCorrect = true THEN 1 ELSE 0 END) * 100.0 / COUNT(ua.id) >= ' . $threshold)
            ->orderBy('averageScore', 'DESC')
            ->getQuery()
            ->getResult();

        $subcategoryResults = array_map(function ($row) {
            return [
                'name' => $row['categoryName'] . ' - ' . $row['subcategoryName'],
                'type' => 'subcategory',
                'averageScore' => (float)$row['averageScore'],
                'totalAttempts' => (int)$row['totalAttempts'],
            ];
        }, $subcategoryResults);

        $results = array_merge($categoryResults, $subcategoryResults);
        usort($results, fn($a, $b) => (float)$b['averageScore'] <=> (float)$a['averageScore']);

        return $results;
    }

    /**
     * Get mistake distribution for pie chart
     * @return array<array{categoryName: string, mistakeCount: int}>
     */
    public function getMistakeDistribution(User $user): array
    {
        $results = $this->createQueryBuilder('ua')
            ->leftJoin('ua.quizSession', 'qs')
            ->leftJoin('ua.question', 'q')
            ->leftJoin('q.category', 'c')
            ->select(
                'c.name as categoryName',
                'COUNT(ua.id) as mistakeCount'
            )
            ->where('qs.user = :user')
            ->andWhere('ua.isCorrect = false')
            ->setParameter('user', $user)
            ->groupBy('q.category', 'c.name')
            ->orderBy('mistakeCount', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(function ($row) {
            return [
                'categoryName' => $row['categoryName'],
                'mistakeCount' => (int)$row['mistakeCount'],
            ];
        }, $results);
    }

    /**
     * Get all question IDs that the user has already seen/answered
     * @return array<int>
     */
    public function getSeenQuestionIds(User $user): array
    {
        $results = $this->createQueryBuilder('ua')
            ->leftJoin('ua.quizSession', 'qs')
            ->select('DISTINCT IDENTITY(ua.question) as questionId')
            ->where('qs.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return array_map(fn($row) => (int)$row['questionId'], $results);
    }

    /**
     * Get count of unique questions seen per subcategory
     * @return array<int, int> [subcategoryId => uniqueQuestionCount]
     */
    public function getUniqueQuestionsCountBySubcategory(User $user): array
    {
        $results = $this->createQueryBuilder('ua')
            ->leftJoin('ua.quizSession', 'qs')
            ->leftJoin('ua.question', 'q')
            ->select(
                'IDENTITY(q.subcategory) as subcategoryId',
                'COUNT(DISTINCT ua.question) as uniqueCount'
            )
            ->where('qs.user = :user')
            ->andWhere('q.subcategory IS NOT NULL')
            ->setParameter('user', $user)
            ->groupBy('q.subcategory')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $row) {
            $counts[(int)$row['subcategoryId']] = (int)$row['uniqueCount'];
        }

        return $counts;
    }

    /**
     * Get question IDs with their failure rate for the user
     * Returns questions ordered by failure rate (highest first)
     * @return array<int, float> [questionId => failureRate]
     */
    public function getQuestionFailureRates(User $user): array
    {
        $results = $this->createQueryBuilder('ua')
            ->leftJoin('ua.quizSession', 'qs')
            ->select(
                'IDENTITY(ua.question) as questionId',
                'SUM(CASE WHEN ua.isCorrect = false THEN 1 ELSE 0 END) as wrongCount',
                'COUNT(ua.id) as totalAttempts'
            )
            ->where('qs.user = :user')
            ->setParameter('user', $user)
            ->groupBy('ua.question')
            ->getQuery()
            ->getResult();

        $failureRates = [];
        foreach ($results as $row) {
            $totalAttempts = (int)$row['totalAttempts'];
            $wrongCount = (int)$row['wrongCount'];
            $questionId = (int)$row['questionId'];
            
            if ($totalAttempts > 0) {
                $failureRates[$questionId] = ($wrongCount / $totalAttempts) * 100;
            }
        }

        // Sort by failure rate descending
        arsort($failureRates);

        return $failureRates;
    }

    /**
     * Get question IDs that the user frequently gets wrong (above threshold)
     * @return array<int>
     */
    public function getFrequentlyWrongQuestionIds(User $user, float $failureRateThreshold = 50.0): array
    {
        $failureRates = $this->getQuestionFailureRates($user);
        
        return array_keys(array_filter(
            $failureRates,
            fn($rate) => $rate >= $failureRateThreshold
        ));
    }

    /**
     * Get statistics for a specific question
     * @return array{totalAttempts: int, correctCount: int, wrongCount: int, successRate: float, failureRate: float, lastAttemptAt: ?\DateTimeImmutable}
     */
    public function getQuestionStats(Question $question): array
    {
        $result = $this->createQueryBuilder('ua')
            ->select(
                'COUNT(ua.id) as totalAttempts',
                'SUM(CASE WHEN ua.isCorrect = true THEN 1 ELSE 0 END) as correctCount',
                'SUM(CASE WHEN ua.isCorrect = false THEN 1 ELSE 0 END) as wrongCount',
                'MAX(ua.answeredAt) as lastAttemptAt'
            )
            ->where('ua.question = :question')
            ->setParameter('question', $question)
            ->getQuery()
            ->getSingleResult();

        $totalAttempts = (int)($result['totalAttempts'] ?? 0);
        $correctCount = (int)($result['correctCount'] ?? 0);
        $wrongCount = (int)($result['wrongCount'] ?? 0);

        return [
            'totalAttempts' => $totalAttempts,
            'correctCount' => $correctCount,
            'wrongCount' => $wrongCount,
            'successRate' => $totalAttempts > 0 ? ($correctCount / $totalAttempts) * 100 : 0,
            'failureRate' => $totalAttempts > 0 ? ($wrongCount / $totalAttempts) * 100 : 0,
            'lastAttemptAt' => $result['lastAttemptAt'] ? new \DateTimeImmutable($result['lastAttemptAt']) : null,
        ];
    }
}

<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\QuizSession;
use App\Entity\Subcategory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuizSession>
 */
class QuizSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizSession::class);
    }

    /**
     * Find completed sessions for a user
     * @return QuizSession[]
     */
    public function findCompletedByUser(User $user, int $limit = 20): array
    {
        return $this->createQueryBuilder('qs')
            ->where('qs.user = :user')
            ->andWhere('qs.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', QuizSession::STATUS_COMPLETED)
            ->orderBy('qs.completedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find in-progress session for a user
     */
    public function findInProgressByUser(User $user): ?QuizSession
    {
        return $this->createQueryBuilder('qs')
            ->where('qs.user = :user')
            ->andWhere('qs.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', QuizSession::STATUS_IN_PROGRESS)
            ->orderBy('qs.startedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get overall statistics for a user
     * @return array{totalSessions: int, totalQuestions: int, correctAnswers: int, averageScore: float}
     */
    public function getOverallStats(User $user): array
    {
        $result = $this->createQueryBuilder('qs')
            ->select(
                'COUNT(qs.id) as totalSessions',
                'SUM(qs.totalQuestions) as totalQuestions',
                'SUM(qs.correctAnswers) as correctAnswers',
                'AVG(qs.score) as averageScore'
            )
            ->where('qs.user = :user')
            ->andWhere('qs.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', QuizSession::STATUS_COMPLETED)
            ->getQuery()
            ->getSingleResult();

        return [
            'totalSessions' => (int)($result['totalSessions'] ?? 0),
            'totalQuestions' => (int)($result['totalQuestions'] ?? 0),
            'correctAnswers' => (int)($result['correctAnswers'] ?? 0),
            'averageScore' => (float)($result['averageScore'] ?? 0),
        ];
    }

    /**
     * Get statistics by category for a user
     * @return array<int, array{categoryId: int, categoryName: string, totalSessions: int, totalQuestions: int, correctAnswers: int, averageScore: float}>
     */
    public function getStatsByCategory(User $user): array
    {
        $results = $this->createQueryBuilder('qs')
            ->leftJoin('qs.category', 'c')
            ->select(
                'IDENTITY(qs.category) as categoryId',
                'c.name as categoryName',
                'COUNT(qs.id) as totalSessions',
                'SUM(qs.totalQuestions) as totalQuestions',
                'SUM(qs.correctAnswers) as correctAnswers',
                'AVG(qs.score) as averageScore'
            )
            ->where('qs.user = :user')
            ->andWhere('qs.status = :status')
            ->andWhere('qs.category IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('status', QuizSession::STATUS_COMPLETED)
            ->groupBy('qs.category', 'c.name')
            ->getQuery()
            ->getResult();

        return array_map(function ($row) {
            return [
                'categoryId' => (int)$row['categoryId'],
                'categoryName' => $row['categoryName'],
                'totalSessions' => (int)$row['totalSessions'],
                'totalQuestions' => (int)$row['totalQuestions'],
                'correctAnswers' => (int)$row['correctAnswers'],
                'averageScore' => (float)$row['averageScore'],
            ];
        }, $results);
    }

    /**
     * Get statistics by subcategory for a user
     * @return array<int, array{subcategoryId: int, subcategoryName: string, categoryName: string, totalSessions: int, totalQuestions: int, correctAnswers: int, averageScore: float}>
     */
    public function getStatsBySubcategory(User $user, ?Category $category = null): array
    {
        $qb = $this->createQueryBuilder('qs')
            ->leftJoin('qs.subcategory', 's')
            ->leftJoin('s.category', 'c')
            ->select(
                'IDENTITY(qs.subcategory) as subcategoryId',
                's.name as subcategoryName',
                'c.name as categoryName',
                'COUNT(qs.id) as totalSessions',
                'SUM(qs.totalQuestions) as totalQuestions',
                'SUM(qs.correctAnswers) as correctAnswers',
                'AVG(qs.score) as averageScore'
            )
            ->where('qs.user = :user')
            ->andWhere('qs.status = :status')
            ->andWhere('qs.subcategory IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('status', QuizSession::STATUS_COMPLETED)
            ->groupBy('qs.subcategory', 's.name', 'c.name');

        if ($category) {
            $qb->andWhere('s.category = :category')
                ->setParameter('category', $category);
        }

        $results = $qb->getQuery()->getResult();

        return array_map(function ($row) {
            return [
                'subcategoryId' => (int)$row['subcategoryId'],
                'subcategoryName' => $row['subcategoryName'],
                'categoryName' => $row['categoryName'],
                'totalSessions' => (int)$row['totalSessions'],
                'totalQuestions' => (int)$row['totalQuestions'],
                'correctAnswers' => (int)$row['correctAnswers'],
                'averageScore' => (float)$row['averageScore'],
            ];
        }, $results);
    }

    /**
     * Get score progression over time for a user
     * @return array<array{date: string, score: float, categoryName: ?string}>
     */
    public function getScoreProgression(User $user, int $days = 30): array
    {
        $since = new \DateTimeImmutable("-{$days} days");

        $results = $this->createQueryBuilder('qs')
            ->leftJoin('qs.category', 'c')
            ->select(
                'qs.completedAt as date',
                'qs.score as score',
                'c.name as categoryName'
            )
            ->where('qs.user = :user')
            ->andWhere('qs.status = :status')
            ->andWhere('qs.completedAt >= :since')
            ->setParameter('user', $user)
            ->setParameter('status', QuizSession::STATUS_COMPLETED)
            ->setParameter('since', $since)
            ->orderBy('qs.completedAt', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(function ($row) {
            return [
                'date' => $row['date']->format('Y-m-d H:i'),
                'score' => (float)$row['score'],
                'categoryName' => $row['categoryName'],
            ];
        }, $results);
    }

    /**
     * Get daily average scores for a user
     * @return array<array{date: string, averageScore: float, sessionsCount: int}>
     */
    public function getDailyScores(User $user, int $days = 30): array
    {
        $since = new \DateTimeImmutable("-{$days} days");

        // For SQLite, we need to format the date differently
        $results = $this->createQueryBuilder('qs')
            ->select(
                "SUBSTRING(qs.completedAt, 1, 10) as day",
                'AVG(qs.score) as averageScore',
                'COUNT(qs.id) as sessionsCount'
            )
            ->where('qs.user = :user')
            ->andWhere('qs.status = :status')
            ->andWhere('qs.completedAt >= :since')
            ->setParameter('user', $user)
            ->setParameter('status', QuizSession::STATUS_COMPLETED)
            ->setParameter('since', $since)
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(function ($row) {
            return [
                'date' => $row['day'],
                'averageScore' => (float)$row['averageScore'],
                'sessionsCount' => (int)$row['sessionsCount'],
            ];
        }, $results);
    }

    /**
     * Get weak areas (categories/subcategories with low success rate)
     * @return array<array{name: string, type: string, averageScore: float, totalAttempts: int}>
     */
    public function getWeakAreas(User $user, float $threshold = 70.0): array
    {
        $categoryResults = $this->createQueryBuilder('qs')
            ->leftJoin('qs.category', 'c')
            ->select(
                'c.name as name',
                "'category' as type",
                'AVG(qs.score) as averageScore',
                'COUNT(qs.id) as totalAttempts'
            )
            ->where('qs.user = :user')
            ->andWhere('qs.status = :status')
            ->andWhere('qs.category IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('status', QuizSession::STATUS_COMPLETED)
            ->groupBy('qs.category', 'c.name')
            ->having('AVG(qs.score) < :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('averageScore', 'ASC')
            ->getQuery()
            ->getResult();

        $subcategoryResults = $this->createQueryBuilder('qs')
            ->leftJoin('qs.subcategory', 's')
            ->leftJoin('s.category', 'c')
            ->select(
                "CONCAT(c.name, ' - ', s.name) as name",
                "'subcategory' as type",
                'AVG(qs.score) as averageScore',
                'COUNT(qs.id) as totalAttempts'
            )
            ->where('qs.user = :user')
            ->andWhere('qs.status = :status')
            ->andWhere('qs.subcategory IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('status', QuizSession::STATUS_COMPLETED)
            ->groupBy('qs.subcategory', 's.name', 'c.name')
            ->having('AVG(qs.score) < :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('averageScore', 'ASC')
            ->getQuery()
            ->getResult();

        $results = array_merge($categoryResults, $subcategoryResults);
        usort($results, fn($a, $b) => $a['averageScore'] <=> $b['averageScore']);

        return array_map(function ($row) {
            return [
                'name' => $row['name'],
                'type' => $row['type'],
                'averageScore' => (float)$row['averageScore'],
                'totalAttempts' => (int)$row['totalAttempts'],
            ];
        }, $results);
    }

    /**
     * Get strong areas (categories/subcategories with high success rate)
     * @return array<array{name: string, type: string, averageScore: float, totalAttempts: int}>
     */
    public function getStrongAreas(User $user, float $threshold = 80.0): array
    {
        $categoryResults = $this->createQueryBuilder('qs')
            ->leftJoin('qs.category', 'c')
            ->select(
                'c.name as name',
                "'category' as type",
                'AVG(qs.score) as averageScore',
                'COUNT(qs.id) as totalAttempts'
            )
            ->where('qs.user = :user')
            ->andWhere('qs.status = :status')
            ->andWhere('qs.category IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('status', QuizSession::STATUS_COMPLETED)
            ->groupBy('qs.category', 'c.name')
            ->having('AVG(qs.score) >= :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('averageScore', 'DESC')
            ->getQuery()
            ->getResult();

        $subcategoryResults = $this->createQueryBuilder('qs')
            ->leftJoin('qs.subcategory', 's')
            ->leftJoin('s.category', 'c')
            ->select(
                "CONCAT(c.name, ' - ', s.name) as name",
                "'subcategory' as type",
                'AVG(qs.score) as averageScore',
                'COUNT(qs.id) as totalAttempts'
            )
            ->where('qs.user = :user')
            ->andWhere('qs.status = :status')
            ->andWhere('qs.subcategory IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('status', QuizSession::STATUS_COMPLETED)
            ->groupBy('qs.subcategory', 's.name', 'c.name')
            ->having('AVG(qs.score) >= :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('averageScore', 'DESC')
            ->getQuery()
            ->getResult();

        $results = array_merge($categoryResults, $subcategoryResults);
        usort($results, fn($a, $b) => $b['averageScore'] <=> $a['averageScore']);

        return array_map(function ($row) {
            return [
                'name' => $row['name'],
                'type' => $row['type'],
                'averageScore' => (float)$row['averageScore'],
                'totalAttempts' => (int)$row['totalAttempts'],
            ];
        }, $results);
    }
}

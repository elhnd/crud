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

    /**
     * Find quiz sessions with pagination and filters (for admin)
     * @return array{sessions: QuizSession[], total: int}
     */
    public function findPaginatedWithFilters(
        int $offset,
        int $limit,
        ?string $status = null,
        ?int $categoryId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): array {
        $qb = $this->createQueryBuilder('qs')
            ->leftJoin('qs.user', 'u')
            ->leftJoin('qs.category', 'c')
            ->leftJoin('qs.subcategory', 's')
            ->orderBy('qs.startedAt', 'DESC');

        if ($status) {
            $qb->andWhere('qs.status = :status')
                ->setParameter('status', $status);
        }

        if ($categoryId) {
            $qb->andWhere('qs.category = :category')
                ->setParameter('category', $categoryId);
        }

        if ($dateFrom) {
            $qb->andWhere('qs.startedAt >= :dateFrom')
                ->setParameter('dateFrom', new \DateTimeImmutable($dateFrom));
        }

        if ($dateTo) {
            $qb->andWhere('qs.startedAt <= :dateTo')
                ->setParameter('dateTo', new \DateTimeImmutable($dateTo . ' 23:59:59'));
        }

        // Count total
        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(qs.id)')->getQuery()->getSingleScalarResult();

        // Get paginated results
        $sessions = $qb->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'sessions' => $sessions,
            'total' => $total,
        ];
    }

    /**
     * Get admin statistics for quiz sessions
     * @return array{total: int, byStatus: array<string, int>, averageScore: float, sessionsToday: int, sessionsThisWeek: int}
     */
    public function getAdminStatistics(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // Total sessions by status
        $statusStats = $conn->fetchAllAssociative(
            'SELECT status, COUNT(*) as count FROM quiz_session GROUP BY status'
        );

        $byStatus = [];
        foreach ($statusStats as $stat) {
            $byStatus[$stat['status']] = (int) $stat['count'];
        }

        // Average score
        $avgScore = $conn->fetchOne(
            'SELECT AVG(score) FROM quiz_session WHERE status = ?',
            [QuizSession::STATUS_COMPLETED]
        );

        // Sessions today
        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $sessionsToday = $conn->fetchOne(
            'SELECT COUNT(*) FROM quiz_session WHERE DATE(started_at) = ?',
            [$today]
        );

        // Sessions this week
        $weekStart = (new \DateTimeImmutable('monday this week'))->format('Y-m-d');
        $sessionsThisWeek = $conn->fetchOne(
            'SELECT COUNT(*) FROM quiz_session WHERE started_at >= ?',
            [$weekStart]
        );

        return [
            'total' => array_sum($byStatus),
            'byStatus' => $byStatus,
            'averageScore' => $avgScore ? round((float) $avgScore, 1) : 0,
            'sessionsToday' => (int) $sessionsToday,
            'sessionsThisWeek' => (int) $sessionsThisWeek,
        ];
    }

    /**
     * Bulk delete quiz sessions by IDs
     * @param int[] $ids
     * @return int Number of deleted sessions
     */
    public function bulkDelete(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        $deleted = $this->getEntityManager()->createQueryBuilder()
            ->delete(QuizSession::class, 'qs')
            ->where('qs.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();

        return $deleted;
    }

    /**
     * Cleanup abandoned sessions older than specified days
     * @return int Number of deleted sessions
     */
    public function cleanupAbandoned(int $daysOld = 7): int
    {
        $threshold = new \DateTimeImmutable("-{$daysOld} days");

        $deleted = $this->getEntityManager()->createQueryBuilder()
            ->delete(QuizSession::class, 'qs')
            ->where('qs.status = :status')
            ->andWhere('qs.startedAt < :threshold')
            ->setParameter('status', QuizSession::STATUS_IN_PROGRESS)
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->execute();

        return $deleted;
    }
}

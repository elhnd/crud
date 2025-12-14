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
     * @return array<array{name: string, type: string, averageScore: float, totalAttempts: int}>
     */
    public function getWeakAreasByAnswers(User $user, float $threshold = 70.0): array
    {
        // Get only weak subcategories based on individual answers
        $results = $this->createQueryBuilder('ua')
            ->leftJoin('ua.quizSession', 'qs')
            ->leftJoin('ua.question', 'q')
            ->leftJoin('q.subcategory', 's')
            ->leftJoin('q.category', 'c')
            ->select(
                "CONCAT(c.name, ' - ', s.name) as name",
                "'subcategory' as type",
                '(SUM(CASE WHEN ua.isCorrect = true THEN 1 ELSE 0 END) * 100.0 / COUNT(ua.id)) as averageScore',
                'COUNT(ua.id) as totalAttempts'
            )
            ->where('qs.user = :user')
            ->andWhere('q.subcategory IS NOT NULL')
            ->setParameter('user', $user)
            ->groupBy('q.subcategory', 's.name', 'c.name')
            ->having('(SUM(CASE WHEN ua.isCorrect = true THEN 1 ELSE 0 END) * 100.0 / COUNT(ua.id)) < :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('averageScore', 'ASC')
            ->getQuery()
            ->getResult();

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
            ->having('SUM(CASE WHEN ua.isCorrect = true THEN 1 ELSE 0 END) * 100.0 / COUNT(ua.id) >= :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('averageScore', 'DESC')
            ->getQuery()
            ->getResult();

        // Get strong subcategories based on individual answers
        $subcategoryResults = $this->createQueryBuilder('ua')
            ->leftJoin('ua.quizSession', 'qs')
            ->leftJoin('ua.question', 'q')
            ->leftJoin('q.subcategory', 's')
            ->leftJoin('q.category', 'c')
            ->select(
                "CONCAT(c.name, ' - ', s.name) as name",
                "'subcategory' as type",
                'SUM(CASE WHEN ua.isCorrect = true THEN 1 ELSE 0 END) * 100.0 / COUNT(ua.id) as averageScore',
                'COUNT(ua.id) as totalAttempts'
            )
            ->where('qs.user = :user')
            ->andWhere('q.subcategory IS NOT NULL')
            ->setParameter('user', $user)
            ->groupBy('q.subcategory', 's.name', 'c.name')
            ->having('SUM(CASE WHEN ua.isCorrect = true THEN 1 ELSE 0 END) * 100.0 / COUNT(ua.id) >= :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('averageScore', 'DESC')
            ->getQuery()
            ->getResult();

        $results = array_merge($categoryResults, $subcategoryResults);
        usort($results, fn($a, $b) => (float)$b['averageScore'] <=> (float)$a['averageScore']);

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
}

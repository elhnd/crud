<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Subcategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Question>
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * Get random questions for a quiz
     * If $limit is 0 or null, returns ALL questions matching the criteria
     * @return Question[]
     */
    public function findRandomQuestions(
        int $limit = 10,
        ?Category $category = null,
        ?Subcategory $subcategory = null
    ): array {
        // First, get all matching question IDs
        $qb = $this->createQueryBuilder('q')
            ->select('q.id');

        if ($subcategory) {
            $qb->where('q.subcategory = :subcategory')
                ->setParameter('subcategory', $subcategory);
        } elseif ($category) {
            $qb->where('q.category = :category')
                ->setParameter('category', $category);
        }

        $ids = array_column($qb->getQuery()->getArrayResult(), 'id');
        
        // Shuffle in PHP
        shuffle($ids);
        
        // If limit is 0 or less, take all questions; otherwise take the limit
        $selectedIds = ($limit > 0) ? array_slice($ids, 0, $limit) : $ids;
        
        if (empty($selectedIds)) {
            return [];
        }

        // Fetch the full questions with answers
        return $this->createQueryBuilder('q')
            ->leftJoin('q.answers', 'a')
            ->addSelect('a')
            ->where('q.id IN (:ids)')
            ->setParameter('ids', $selectedIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get questions by IDs maintaining order
     * @param int[] $ids
     * @return Question[]
     */
    public function findByIdsOrdered(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $questions = $this->createQueryBuilder('q')
            ->leftJoin('q.answers', 'a')
            ->addSelect('a')
            ->where('q.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        // Reorder by input IDs
        $indexed = [];
        foreach ($questions as $question) {
            $indexed[$question->getId()] = $question;
        }

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($indexed[$id])) {
                $ordered[] = $indexed[$id];
            }
        }

        return $ordered;
    }

    /**
     * Count questions by category
     * @return array<int, int> [categoryId => count]
     */
    public function countByCategory(): array
    {
        $results = $this->createQueryBuilder('q')
            ->select('IDENTITY(q.category) as categoryId', 'COUNT(q.id) as cnt')
            ->groupBy('q.category')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $row) {
            $counts[(int)$row['categoryId']] = (int)$row['cnt'];
        }

        return $counts;
    }

    /**
     * Count questions by subcategory
     * @return array<int, int> [subcategoryId => count]
     */
    public function countBySubcategory(): array
    {
        $results = $this->createQueryBuilder('q')
            ->select('IDENTITY(q.subcategory) as subcategoryId', 'COUNT(q.id) as cnt')
            ->groupBy('q.subcategory')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $row) {
            $counts[(int)$row['subcategoryId']] = (int)$row['cnt'];
        }

        return $counts;
    }

    /**
     * Find questions with full details
     * @return Question[]
     */
    public function findWithDetails(?Category $category = null, ?Subcategory $subcategory = null): array
    {
        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.category', 'c')
            ->leftJoin('q.subcategory', 's')
            ->leftJoin('q.answers', 'a')
            ->addSelect('c', 's', 'a')
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->addOrderBy('q.id', 'ASC');

        if ($subcategory) {
            $qb->where('q.subcategory = :subcategory')
                ->setParameter('subcategory', $subcategory);
        } elseif ($category) {
            $qb->where('q.category = :category')
                ->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get random questions from multiple categories
     * If $limit is 0 or less, returns ALL questions matching the criteria
     * @param int[] $categoryIds
     * @return Question[]
     */
    public function findRandomQuestionsMultiCategory(int $limit, array $categoryIds): array
    {
        if (empty($categoryIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('q')
            ->select('q.id')
            ->where('q.category IN (:categoryIds)')
            ->setParameter('categoryIds', $categoryIds);

        $ids = array_column($qb->getQuery()->getArrayResult(), 'id');
        
        shuffle($ids);
        $selectedIds = ($limit > 0) ? array_slice($ids, 0, $limit) : $ids;
        
        if (empty($selectedIds)) {
            return [];
        }

        return $this->createQueryBuilder('q')
            ->leftJoin('q.answers', 'a')
            ->addSelect('a')
            ->where('q.id IN (:ids)')
            ->setParameter('ids', $selectedIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get random questions from multiple subcategories
     * If $limit is 0 or less, returns ALL questions matching the criteria
     * @param int[] $subcategoryIds
     * @return Question[]
     */
    public function findRandomQuestionsMultiSubcategory(int $limit, array $subcategoryIds): array
    {
        if (empty($subcategoryIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('q')
            ->select('q.id')
            ->where('q.subcategory IN (:subcategoryIds)')
            ->setParameter('subcategoryIds', $subcategoryIds);

        $ids = array_column($qb->getQuery()->getArrayResult(), 'id');
        
        shuffle($ids);
        $selectedIds = ($limit > 0) ? array_slice($ids, 0, $limit) : $ids;
        
        if (empty($selectedIds)) {
            return [];
        }

        return $this->createQueryBuilder('q')
            ->leftJoin('q.answers', 'a')
            ->addSelect('a')
            ->where('q.id IN (:ids)')
            ->setParameter('ids', $selectedIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get random certification questions for exam mode
     * Only returns questions marked as isCertification = true
     * @return Question[]
     */
    public function findRandomCertificationQuestions(int $limit = 75): array
    {
        $qb = $this->createQueryBuilder('q')
            ->select('q.id')
            ->where('q.isCertification = :isCert')
            ->setParameter('isCert', true);

        $ids = array_column($qb->getQuery()->getArrayResult(), 'id');
        
        shuffle($ids);
        $selectedIds = ($limit > 0) ? array_slice($ids, 0, $limit) : $ids;
        
        if (empty($selectedIds)) {
            return [];
        }

        return $this->createQueryBuilder('q')
            ->leftJoin('q.answers', 'a')
            ->addSelect('a')
            ->where('q.id IN (:ids)')
            ->setParameter('ids', $selectedIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find questions with pagination and filters (for admin)
     * @return array{questions: Question[], total: int}
     */
    public function findPaginatedWithFilters(
        int $offset,
        int $limit,
        ?string $search = null,
        ?int $categoryId = null,
        ?int $subcategoryId = null,
        ?string $type = null,
        ?int $difficulty = null,
        ?string $certification = null,
    ): array {
        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.category', 'c')
            ->leftJoin('q.subcategory', 's')
            ->orderBy('q.id', 'DESC');

        if ($search) {
            $qb->andWhere('q.text LIKE :search OR q.explanation LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($categoryId) {
            $qb->andWhere('q.category = :category')
                ->setParameter('category', $categoryId);
        }

        if ($subcategoryId) {
            $qb->andWhere('q.subcategory = :subcategory')
                ->setParameter('subcategory', $subcategoryId);
        }

        if ($type) {
            $qb->andWhere('q.type = :type')
                ->setParameter('type', $type);
        }

        if ($difficulty) {
            $qb->andWhere('q.difficulty = :difficulty')
                ->setParameter('difficulty', $difficulty);
        }

        if ($certification !== null && $certification !== '') {
            $qb->andWhere('q.isCertification = :certification')
                ->setParameter('certification', $certification === '1');
        }

        // Count total
        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(q.id)')->getQuery()->getSingleScalarResult();

        // Get paginated results
        $questions = $qb->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'questions' => $questions,
            'total' => $total,
        ];
    }

    /**
     * Get question statistics for admin dashboard
     * @return array{total: int, byType: array<string, int>, byCategory: array<string, int>}
     */
    public function getAdminStatistics(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // By type
        $typeStats = $conn->fetchAllAssociative(
            'SELECT type, COUNT(*) as count FROM question GROUP BY type'
        );

        $byType = [];
        foreach ($typeStats as $stat) {
            $byType[$stat['type']] = (int) $stat['count'];
        }

        // By category
        $categoryStats = $conn->fetchAllAssociative(
            'SELECT c.name, COUNT(q.id) as count 
             FROM question q 
             LEFT JOIN category c ON q.category_id = c.id 
             GROUP BY q.category_id, c.name 
             ORDER BY count DESC 
             LIMIT 5'
        );

        $byCategory = [];
        foreach ($categoryStats as $stat) {
            $name = $stat['name'] ?? 'Uncategorized';
            $byCategory[$name] = (int) $stat['count'];
        }

        // Count certification questions
        $certification = (int) $conn->fetchOne(
            'SELECT COUNT(*) FROM question WHERE is_certification = 1'
        );

        return [
            'total' => array_sum($byType),
            'byType' => $byType,
            'byCategory' => $byCategory,
            'certification' => $certification,
        ];
    }

    /**
     * Bulk delete questions by IDs
     * @param int[] $ids
     * @return int Number of deleted questions
     */
    public function bulkDelete(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        $em = $this->getEntityManager();

        // First delete associated answers
        $em->createQueryBuilder()
            ->delete('App\Entity\Answer', 'a')
            ->where('a.question IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();

        // Then delete questions
        $deleted = $em->createQueryBuilder()
            ->delete('App\Entity\Question', 'q')
            ->where('q.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();

        return $deleted;
    }
}

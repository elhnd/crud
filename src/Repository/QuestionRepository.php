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
        // First, get all matching question IDs (only active questions)
        $qb = $this->createQueryBuilder('q')
            ->select('q.id')
            ->andWhere('q.isActive = :isActive')
            ->setParameter('isActive', true);

        if ($subcategory) {
            $qb->andWhere('q.subcategory = :subcategory')
                ->setParameter('subcategory', $subcategory);
        } elseif ($category) {
            $qb->andWhere('q.category = :category')
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
            ->andWhere('q.isActive = :isActive')
            ->setParameter('categoryIds', $categoryIds)
            ->setParameter('isActive', true);

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
            ->andWhere('q.isActive = :isActive')
            ->setParameter('subcategoryIds', $subcategoryIds)
            ->setParameter('isActive', true);

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
            ->andWhere('q.isActive = :isActive')
            ->setParameter('isCert', true)
            ->setParameter('isActive', true);

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
        ?string $active = null,
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

        if ($active !== null && $active !== '') {
            $qb->andWhere('q.isActive = :isActive')
                ->setParameter('isActive', $active === '1');
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

    /**
     * Get smart random questions prioritizing:
     * 1. Questions never seen by the user
     * 2. Questions with high failure rate
     * 3. Random questions to fill the remaining slots
     * 
     * @param int $limit Number of questions to return
     * @param array<int> $seenQuestionIds Question IDs already seen by user
     * @param array<int, float> $questionFailureRates Map of questionId => failureRate
     * @param Category|null $category Optional category filter
     * @param Subcategory|null $subcategory Optional subcategory filter
     * @return Question[]
     */
    public function findSmartRandomQuestions(
        int $limit,
        array $seenQuestionIds,
        array $questionFailureRates,
        ?Category $category = null,
        ?Subcategory $subcategory = null
    ): array {
        // Get all matching question IDs (only active questions)
        $qb = $this->createQueryBuilder('q')
            ->select('q.id')
            ->andWhere('q.isActive = :isActive')
            ->setParameter('isActive', true);

        if ($subcategory) {
            $qb->andWhere('q.subcategory = :subcategory')
                ->setParameter('subcategory', $subcategory);
        } elseif ($category) {
            $qb->andWhere('q.category = :category')
                ->setParameter('category', $category);
        }

        $allIds = array_column($qb->getQuery()->getArrayResult(), 'id');
        
        if (empty($allIds)) {
            return [];
        }

        // Prioritize selection
        $selectedIds = $this->selectSmartQuestionIds(
            $allIds,
            $seenQuestionIds,
            $questionFailureRates,
            $limit
        );
        
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
     * Get smart random questions from multiple categories
     * @param int[] $categoryIds
     * @param array<int> $seenQuestionIds
     * @param array<int, float> $questionFailureRates
     * @return Question[]
     */
    public function findSmartRandomQuestionsMultiCategory(
        int $limit,
        array $categoryIds,
        array $seenQuestionIds,
        array $questionFailureRates
    ): array {
        if (empty($categoryIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('q')
            ->select('q.id')
            ->where('q.category IN (:categoryIds)')
            ->andWhere('q.isActive = :isActive')
            ->setParameter('categoryIds', $categoryIds)
            ->setParameter('isActive', true);

        $allIds = array_column($qb->getQuery()->getArrayResult(), 'id');
        
        $selectedIds = $this->selectSmartQuestionIds(
            $allIds,
            $seenQuestionIds,
            $questionFailureRates,
            $limit
        );
        
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
     * Get smart random questions from multiple subcategories
     * @param int[] $subcategoryIds
     * @param array<int> $seenQuestionIds
     * @param array<int, float> $questionFailureRates
     * @return Question[]
     */
    public function findSmartRandomQuestionsMultiSubcategory(
        int $limit,
        array $subcategoryIds,
        array $seenQuestionIds,
        array $questionFailureRates
    ): array {
        if (empty($subcategoryIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('q')
            ->select('q.id')
            ->where('q.subcategory IN (:subcategoryIds)')
            ->andWhere('q.isActive = :isActive')
            ->setParameter('subcategoryIds', $subcategoryIds)
            ->setParameter('isActive', true);

        $allIds = array_column($qb->getQuery()->getArrayResult(), 'id');
        
        $selectedIds = $this->selectSmartQuestionIds(
            $allIds,
            $seenQuestionIds,
            $questionFailureRates,
            $limit
        );
        
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
     * Get smart random certification questions
     * @param array<int> $seenQuestionIds
     * @param array<int, float> $questionFailureRates
     * @return Question[]
     */
    public function findSmartRandomCertificationQuestions(
        int $limit,
        array $seenQuestionIds,
        array $questionFailureRates
    ): array {
        $qb = $this->createQueryBuilder('q')
            ->select('q.id')
            ->where('q.isCertification = :isCert')
            ->andWhere('q.isActive = :isActive')
            ->setParameter('isCert', true)
            ->setParameter('isActive', true);

        $allIds = array_column($qb->getQuery()->getArrayResult(), 'id');
        
        $selectedIds = $this->selectSmartQuestionIds(
            $allIds,
            $seenQuestionIds,
            $questionFailureRates,
            $limit
        );
        
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
     * Smart selection algorithm:
     * - 40% unseen questions (prioritized)
     * - 40% questions with high failure rate (>= 50%)
     * - 20% random questions to add variety
     * 
     * @param array<int> $allIds All available question IDs
     * @param array<int> $seenQuestionIds Questions already seen by user
     * @param array<int, float> $questionFailureRates Map of questionId => failureRate
     * @param int $limit Number of questions to select
     * @return array<int>
     */
    private function selectSmartQuestionIds(
        array $allIds,
        array $seenQuestionIds,
        array $questionFailureRates,
        int $limit
    ): array {
        if (empty($allIds) || $limit <= 0) {
            return [];
        }

        // Separate unseen from seen questions
        $unseenIds = array_diff($allIds, $seenQuestionIds);
        $seenInPool = array_intersect($allIds, $seenQuestionIds);

        // Get high failure rate questions (>= 50% failure rate)
        $highFailureIds = [];
        foreach ($seenInPool as $id) {
            if (isset($questionFailureRates[$id]) && $questionFailureRates[$id] >= 50.0) {
                $highFailureIds[$id] = $questionFailureRates[$id];
            }
        }
        // Sort by failure rate descending
        arsort($highFailureIds);
        $highFailureIds = array_keys($highFailureIds);

        // Calculate quotas
        $unseenQuota = (int) ceil($limit * 0.4);    // 40% unseen
        $failureQuota = (int) ceil($limit * 0.4);   // 40% high failure
        $randomQuota = $limit - $unseenQuota - $failureQuota; // Rest random

        $selectedIds = [];

        // 1. Add unseen questions (up to quota)
        shuffle($unseenIds);
        $unseenSelection = array_slice($unseenIds, 0, min($unseenQuota, count($unseenIds)));
        $selectedIds = array_merge($selectedIds, $unseenSelection);

        // 2. Add high failure rate questions (up to quota, not already selected)
        $remainingHighFailure = array_diff($highFailureIds, $selectedIds);
        $failureSelection = array_slice($remainingHighFailure, 0, min($failureQuota, count($remainingHighFailure)));
        $selectedIds = array_merge($selectedIds, $failureSelection);

        // 3. Fill remaining with random questions not yet selected
        $remaining = array_diff($allIds, $selectedIds);
        shuffle($remaining);
        $needed = $limit - count($selectedIds);
        if ($needed > 0 && !empty($remaining)) {
            $randomSelection = array_slice($remaining, 0, min($needed, count($remaining)));
            $selectedIds = array_merge($selectedIds, $randomSelection);
        }

        // If we still don't have enough (edge case), fill from any available
        if (count($selectedIds) < $limit) {
            $stillNeeded = $limit - count($selectedIds);
            $remaining = array_diff($allIds, $selectedIds);
            shuffle($remaining);
            $selectedIds = array_merge($selectedIds, array_slice($remaining, 0, $stillNeeded));
        }

        return $selectedIds;
    }
}

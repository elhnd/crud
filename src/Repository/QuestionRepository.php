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
}

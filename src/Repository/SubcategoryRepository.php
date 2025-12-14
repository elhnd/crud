<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Subcategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subcategory>
 */
class SubcategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subcategory::class);
    }

    /**
     * @return Subcategory[]
     */
    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.category = :category')
            ->setParameter('category', $category)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get subcategories with question counts
     * @return array<array{subcategory: Subcategory, questionCount: int}>
     */
    public function findWithQuestionCounts(?Category $category = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.questions', 'q')
            ->leftJoin('s.category', 'c')
            ->select('s', 'c', 'COUNT(q.id) as questionCount')
            ->groupBy('s.id', 'c.id')
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('s.name', 'ASC');

        if ($category) {
            $qb->where('s.category = :category')
                ->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @return Category[]
     */
    public function findAllWithSubcategories(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.subcategories', 's')
            ->addSelect('s')
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get categories with question counts
     * @return array<array{category: Category, questionCount: int}>
     */
    public function findWithQuestionCounts(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.questions', 'q')
            ->select('c', 'COUNT(q.id) as questionCount')
            ->groupBy('c.id')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

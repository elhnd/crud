<?php

namespace App\Repository;

use App\Entity\QuestionReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuestionReport>
 */
class QuestionReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuestionReport::class);
    }

    public function countByStatus(?string $status = null): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)');

        if ($status !== null) {
            $qb->where('r.status = :status')
                ->setParameter('status', $status);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countPending(): int
    {
        return $this->countByStatus(QuestionReport::STATUS_PENDING);
    }

    /**
     * @return array<string, int>
     */
    public function countGroupedByStatus(): array
    {
        $results = $this->createQueryBuilder('r')
            ->select('r.status, COUNT(r.id) as count')
            ->groupBy('r.status')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $result) {
            $counts[$result['status']] = (int) $result['count'];
        }

        return $counts;
    }

    public function createFilteredQueryBuilder(?string $status = null, ?string $type = null, ?string $search = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.question', 'q')
            ->orderBy('r.createdAt', 'DESC');

        if ($status !== null) {
            $qb->andWhere('r.status = :status')
                ->setParameter('status', $status);
        }

        if ($type !== null) {
            $qb->andWhere('r.type = :type')
                ->setParameter('type', $type);
        }

        if ($search !== null && $search !== '') {
            $qb->andWhere('r.message LIKE :search OR q.text LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb;
    }

    /**
     * @return QuestionReport[]
     */
    public function findByQuestionId(int $questionId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.question = :questionId')
            ->setParameter('questionId', $questionId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

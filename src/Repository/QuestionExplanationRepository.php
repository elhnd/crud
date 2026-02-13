<?php

namespace App\Repository;

use App\Entity\Question;
use App\Entity\QuestionExplanation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuestionExplanation>
 */
class QuestionExplanationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuestionExplanation::class);
    }

    public function findByQuestion(Question $question): ?QuestionExplanation
    {
        return $this->findOneBy(['question' => $question]);
    }

    public function findByQuestionAndLocale(Question $question, string $locale): ?QuestionExplanation
    {
        return $this->findOneBy(['question' => $question, 'locale' => $locale]);
    }

    /**
     * Find all explanations for a question (all locales)
     * @return QuestionExplanation[]
     */
    public function findAllByQuestion(Question $question): array
    {
        return $this->findBy(['question' => $question], ['locale' => 'ASC']);
    }

    /**
     * Get available locales for a question
     * @return string[]
     */
    public function getAvailableLocales(Question $question): array
    {
        $results = $this->createQueryBuilder('e')
            ->select('e.locale')
            ->where('e.question = :question')
            ->setParameter('question', $question)
            ->getQuery()
            ->getSingleColumnResult();

        return $results;
    }

    public function save(QuestionExplanation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(QuestionExplanation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

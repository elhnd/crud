<?php

namespace App\Service;

use App\Entity\QuizSession;
use App\Repository\QuizSessionRepository;
use Doctrine\ORM\EntityManagerInterface;

class QuizSessionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly QuizSessionRepository $quizSessionRepository,
    ) {
    }

    /**
     * @return array{sessions: QuizSession[], total: int, pages: int}
     */
    public function findPaginated(
        int $page = 1,
        int $limit = 20,
        ?string $status = null,
        ?int $categoryId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): array {
        $offset = ($page - 1) * $limit;

        $result = $this->quizSessionRepository->findPaginatedWithFilters(
            $offset,
            $limit,
            $status,
            $categoryId,
            $dateFrom,
            $dateTo
        );

        return [
            'sessions' => $result['sessions'],
            'total' => $result['total'],
            'pages' => (int) ceil($result['total'] / $limit),
        ];
    }

    /**
     * Delete a quiz session.
     */
    public function delete(QuizSession $session): void
    {
        $this->entityManager->remove($session);
        $this->entityManager->flush();
    }

    /**
     * Bulk delete quiz sessions by IDs.
     * 
     * @param int[] $ids
     * @return int Number of deleted sessions
     */
    public function bulkDelete(array $ids): int
    {
        return $this->quizSessionRepository->bulkDelete($ids);
    }

    /**
     * Cleanup abandoned sessions older than specified days.
     * 
     * @return int Number of deleted sessions
     */
    public function cleanupAbandoned(int $daysOld = 7): int
    {
        return $this->quizSessionRepository->cleanupAbandoned($daysOld);
    }

    /**
     * Get quiz session statistics.
     * 
     * @return array{total: int, byStatus: array<string, int>, averageScore: float, sessionsToday: int, sessionsThisWeek: int}
     */
    public function getStatistics(): array
    {
        return $this->quizSessionRepository->getAdminStatistics();
    }
}

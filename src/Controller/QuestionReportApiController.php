<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\QuestionReport;
use App\Repository\QuestionReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class QuestionReportApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly QuestionReportRepository $reportRepository,
    ) {
    }

    #[Route('/question/{id}/report', name: 'api_question_report', methods: ['POST'])]
    public function submitReport(Request $request, Question $question): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $type = $data['type'] ?? null;
        $message = trim($data['message'] ?? '');

        if (!$type || !in_array($type, array_values(QuestionReport::getTypes()))) {
            return $this->json([
                'success' => false,
                'error' => 'Please select a valid report type.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (empty($message)) {
            return $this->json([
                'success' => false,
                'error' => 'Please provide a description of the issue.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (mb_strlen($message) < 10) {
            return $this->json([
                'success' => false,
                'error' => 'Please provide a more detailed description (at least 10 characters).',
            ], Response::HTTP_BAD_REQUEST);
        }

        $report = new QuestionReport();
        $report->setQuestion($question);
        $report->setType($type);
        $report->setMessage($message);

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Report submitted successfully. Thank you for helping us improve!',
        ]);
    }

    #[Route('/question/{id}/reports', name: 'api_question_reports', methods: ['GET'])]
    public function getReports(Question $question): JsonResponse
    {
        $reports = $this->reportRepository->findByQuestionId($question->getId());

        $data = array_map(fn(QuestionReport $r) => [
            'id' => $r->getId(),
            'type' => $r->getType(),
            'typeLabel' => $r->getTypeLabel(),
            'message' => $r->getMessage(),
            'status' => $r->getStatus(),
            'statusLabel' => $r->getStatusLabel(),
            'adminResponse' => $r->getAdminResponse(),
            'createdAt' => $r->getCreatedAt()->format('Y-m-d H:i'),
        ], $reports);

        return $this->json([
            'success' => true,
            'reports' => $data,
            'count' => count($data),
        ]);
    }
}

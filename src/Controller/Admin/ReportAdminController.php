<?php

namespace App\Controller\Admin;

use App\Entity\QuestionReport;
use App\Repository\QuestionReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/reports')]
class ReportAdminController extends AbstractController
{
    public function __construct(
        private readonly QuestionReportRepository $reportRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'admin_report_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $status = $request->query->get('status');
        $type = $request->query->get('type');
        $search = $request->query->get('search');

        $qb = $this->reportRepository->createFilteredQueryBuilder($status, $type, $search);
        $reports = $qb->getQuery()->getResult();

        $statusCounts = $this->reportRepository->countGroupedByStatus();

        return $this->render('admin/report/index.html.twig', [
            'reports' => $reports,
            'statusCounts' => $statusCounts,
            'currentStatus' => $status,
            'currentType' => $type,
            'currentSearch' => $search,
            'reportTypes' => QuestionReport::getTypes(),
            'reportStatuses' => QuestionReport::getStatuses(),
            'totalPending' => $statusCounts[QuestionReport::STATUS_PENDING] ?? 0,
        ]);
    }

    #[Route('/{id}', name: 'admin_report_show', methods: ['GET'])]
    public function show(QuestionReport $report): Response
    {
        return $this->render('admin/report/show.html.twig', [
            'report' => $report,
            'question' => $report->getQuestion(),
            'reportStatuses' => QuestionReport::getStatuses(),
        ]);
    }

    #[Route('/{id}/respond', name: 'admin_report_respond', methods: ['POST'])]
    public function respond(Request $request, QuestionReport $report): Response
    {
        $adminResponse = trim($request->request->get('admin_response', ''));
        $newStatus = $request->request->get('status', QuestionReport::STATUS_REVIEWED);

        if (!in_array($newStatus, array_values(QuestionReport::getStatuses()))) {
            $this->addFlash('error', 'Invalid status.');
            return $this->redirectToRoute('admin_report_show', ['id' => $report->getId()]);
        }

        $report->setAdminResponse($adminResponse);
        $report->setStatus($newStatus);

        if ($newStatus === QuestionReport::STATUS_RESOLVED || $newStatus === QuestionReport::STATUS_DISMISSED) {
            $report->setResolvedAt(new \DateTimeImmutable());
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Report updated successfully.');

        return $this->redirectToRoute('admin_report_show', ['id' => $report->getId()]);
    }

    #[Route('/{id}/update-question', name: 'admin_report_update_question', methods: ['POST'])]
    public function updateQuestion(Request $request, QuestionReport $report): Response
    {
        $question = $report->getQuestion();
        $questionText = trim($request->request->get('question_text', ''));
        $explanation = trim($request->request->get('explanation', ''));

        if (!empty($questionText)) {
            $question->setText($questionText);
        }

        $question->setExplanation($explanation ?: null);

        // Update answers if provided
        $answerTexts = $request->request->all('answer_texts');
        $answerCorrect = $request->request->all('answer_correct');

        foreach ($question->getAnswers() as $answer) {
            $answerId = $answer->getId();
            if (isset($answerTexts[$answerId])) {
                $answer->setText($answerTexts[$answerId]);
            }
            $answer->setIsCorrect(isset($answerCorrect[$answerId]));
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Question updated successfully based on the report.');

        return $this->redirectToRoute('admin_report_show', ['id' => $report->getId()]);
    }

    #[Route('/{id}/delete', name: 'admin_report_delete', methods: ['POST'])]
    public function delete(Request $request, QuestionReport $report): Response
    {
        if ($this->isCsrfTokenValid('delete-report-' . $report->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($report);
            $this->entityManager->flush();
            $this->addFlash('success', 'Report deleted.');
        }

        return $this->redirectToRoute('admin_report_index');
    }

    #[Route('/bulk-resolve', name: 'admin_report_bulk_resolve', methods: ['POST'])]
    public function bulkResolve(Request $request): Response
    {
        $ids = $request->request->all('report_ids');
        $action = $request->request->get('bulk_action', 'resolve');

        if (empty($ids)) {
            $this->addFlash('warning', 'No reports selected.');
            return $this->redirectToRoute('admin_report_index');
        }

        $newStatus = match ($action) {
            'resolve' => QuestionReport::STATUS_RESOLVED,
            'dismiss' => QuestionReport::STATUS_DISMISSED,
            'review' => QuestionReport::STATUS_REVIEWED,
            default => null,
        };

        if ($newStatus === null) {
            $this->addFlash('error', 'Invalid action.');
            return $this->redirectToRoute('admin_report_index');
        }

        $count = 0;
        foreach ($ids as $id) {
            $report = $this->reportRepository->find($id);
            if ($report) {
                $report->setStatus($newStatus);
                if ($newStatus === QuestionReport::STATUS_RESOLVED || $newStatus === QuestionReport::STATUS_DISMISSED) {
                    $report->setResolvedAt(new \DateTimeImmutable());
                }
                $count++;
            }
        }

        $this->entityManager->flush();

        $this->addFlash('success', sprintf('%d report(s) updated to "%s".', $count, $newStatus));

        return $this->redirectToRoute('admin_report_index');
    }
}

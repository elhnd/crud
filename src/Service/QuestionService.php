<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Question;
use App\Enum\QuestionType;
use App\Enum\SymfonyVersion;
use App\Repository\CategoryRepository;
use App\Repository\QuestionRepository;
use App\Repository\SubcategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class QuestionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly QuestionRepository $questionRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly SubcategoryRepository $subcategoryRepository,
    ) {
    }

    /**
     * @return array{questions: Question[], total: int, pages: int}
     */
    public function findPaginated(
        int $page = 1,
        int $limit = 20,
        ?string $search = null,
        ?int $categoryId = null,
        ?int $subcategoryId = null,
        ?string $type = null,
        ?int $difficulty = null,
        ?string $certification = null,
        ?string $active = null,
        ?string $symfonyVersion = null,
    ): array {
        $offset = ($page - 1) * $limit;

        $result = $this->questionRepository->findPaginatedWithFilters(
            $offset,
            $limit,
            $search,
            $categoryId,
            $subcategoryId,
            $type,
            $difficulty,
            $certification,
            $active,
            $symfonyVersion
        );

        return [
            'questions' => $result['questions'],
            'total' => $result['total'],
            'pages' => (int) ceil($result['total'] / $limit),
        ];
    }

    /**
     * Create or update a question from form data.
     * 
     * @param array<string, mixed> $formData
     * @return array{success: bool, question?: Question, error?: string}
     */
    public function saveFromFormData(array $formData, ?Question $question = null): array
    {
        $isNew = $question === null;

        if ($isNew) {
            $question = new Question();
        }

        // Validate required fields
        $text = trim($formData['text'] ?? '');
        if (empty($text)) {
            return ['success' => false, 'error' => 'Question text is required.'];
        }

        $typeValue = $formData['type'] ?? '';
        if (empty($typeValue)) {
            return ['success' => false, 'error' => 'Question type is required.'];
        }

        // Set basic fields
        $question->setText($text);
        $question->setType($typeValue);
        $question->setExplanation(trim($formData['explanation'] ?? '') ?: null);
        
        // Set difficulty and certification
        $difficulty = (int) ($formData['difficulty'] ?? 1);
        $question->setDifficulty($difficulty > 0 && $difficulty <= 5 ? $difficulty : 1);
        $question->setIsCertification(!empty($formData['is_certification']));

        // Set Symfony version
        $symfonyVersion = $formData['symfony_version'] ?? null;
        if ($symfonyVersion && $symfonyVersion !== '') {
            $question->setSymfonyVersion($symfonyVersion);
        } else {
            $question->setSymfonyVersion(null);
        }

        // Set category and subcategory
        $categoryId = (int) ($formData['category'] ?? 0);
        if ($categoryId > 0) {
            $category = $this->categoryRepository->find($categoryId);
            $question->setCategory($category);

            $subcategoryId = (int) ($formData['subcategory'] ?? 0);
            if ($subcategoryId > 0) {
                $subcategory = $this->subcategoryRepository->find($subcategoryId);
                if ($subcategory && $subcategory->getCategory()?->getId() === $categoryId) {
                    $question->setSubcategory($subcategory);
                } else {
                    $question->setSubcategory(null);
                }
            } else {
                $question->setSubcategory(null);
            }
        } else {
            $question->setCategory(null);
            $question->setSubcategory(null);
        }

        // Handle URLs (resourceUrl is a single string)
        $resourceUrl = $formData['resource_url'] ?? '';
        if (is_string($resourceUrl)) {
            $question->setResourceUrl(trim($resourceUrl) ?: null);
        }

        // Handle answers - supports both nested array format (answers[0][text], answers[0][correct])
        // and simple format (answers[], correct[])
        $answersData = $formData['answers'] ?? [];

        if (empty($answersData) || !is_array($answersData)) {
            return ['success' => false, 'error' => 'At least one answer is required.'];
        }

        // Filter and normalize answers
        $validAnswers = [];
        foreach ($answersData as $index => $answerItem) {
            // Handle nested format: answers[0][text], answers[0][correct]
            if (is_array($answerItem)) {
                $text = isset($answerItem['text']) && is_string($answerItem['text']) ? trim($answerItem['text']) : '';
                $isCorrect = isset($answerItem['correct']) && $answerItem['correct'];
                if (!empty($text)) {
                    $validAnswers[] = [
                        'text' => $text,
                        'isCorrect' => (bool) $isCorrect,
                    ];
                }
            }
            // Handle simple format: answers[], correct[]
            elseif (is_string($answerItem)) {
                $text = trim($answerItem);
                $correctAnswers = $formData['correct'] ?? [];
                if (!empty($text)) {
                    $validAnswers[] = [
                        'text' => $text,
                        'isCorrect' => in_array((string) $index, (array) $correctAnswers, true),
                    ];
                }
            }
        }

        if (empty($validAnswers)) {
            return ['success' => false, 'error' => 'At least one answer is required.'];
        }

        // Validate at least one correct answer
        $hasCorrect = false;
        foreach ($validAnswers as $answer) {
            if ($answer['isCorrect']) {
                $hasCorrect = true;
                break;
            }
        }

        if (!$hasCorrect) {
            return ['success' => false, 'error' => 'At least one answer must be marked as correct.'];
        }

        // Validate based on question type
        $type = QuestionType::from($typeValue);
        $correctCount = count(array_filter($validAnswers, fn($a) => $a['isCorrect']));

        if ($type === QuestionType::SINGLE_CHOICE && $correctCount !== 1) {
            return ['success' => false, 'error' => 'Single choice questions must have exactly one correct answer.'];
        }

        if ($type === QuestionType::TRUE_FALSE) {
            if (count($validAnswers) !== 2) {
                return ['success' => false, 'error' => 'True/False questions must have exactly 2 answers.'];
            }
            if ($correctCount !== 1) {
                return ['success' => false, 'error' => 'True/False questions must have exactly one correct answer.'];
            }
        }

        // Clear existing answers and add new ones
        foreach ($question->getAnswers() as $existingAnswer) {
            $question->removeAnswer($existingAnswer);
            $this->entityManager->remove($existingAnswer);
        }

        foreach ($validAnswers as $answerData) {
            $answer = new Answer();
            $answer->setText($answerData['text']);
            $answer->setIsCorrect($answerData['isCorrect']);
            $question->addAnswer($answer);
        }

        $this->entityManager->persist($question);
        $this->entityManager->flush();

        return ['success' => true, 'question' => $question];
    }

    /**
     * Duplicate a question with its answers.
     */
    public function duplicate(Question $question): Question
    {
        $newQuestion = new Question();
        $newQuestion->setText($question->getText() . ' (Copy)');
        $newQuestion->setType($question->getType());
        $newQuestion->setExplanation($question->getExplanation());
        $newQuestion->setCategory($question->getCategory());
        $newQuestion->setSubcategory($question->getSubcategory());
        $newQuestion->setResourceUrl($question->getResourceUrl());
        $newQuestion->setDifficulty($question->getDifficulty());
        $newQuestion->setIsCertification($question->isCertification());
        $newQuestion->setSymfonyVersion($question->getSymfonyVersion());

        foreach ($question->getAnswers() as $answer) {
            $newAnswer = new Answer();
            $newAnswer->setText($answer->getText());
            $newAnswer->setIsCorrect($answer->isCorrect());
            $newQuestion->addAnswer($newAnswer);
        }

        $this->entityManager->persist($newQuestion);
        $this->entityManager->flush();

        return $newQuestion;
    }

    /**
     * Delete a question.
     */
    public function delete(Question $question): void
    {
        $this->entityManager->remove($question);
        $this->entityManager->flush();
    }

    /**
     * Bulk delete questions by IDs.
     * 
     * @param int[] $ids
     * @return int Number of deleted questions
     */
    public function bulkDelete(array $ids): int
    {
        return $this->questionRepository->bulkDelete($ids);
    }

    /**
     * Get question statistics.
     * 
     * @return array{total: int, byType: array<string, int>, byCategory: array<string, int>}
     */
    public function getStatistics(): array
    {
        return $this->questionRepository->getAdminStatistics();
    }
}

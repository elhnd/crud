<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class QuizApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly QuestionRepository $questionRepository,
        private readonly AnswerRepository $answerRepository,
    ) {
    }

    #[Route('/question/{id}/edit', name: 'api_question_edit', methods: ['POST'])]
    public function editQuestion(Request $request, Question $question): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['text']) || empty(trim($data['text']))) {
            return $this->json(['success' => false, 'error' => 'Question text is required'], Response::HTTP_BAD_REQUEST);
        }
        
        $question->setText(trim($data['text']));
        
        if (isset($data['explanation'])) {
            $question->setExplanation(trim($data['explanation']));
        }
        
        if (array_key_exists('resourceUrl', $data)) {
            $question->setResourceUrl(trim($data['resourceUrl']) ?: null);
        }
        
        $this->entityManager->flush();
        
        return $this->json([
            'success' => true,
            'message' => 'Question updated successfully',
            'question' => [
                'id' => $question->getId(),
                'text' => $question->getText(),
                'explanation' => $question->getExplanation(),
                'resourceUrl' => $question->getResourceUrl(),
            ],
        ]);
    }

    #[Route('/answer/{id}/edit', name: 'api_answer_edit', methods: ['POST'])]
    public function editAnswer(Request $request, Answer $answer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['text']) || empty(trim($data['text']))) {
            return $this->json(['success' => false, 'error' => 'Answer text is required'], Response::HTTP_BAD_REQUEST);
        }
        
        $answer->setText(trim($data['text']));
        
        if (isset($data['isCorrect'])) {
            $answer->setIsCorrect((bool) $data['isCorrect']);
        }
        
        $this->entityManager->flush();
        
        return $this->json([
            'success' => true,
            'message' => 'Answer updated successfully',
            'answer' => [
                'id' => $answer->getId(),
                'text' => $answer->getText(),
                'isCorrect' => $answer->isCorrect(),
            ],
        ]);
    }

    #[Route('/question/{id}', name: 'api_question_get', methods: ['GET'])]
    public function getQuestion(Question $question): JsonResponse
    {
        $answers = [];
        foreach ($question->getAnswers() as $answer) {
            $answers[] = [
                'id' => $answer->getId(),
                'text' => $answer->getText(),
                'isCorrect' => $answer->isCorrect(),
            ];
        }
        
        return $this->json([
            'id' => $question->getId(),
            'text' => $question->getText(),
            'type' => $question->getType(),
            'difficulty' => $question->getDifficulty(),
            'explanation' => $question->getExplanation(),
            'resourceUrl' => $question->getResourceUrl(),
            'answers' => $answers,
        ]);
    }
}

<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\QuizSession;
use App\Entity\Subcategory;
use App\Entity\User;
use App\Entity\UserAnswer;
use App\Repository\QuestionRepository;
use App\Repository\QuizSessionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class QuizService
{
    private const SESSION_KEY_QUIZ = 'current_quiz_session_id';
    private const SESSION_KEY_QUESTIONS = 'current_quiz_question_ids';
    private const SESSION_KEY_QUESTION_START = 'current_question_start_time';
    private const SESSION_KEY_ANSWER_ORDER = 'current_quiz_answer_order';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly QuestionRepository $questionRepository,
        private readonly QuizSessionRepository $quizSessionRepository,
        private readonly UserRepository $userRepository,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Start a new quiz session with optional multiple categories/subcategories
     * @param array<int>|null $categoryIds Optional array of category IDs to filter
     * @param array<int>|null $subcategoryIds Optional array of subcategory IDs to filter
     */
    public function startQuiz(
        int $numberOfQuestions = 10,
        ?Category $category = null,
        ?Subcategory $subcategory = null,
        string $mode = 'random',
        ?array $categoryIds = null,
        ?array $subcategoryIds = null
    ): QuizSession {
        $user = $this->getOrCreateUser();

        // Abandon any in-progress session
        $inProgress = $this->quizSessionRepository->findInProgressByUser($user);
        if ($inProgress) {
            $inProgress->setStatus(QuizSession::STATUS_ABANDONED);
            $this->entityManager->flush();
        }

        // Get random questions - support certification mode and multi-category/subcategory selection
        if ($mode === 'certification') {
            // Certification exam uses only certification questions
            $questions = $this->questionRepository->findRandomCertificationQuestions($numberOfQuestions);
        } elseif (!empty($subcategoryIds)) {
            $questions = $this->questionRepository->findRandomQuestionsMultiSubcategory(
                $numberOfQuestions,
                $subcategoryIds
            );
        } elseif (!empty($categoryIds)) {
            $questions = $this->questionRepository->findRandomQuestionsMultiCategory(
                $numberOfQuestions,
                $categoryIds
            );
        } else {
            $questions = $this->questionRepository->findRandomQuestions(
                $numberOfQuestions,
                $category,
                $subcategory
            );
        }

        if (empty($questions)) {
            throw new \RuntimeException('No questions available for the selected criteria.');
        }

        // Shuffle questions for randomness
        shuffle($questions);

        // Create new quiz session
        $session = new QuizSession();
        $session->setUser($user);
        $session->setCategory($category);
        $session->setSubcategory($subcategory);
        $session->setTotalQuestions(count($questions));
        $session->setMode($mode);

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        // Store in HTTP session
        $httpSession = $this->requestStack->getSession();
        $httpSession->set(self::SESSION_KEY_QUIZ, $session->getId());
        $httpSession->set(self::SESSION_KEY_QUESTIONS, array_map(fn(Question $q) => $q->getId(), $questions));
        
        // Pre-generate shuffled answer orders for all questions
        $answerOrders = [];
        foreach ($questions as $question) {
            $answerIds = $question->getAnswers()->map(fn($a) => $a->getId())->toArray();
            shuffle($answerIds);
            $answerOrders[$question->getId()] = $answerIds;
        }
        $httpSession->set(self::SESSION_KEY_ANSWER_ORDER, $answerOrders);
        
        // Start timing for first question
        $httpSession->set(self::SESSION_KEY_QUESTION_START, time());

        return $session;
    }

    /**
     * Get the current quiz session
     */
    public function getCurrentSession(): ?QuizSession
    {
        $httpSession = $this->requestStack->getSession();
        $sessionId = $httpSession->get(self::SESSION_KEY_QUIZ);

        if (!$sessionId) {
            return null;
        }

        return $this->quizSessionRepository->find($sessionId);
    }

    /**
     * Get the current question for the session
     */
    public function getCurrentQuestion(QuizSession $session): ?Question
    {
        $httpSession = $this->requestStack->getSession();
        $questionIds = $httpSession->get(self::SESSION_KEY_QUESTIONS, []);

        $currentIndex = $session->getCurrentQuestionIndex();

        if (!isset($questionIds[$currentIndex])) {
            return null;
        }

        return $this->questionRepository->find($questionIds[$currentIndex]);
    }

    /**
     * Get all questions for the current session
     * @return Question[]
     */
    public function getSessionQuestions(): array
    {
        $httpSession = $this->requestStack->getSession();
        $questionIds = $httpSession->get(self::SESSION_KEY_QUESTIONS, []);

        return $this->questionRepository->findByIdsOrdered($questionIds);
    }

    /**
     * Submit an answer for the current question
     * @param array<int> $selectedAnswerIds
     */
    public function submitAnswer(QuizSession $session, array $selectedAnswerIds): UserAnswer
    {
        $httpSession = $this->requestStack->getSession();
        $question = $this->getCurrentQuestion($session);

        if (!$question) {
            throw new \RuntimeException('No current question found.');
        }

        // Calculate time spent on this question
        $questionStartTime = $httpSession->get(self::SESSION_KEY_QUESTION_START, time());
        $timeSpentSeconds = time() - $questionStartTime;

        // Create user answer
        $userAnswer = new UserAnswer();
        $userAnswer->setQuizSession($session);
        $userAnswer->setQuestion($question);
        $userAnswer->setSelectedAnswerIds($selectedAnswerIds);
        $userAnswer->setTimeSpentSeconds($timeSpentSeconds);
        $userAnswer->evaluate();

        // Reset question start time for next question
        $httpSession->set(self::SESSION_KEY_QUESTION_START, time());

        // Update session stats
        if ($userAnswer->isCorrect()) {
            $session->incrementCorrectAnswers();
        } else {
            $session->incrementIncorrectAnswers();
        }

        $session->incrementCurrentQuestionIndex();

        // Check if quiz is complete
        if ($session->getCurrentQuestionIndex() >= $session->getTotalQuestions()) {
            $session->complete();
        }

        $this->entityManager->persist($userAnswer);
        $this->entityManager->flush();

        return $userAnswer;
    }

    /**
     * Get quiz progress
     * @return array{currentIndex: int, total: int, percentage: float, answeredCount: int}
     */
    public function getProgress(QuizSession $session): array
    {
        $currentIndex = $session->getCurrentQuestionIndex();
        $total = $session->getTotalQuestions();

        return [
            'currentIndex' => $currentIndex,
            'total' => $total,
            'percentage' => $total > 0 ? ($currentIndex / $total) * 100 : 0,
            'answeredCount' => $currentIndex,
        ];
    }

    /**
     * Get quiz results
     * @return array{session: QuizSession, answers: array<UserAnswer>, questions: array<Question>}
     */
    public function getResults(QuizSession $session): array
    {
        $answers = $session->getUserAnswers()->toArray();
        $questions = $this->getSessionQuestions();

        return [
            'session' => $session,
            'answers' => $answers,
            'questions' => $questions,
        ];
    }

    /**
     * Abandon the current quiz
     */
    public function abandonQuiz(): void
    {
        $session = $this->getCurrentSession();

        if ($session && $session->isInProgress()) {
            $session->setStatus(QuizSession::STATUS_ABANDONED);
            $this->entityManager->flush();
        }

        $this->clearSessionData();
    }

    /**
     * Clear session data
     */
    public function clearSessionData(): void
    {
        $httpSession = $this->requestStack->getSession();
        $httpSession->remove(self::SESSION_KEY_QUIZ);
        $httpSession->remove(self::SESSION_KEY_QUESTIONS);
    }

    /**
     * Check if an answer was already given for a question
     */
    public function hasAnswered(QuizSession $session, Question $question): bool
    {
        foreach ($session->getUserAnswers() as $answer) {
            if ($answer->getQuestion()->getId() === $question->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get user's answer for a specific question
     */
    public function getUserAnswerForQuestion(QuizSession $session, Question $question): ?UserAnswer
    {
        foreach ($session->getUserAnswers() as $answer) {
            if ($answer->getQuestion()->getId() === $question->getId()) {
                return $answer;
            }
        }

        return null;
    }

    private function getOrCreateUser(): User
    {
        return $this->userRepository->findOrCreateDefaultUser();
    }

    /**
     * Get answers for a question in the shuffled order stored in session
     * @return Answer[]
     */
    public function getShuffledAnswers(Question $question): array
    {
        $httpSession = $this->requestStack->getSession();
        $answerOrder = $httpSession->get(self::SESSION_KEY_ANSWER_ORDER, []);
        $questionId = $question->getId();
        $answers = $question->getAnswers()->toArray();

        // If we have a stored order for this question, use it
        if (isset($answerOrder[$questionId])) {
            $orderedAnswers = [];
            $answersById = [];
            foreach ($answers as $answer) {
                $answersById[$answer->getId()] = $answer;
            }
            foreach ($answerOrder[$questionId] as $answerId) {
                if (isset($answersById[$answerId])) {
                    $orderedAnswers[] = $answersById[$answerId];
                }
            }
            // Return ordered answers if all were found
            if (count($orderedAnswers) === count($answers)) {
                return $orderedAnswers;
            }
        }

        // Fallback: shuffle now if no stored order
        shuffle($answers);
        return $answers;
    }

    /**
     * Get elapsed time for current question
     */
    public function getCurrentQuestionElapsedTime(): int
    {
        $httpSession = $this->requestStack->getSession();
        $startTime = $httpSession->get(self::SESSION_KEY_QUESTION_START, time());
        return time() - $startTime;
    }

    /**
     * Get elapsed time for entire quiz session
     */
    public function getQuizElapsedTime(QuizSession $session): int
    {
        $startedAt = $session->getStartedAt();
        if (!$startedAt) {
            return 0;
        }
        return time() - $startedAt->getTimestamp();
    }
}

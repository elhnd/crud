<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\QuizSession;
use App\Entity\Subcategory;
use App\Repository\CategoryRepository;
use App\Repository\SubcategoryRepository;
use App\Service\QuizService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/quiz')]
class QuizController extends AbstractController
{
    public function __construct(
        private readonly QuizService $quizService,
        private readonly CategoryRepository $categoryRepository,
        private readonly SubcategoryRepository $subcategoryRepository,
    ) {
    }

    #[Route('/start', name: 'quiz_start_form', methods: ['GET'])]
    public function startForm(Request $request): Response
    {
        $categories = $this->categoryRepository->findAllWithSubcategories();
        $preSelectedSubcategories = $request->query->all('subcategory');

        return $this->render('quiz/start.html.twig', [
            'categories' => $categories,
            'preSelectedSubcategories' => array_map('intval', $preSelectedSubcategories),
        ]);
    }

    #[Route('/start', name: 'quiz_start', methods: ['POST'])]
    public function start(Request $request): Response
    {
        $numberOfQuestions = (int) $request->request->get('numberOfQuestions', 10);
        $mode = $request->request->get('mode', 'random');

        // Get arrays of selected categories and subcategories
        $categoryIds = $request->request->all('categories');
        $subcategoryIds = $request->request->all('subcategories');
        
        // Convert to integers
        $categoryIds = array_map('intval', array_filter($categoryIds));
        $subcategoryIds = array_map('intval', array_filter($subcategoryIds));

        $category = null;
        $subcategory = null;

        // For backward compatibility - if single category selected
        if (count($categoryIds) === 1 && empty($subcategoryIds)) {
            $category = $this->categoryRepository->find($categoryIds[0]);
        }
        
        // For backward compatibility - if single subcategory selected
        if (count($subcategoryIds) === 1) {
            $subcategory = $this->subcategoryRepository->find($subcategoryIds[0]);
            $category = $subcategory?->getCategory();
        }

        try {
            $session = $this->quizService->startQuiz(
                $numberOfQuestions,
                $category,
                $subcategory,
                $mode,
                count($categoryIds) > 1 ? $categoryIds : null,
                count($subcategoryIds) > 1 ? $subcategoryIds : null
            );

            return $this->redirectToRoute('quiz_question');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('quiz_start_form');
        }
    }

    #[Route('/question', name: 'quiz_question')]
    public function question(): Response
    {
        $session = $this->quizService->getCurrentSession();

        if (!$session || !$session->isInProgress()) {
            $this->addFlash('warning', 'No active quiz session. Please start a new quiz.');

            return $this->redirectToRoute('quiz_start_form');
        }

        $question = $this->quizService->getCurrentQuestion($session);

        if (!$question) {
            return $this->redirectToRoute('quiz_results');
        }

        $progress = $this->quizService->getProgress($session);
        $shuffledAnswers = $this->quizService->getShuffledAnswers($question);
        $questionElapsedTime = $this->quizService->getCurrentQuestionElapsedTime();
        $quizElapsedTime = $this->quizService->getQuizElapsedTime($session);

        return $this->render('quiz/question.html.twig', [
            'session' => $session,
            'question' => $question,
            'answers' => $shuffledAnswers,
            'progress' => $progress,
            'questionNumber' => $progress['currentIndex'] + 1,
            'questionElapsedTime' => $questionElapsedTime,
            'quizElapsedTime' => $quizElapsedTime,
        ]);
    }

    #[Route('/submit', name: 'quiz_submit', methods: ['POST'])]
    public function submit(Request $request): Response
    {
        $session = $this->quizService->getCurrentSession();

        if (!$session || !$session->isInProgress()) {
            $this->addFlash('warning', 'No active quiz session.');

            return $this->redirectToRoute('quiz_start_form');
        }

        $selectedAnswers = $request->request->all('answers');
        
        // Convert to array of integers
        $selectedAnswerIds = array_map('intval', is_array($selectedAnswers) ? $selectedAnswers : [$selectedAnswers]);
        $selectedAnswerIds = array_filter($selectedAnswerIds, fn($id) => $id > 0);

        try {
            $userAnswer = $this->quizService->submitAnswer($session, $selectedAnswerIds);

            // Store flash message for feedback
            if ($userAnswer->isCorrect()) {
                $this->addFlash('success', 'Correct! ✓');
            } else {
                $this->addFlash('error', 'Incorrect. ✗');
            }

            if ($session->isCompleted()) {
                return $this->redirectToRoute('quiz_results');
            }

            return $this->redirectToRoute('quiz_question');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('quiz_question');
        }
    }

    #[Route('/results', name: 'quiz_results')]
    public function results(): Response
    {
        $session = $this->quizService->getCurrentSession();

        if (!$session) {
            $this->addFlash('warning', 'No quiz session found.');

            return $this->redirectToRoute('quiz_start_form');
        }

        $results = $this->quizService->getResults($session);

        return $this->render('quiz/results.html.twig', [
            'session' => $results['session'],
            'answers' => $results['answers'],
            'questions' => $results['questions'],
        ]);
    }

    #[Route('/review/{id}', name: 'quiz_review')]
    public function review(QuizSession $session): Response
    {
        return $this->render('quiz/review.html.twig', [
            'session' => $session,
            'answers' => $session->getUserAnswers(),
        ]);
    }

    #[Route('/abandon', name: 'quiz_abandon', methods: ['POST'])]
    public function abandon(): Response
    {
        $this->quizService->abandonQuiz();
        $this->addFlash('info', 'Quiz abandoned.');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/quick/{type}', name: 'quiz_quick_start')]
    public function quickStart(string $type): Response
    {
        $category = null;
        $mode = 'targeted';

        if ($type !== 'random') {
            $category = $this->categoryRepository->findOneBy(['name' => ucfirst($type)]);
        } else {
            $mode = 'random';
        }

        try {
            $this->quizService->startQuiz(10, $category, null, $mode);

            return $this->redirectToRoute('quiz_question');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('quiz_start_form');
        }
    }

    #[Route('/category/{id}', name: 'quiz_by_category')]
    public function byCategory(Category $category): Response
    {
        try {
            $this->quizService->startQuiz(10, $category, null, 'category');

            return $this->redirectToRoute('quiz_question');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('quiz_start_form');
        }
    }

    #[Route('/subcategory/{id}', name: 'quiz_by_subcategory')]
    public function bySubcategory(Subcategory $subcategory): Response
    {
        try {
            // Pass 0 to load ALL questions from this subcategory
            $this->quizService->startQuiz(0, $subcategory->getCategory(), $subcategory, 'subcategory');

            return $this->redirectToRoute('quiz_question');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('quiz_start_form');
        }
    }

    #[Route('/certification-exam', name: 'quiz_certification_exam')]
    public function certificationExam(): Response
    {
        try {
            // Start a certification exam with 75 random questions from all categories
            $this->quizService->startQuiz(75, null, null, 'certification');

            return $this->redirectToRoute('quiz_question');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('quiz_start_form');
        }
    }
}

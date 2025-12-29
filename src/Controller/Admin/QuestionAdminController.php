<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Enum\QuestionType;
use App\Repository\CategoryRepository;
use App\Repository\SubcategoryRepository;
use App\Service\CategoryService;
use App\Service\QuestionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/questions')]
class QuestionAdminController extends AbstractController
{
    public function __construct(
        private readonly QuestionService $questionService,
        private readonly CategoryService $categoryService,
        private readonly CategoryRepository $categoryRepository,
        private readonly SubcategoryRepository $subcategoryRepository,
    ) {
    }

    #[Route('', name: 'admin_question_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 20;

        // Get filter values, handling empty strings properly
        $search = $request->query->get('search');
        $categoryParam = $request->query->get('category');
        $categoryId = $categoryParam !== null && $categoryParam !== '' ? (int) $categoryParam : null;
        $subcategoryParam = $request->query->get('subcategory');
        $subcategoryId = $subcategoryParam !== null && $subcategoryParam !== '' ? (int) $subcategoryParam : null;
        $type = $request->query->get('type');
        $difficultyParam = $request->query->get('difficulty');
        $difficulty = $difficultyParam !== null && $difficultyParam !== '' ? (int) $difficultyParam : null;
        $certification = $request->query->get('certification');
        $active = $request->query->get('active');

        $result = $this->questionService->findPaginated(
            $page,
            $limit,
            $search,
            $categoryId,
            $subcategoryId,
            $type,
            $difficulty,
            $certification,
            $active
        );

        $stats = $this->questionService->getStatistics();

        return $this->render('admin/question/index.html.twig', [
            'questions' => $result['questions'],
            'stats' => $stats,
            'categories' => $this->categoryRepository->findAll(),
            'subcategories' => $categoryId 
                ? $this->subcategoryRepository->findBy(['category' => $categoryId]) 
                : [],
            'questionTypes' => QuestionType::cases(),
            'filters' => [
                'search' => $search,
                'category' => $categoryId,
                'subcategory' => $subcategoryId,
                'type' => $type,
                'difficulty' => $difficulty,
                'certification' => $certification,
                'active' => $active,
            ],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $result['total'],
                'pages' => $result['pages'],
            ],
        ]);
    }

    #[Route('/new', name: 'admin_question_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleQuestionForm($request);
        }

        return $this->renderQuestionForm();
    }

    #[Route('/bulk-delete', name: 'admin_question_bulk_delete', methods: ['POST'])]
    public function bulkDelete(Request $request): Response
    {
        $ids = $request->request->all('ids');

        if ($this->isCsrfTokenValid('bulk_delete', $request->request->get('_token')) && !empty($ids)) {
            $deleted = $this->questionService->bulkDelete($ids);
            $this->addFlash('success', sprintf('%d question(s) deleted successfully.', $deleted));
        }

        return $this->redirectToRoute('admin_question_index');
    }

    #[Route('/api/subcategories/{categoryId}', name: 'admin_question_api_subcategories', methods: ['GET'])]
    public function apiGetSubcategories(int $categoryId): JsonResponse
    {
        $subcategories = $this->categoryService->getSubcategoriesForCategory($categoryId);

        $data = array_map(fn($s) => [
            'id' => $s->getId(),
            'name' => $s->getName(),
        ], $subcategories);

        return new JsonResponse($data);
    }

    #[Route('/api/categories', name: 'admin_question_api_create_category', methods: ['POST'])]
    public function apiCreateCategory(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '') ?: null;

        $result = $this->categoryService->createCategory($name, $description);

        if (!$result['success']) {
            return new JsonResponse(['success' => false, 'error' => $result['error']], 400);
        }

        $category = $result['category'];
        return new JsonResponse([
            'success' => true,
            'category' => [
                'id' => $category->getId(),
                'name' => $category->getName(),
            ],
        ]);
    }

    #[Route('/api/subcategories', name: 'admin_question_api_create_subcategory', methods: ['POST'])]
    public function apiCreateSubcategory(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $categoryId = (int) ($data['category_id'] ?? 0);
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '') ?: null;

        $category = $this->categoryRepository->find($categoryId);
        if (!$category) {
            return new JsonResponse(['success' => false, 'error' => 'Category not found.'], 404);
        }

        $result = $this->categoryService->createSubcategory($category, $name, $description);

        if (!$result['success']) {
            return new JsonResponse(['success' => false, 'error' => $result['error']], 400);
        }

        $subcategory = $result['subcategory'];
        return new JsonResponse([
            'success' => true,
            'subcategory' => [
                'id' => $subcategory->getId(),
                'name' => $subcategory->getName(),
            ],
        ]);
    }

    #[Route('/{id}', name: 'admin_question_show', methods: ['GET'])]
    public function show(Question $question): Response
    {
        return $this->render('admin/question/show.html.twig', [
            'question' => $question,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_question_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Question $question): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleQuestionForm($request, $question);
        }

        return $this->renderQuestionForm($question);
    }

    #[Route('/{id}/delete', name: 'admin_question_delete', methods: ['POST'])]
    public function delete(Request $request, Question $question): Response
    {
        if ($this->isCsrfTokenValid('delete' . $question->getId(), $request->request->get('_token'))) {
            $this->questionService->delete($question);
            $this->addFlash('success', 'Question deleted successfully.');
        }

        return $this->redirectToRoute('admin_question_index');
    }

    #[Route('/{id}/duplicate', name: 'admin_question_duplicate', methods: ['POST'])]
    public function duplicate(Request $request, Question $question): Response
    {
        if ($this->isCsrfTokenValid('duplicate' . $question->getId(), $request->request->get('_token'))) {
            $newQuestion = $this->questionService->duplicate($question);
            $this->addFlash('success', 'Question duplicated successfully.');

            return $this->redirectToRoute('admin_question_edit', ['id' => $newQuestion->getId()]);
        }

        return $this->redirectToRoute('admin_question_index');
    }

    private function renderQuestionForm(?Question $question = null): Response
    {
        return $this->render('admin/question/form.html.twig', [
            'question' => $question,
            'categories' => $this->categoryRepository->findAll(),
            'subcategories' => $question?->getCategory() 
                ? $this->subcategoryRepository->findBy(['category' => $question->getCategory()]) 
                : [],
            'questionTypes' => QuestionType::cases(),
        ]);
    }

    private function handleQuestionForm(Request $request, ?Question $question = null): Response
    {
        $isNew = $question === null;

        if (!$this->isCsrfTokenValid('question_form', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_question_index');
        }

        $formData = [
            'text' => $request->request->get('text'),
            'type' => $request->request->get('type'),
            'explanation' => $request->request->get('explanation'),
            'category' => $request->request->get('category'),
            'subcategory' => $request->request->get('subcategory'),
            'resource_url' => $request->request->get('resource_url'),
            'answers' => $request->request->all('answers'),
            'difficulty' => $request->request->get('difficulty'),
            'is_certification' => $request->request->get('is_certification'),
        ];

        $result = $this->questionService->saveFromFormData($formData, $question);

        if (!$result['success']) {
            $this->addFlash('error', $result['error']);
            return $this->render('admin/question/form.html.twig', [
                'question' => $question,
                'categories' => $this->categoryRepository->findAll(),
                'subcategories' => [],
                'questionTypes' => QuestionType::cases(),
                'formData' => $request->request->all(),
            ]);
        }

        $this->addFlash('success', $isNew ? 'Question created successfully.' : 'Question updated successfully.');

        return $this->redirectToRoute('admin_question_index');
    }
}

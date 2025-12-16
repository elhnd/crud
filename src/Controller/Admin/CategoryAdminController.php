<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Subcategory;
use App\Repository\CategoryRepository;
use App\Repository\QuestionRepository;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/categories')]
class CategoryAdminController extends AbstractController
{
    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly CategoryRepository $categoryRepository,
        private readonly QuestionRepository $questionRepository,
    ) {
    }

    #[Route('', name: 'admin_category_index', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->categoryService->getAllWithSubcategories();
        $questionCounts = $this->questionRepository->countByCategory();
        $subcategoryCounts = $this->questionRepository->countBySubcategory();

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
            'questionCounts' => $questionCounts,
            'subcategoryCounts' => $subcategoryCounts,
        ]);
    }

    #[Route('/new', name: 'admin_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleCategoryForm($request);
        }

        return $this->render('admin/category/form.html.twig', [
            'category' => null,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleCategoryForm($request, $category);
        }

        return $this->render('admin/category/form.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $result = $this->categoryService->deleteCategory($category);

            if ($result['success']) {
                $this->addFlash('success', 'Category deleted successfully.');
            } else {
                $this->addFlash('error', $result['error']);
            }
        }

        return $this->redirectToRoute('admin_category_index');
    }

    #[Route('/{id}/subcategories/new', name: 'admin_subcategory_new', methods: ['GET', 'POST'])]
    public function newSubcategory(Request $request, Category $category): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleSubcategoryForm($request, $category);
        }

        return $this->render('admin/category/subcategory_form.html.twig', [
            'category' => $category,
            'subcategory' => null,
        ]);
    }

    #[Route('/subcategories/{id}/edit', name: 'admin_subcategory_edit', methods: ['GET', 'POST'])]
    public function editSubcategory(Request $request, Subcategory $subcategory): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleSubcategoryForm($request, $subcategory->getCategory(), $subcategory);
        }

        return $this->render('admin/category/subcategory_form.html.twig', [
            'category' => $subcategory->getCategory(),
            'subcategory' => $subcategory,
            'categories' => $this->categoryRepository->findAll(),
        ]);
    }

    #[Route('/subcategories/{id}/delete', name: 'admin_subcategory_delete', methods: ['POST'])]
    public function deleteSubcategory(Request $request, Subcategory $subcategory): Response
    {
        if ($this->isCsrfTokenValid('delete' . $subcategory->getId(), $request->request->get('_token'))) {
            $result = $this->categoryService->deleteSubcategory($subcategory);

            if ($result['success']) {
                $this->addFlash('success', 'Subcategory deleted successfully.');
            } else {
                $this->addFlash('error', $result['error']);
            }
        }

        return $this->redirectToRoute('admin_category_index');
    }

    private function handleCategoryForm(Request $request, ?Category $category = null): Response
    {
        $isNew = $category === null;

        if (!$this->isCsrfTokenValid('category_form', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_category_index');
        }

        $formData = [
            'name' => $request->request->get('name'),
            'description' => $request->request->get('description'),
            'icon' => $request->request->get('icon'),
            'color' => $request->request->get('color'),
        ];

        $result = $this->categoryService->saveCategory($formData, $category);

        if (!$result['success']) {
            $this->addFlash('error', $result['error']);
            return $this->render('admin/category/form.html.twig', [
                'category' => $category,
                'formData' => $request->request->all(),
            ]);
        }

        $this->addFlash('success', $isNew ? 'Category created successfully.' : 'Category updated successfully.');

        return $this->redirectToRoute('admin_category_index');
    }

    private function handleSubcategoryForm(Request $request, Category $category, ?Subcategory $subcategory = null): Response
    {
        $isNew = $subcategory === null;

        if (!$this->isCsrfTokenValid('subcategory_form', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_category_index');
        }

        $formData = [
            'name' => $request->request->get('name'),
            'description' => $request->request->get('description'),
            'category_id' => $request->request->getInt('category_id'),
        ];

        $result = $this->categoryService->saveSubcategory($formData, $category, $subcategory);

        if (!$result['success']) {
            $this->addFlash('error', $result['error']);
            return $this->render('admin/category/subcategory_form.html.twig', [
                'category' => $category,
                'subcategory' => $subcategory,
                'categories' => $this->categoryRepository->findAll(),
                'formData' => $request->request->all(),
            ]);
        }

        $this->addFlash('success', $isNew ? 'Subcategory created successfully.' : 'Subcategory updated successfully.');

        return $this->redirectToRoute('admin_category_index');
    }
}

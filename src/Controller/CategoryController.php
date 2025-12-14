<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\QuestionRepository;
use App\Repository\SubcategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categories')]
class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly SubcategoryRepository $subcategoryRepository,
        private readonly QuestionRepository $questionRepository,
    ) {
    }

    #[Route('', name: 'category_list')]
    public function list(): Response
    {
        $categories = $this->categoryRepository->findAllWithSubcategories();
        $questionCounts = $this->questionRepository->countByCategory();
        $subcategoryCounts = $this->questionRepository->countBySubcategory();

        return $this->render('category/list.html.twig', [
            'categories' => $categories,
            'questionCounts' => $questionCounts,
            'subcategoryCounts' => $subcategoryCounts,
        ]);
    }

    #[Route('/{id}', name: 'category_show')]
    public function show(Category $category): Response
    {
        $subcategories = $this->subcategoryRepository->findByCategory($category);
        $questionCounts = $this->questionRepository->countBySubcategory();

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'subcategories' => $subcategories,
            'questionCounts' => $questionCounts,
        ]);
    }

    #[Route('/{id}/subcategories', name: 'api_category_subcategories')]
    public function getSubcategories(Category $category): JsonResponse
    {
        $subcategories = $this->subcategoryRepository->findByCategory($category);

        $data = array_map(function ($subcategory) {
            return [
                'id' => $subcategory->getId(),
                'name' => $subcategory->getName(),
                'description' => $subcategory->getDescription(),
            ];
        }, $subcategories);

        return $this->json($data);
    }
}

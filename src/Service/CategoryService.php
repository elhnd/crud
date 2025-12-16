<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Subcategory;
use App\Repository\CategoryRepository;
use App\Repository\SubcategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryRepository $categoryRepository,
        private readonly SubcategoryRepository $subcategoryRepository,
    ) {
    }

    /**
     * Get all categories with their subcategories.
     * 
     * @return Category[]
     */
    public function getAllWithSubcategories(): array
    {
        return $this->categoryRepository->findAllWithSubcategories();
    }

    /**
     * Create or update a category from form data.
     * 
     * @param array<string, mixed> $formData
     * @return array{success: bool, category?: Category, error?: string}
     */
    public function saveCategory(array $formData, ?Category $category = null): array
    {
        $isNew = $category === null;

        if ($isNew) {
            $category = new Category();
        }

        $name = trim($formData['name'] ?? '');
        if (empty($name)) {
            return ['success' => false, 'error' => 'Category name is required.'];
        }

        // Check for duplicate name
        $existing = $this->categoryRepository->findOneBy(['name' => $name]);
        if ($existing && $existing->getId() !== $category->getId()) {
            return ['success' => false, 'error' => 'A category with this name already exists.'];
        }

        $category->setName($name);
        $category->setDescription(trim($formData['description'] ?? '') ?: null);
        $category->setIcon(trim($formData['icon'] ?? '') ?: null);
        $category->setColor(trim($formData['color'] ?? '') ?: null);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return ['success' => true, 'category' => $category];
    }

    /**
     * Create a category with minimal data (for API/inline creation).
     * 
     * @return array{success: bool, category?: Category, error?: string}
     */
    public function createCategory(string $name, ?string $description = null): array
    {
        $name = trim($name);
        if (empty($name)) {
            return ['success' => false, 'error' => 'Category name is required.'];
        }

        // Check for duplicate
        $existing = $this->categoryRepository->findOneBy(['name' => $name]);
        if ($existing) {
            return ['success' => false, 'error' => 'A category with this name already exists.'];
        }

        $category = new Category();
        $category->setName($name);
        $category->setDescription($description);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return ['success' => true, 'category' => $category];
    }

    /**
     * Delete a category if it has no questions.
     * 
     * @return array{success: bool, error?: string}
     */
    public function deleteCategory(Category $category): array
    {
        if ($category->getQuestions()->count() > 0) {
            return ['success' => false, 'error' => 'Cannot delete category with existing questions. Delete or reassign questions first.'];
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return ['success' => true];
    }

    /**
     * Create or update a subcategory from form data.
     * 
     * @param array<string, mixed> $formData
     * @return array{success: bool, subcategory?: Subcategory, error?: string}
     */
    public function saveSubcategory(array $formData, Category $category, ?Subcategory $subcategory = null): array
    {
        $isNew = $subcategory === null;

        if ($isNew) {
            $subcategory = new Subcategory();
        }

        $name = trim($formData['name'] ?? '');
        if (empty($name)) {
            return ['success' => false, 'error' => 'Subcategory name is required.'];
        }

        // If category changed during edit
        $categoryId = (int) ($formData['category_id'] ?? 0);
        if (!$isNew && $categoryId > 0 && $categoryId !== $category->getId()) {
            $newCategory = $this->categoryRepository->find($categoryId);
            if ($newCategory) {
                $category = $newCategory;
            }
        }

        // Check for duplicate name within the same category
        $existing = $this->subcategoryRepository->findOneBy(['name' => $name, 'category' => $category]);
        if ($existing && $existing->getId() !== $subcategory->getId()) {
            return ['success' => false, 'error' => 'A subcategory with this name already exists in this category.'];
        }

        $subcategory->setName($name);
        $subcategory->setDescription(trim($formData['description'] ?? '') ?: null);
        $subcategory->setCategory($category);

        $this->entityManager->persist($subcategory);
        $this->entityManager->flush();

        return ['success' => true, 'subcategory' => $subcategory];
    }

    /**
     * Create a subcategory with minimal data (for API/inline creation).
     * 
     * @return array{success: bool, subcategory?: Subcategory, error?: string}
     */
    public function createSubcategory(Category $category, string $name, ?string $description = null): array
    {
        $name = trim($name);
        if (empty($name)) {
            return ['success' => false, 'error' => 'Subcategory name is required.'];
        }

        // Check for duplicate
        $existing = $this->subcategoryRepository->findOneBy(['name' => $name, 'category' => $category]);
        if ($existing) {
            return ['success' => false, 'error' => 'A subcategory with this name already exists in this category.'];
        }

        $subcategory = new Subcategory();
        $subcategory->setName($name);
        $subcategory->setDescription($description);
        $subcategory->setCategory($category);

        $this->entityManager->persist($subcategory);
        $this->entityManager->flush();

        return ['success' => true, 'subcategory' => $subcategory];
    }

    /**
     * Delete a subcategory if it has no questions.
     * 
     * @return array{success: bool, error?: string}
     */
    public function deleteSubcategory(Subcategory $subcategory): array
    {
        if ($subcategory->getQuestions()->count() > 0) {
            return ['success' => false, 'error' => 'Cannot delete subcategory with existing questions. Delete or reassign questions first.'];
        }

        $this->entityManager->remove($subcategory);
        $this->entityManager->flush();

        return ['success' => true];
    }

    /**
     * Get subcategories for a category.
     * 
     * @return Subcategory[]
     */
    public function getSubcategoriesForCategory(int $categoryId): array
    {
        $category = $this->categoryRepository->find($categoryId);
        if (!$category) {
            return [];
        }

        return $this->subcategoryRepository->findBy(
            ['category' => $category],
            ['name' => 'ASC']
        );
    }
}

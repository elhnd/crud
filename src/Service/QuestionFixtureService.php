<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Subcategory;
use App\Enum\QuestionType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour gérer l'upsert des questions dans les fixtures.
 * Permet de mettre à jour les questions existantes au lieu de les recréer,
 * préservant ainsi les IDs et la cohérence des statistiques.
 */
class QuestionFixtureService
{
    private array $questionCache = [];
    private array $categoryCache = [];
    private array $subcategoryCache = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Récupère ou crée une catégorie
     */
    public function getOrCreateCategory(string $name, ?string $description = null, ?string $icon = null, ?string $color = null): Category
    {
        if (isset($this->categoryCache[$name])) {
            return $this->categoryCache[$name];
        }

        $category = $this->entityManager->getRepository(Category::class)->findOneBy(['name' => $name]);
        
        if (!$category) {
            $category = new Category();
            $category->setName($name);
            $category->setDescription($description ?? $name);
            $category->setIcon($icon ?? 'code');
            $category->setColor($color ?? '#000000');
            $this->entityManager->persist($category);
        }

        $this->categoryCache[$name] = $category;
        return $category;
    }

    /**
     * Récupère ou crée une sous-catégorie
     */
    public function getOrCreateSubcategory(string $name, Category $category, ?string $description = null): Subcategory
    {
        $key = $category->getName() . ':' . $name;
        
        if (isset($this->subcategoryCache[$key])) {
            return $this->subcategoryCache[$key];
        }

        $subcategory = $this->entityManager->getRepository(Subcategory::class)->findOneBy([
            'name' => $name,
            'category' => $category,
        ]);

        if (!$subcategory) {
            $subcategory = new Subcategory();
            $subcategory->setName($name);
            $subcategory->setDescription($description ?? $name);
            $subcategory->setCategory($category);
            $this->entityManager->persist($subcategory);
        }

        $this->subcategoryCache[$key] = $subcategory;
        return $subcategory;
    }

    /**
     * Génère un identifiant unique pour une question basé sur son texte
     */
    public function generateIdentifier(string $text): string
    {
        $normalized = mb_strtolower(trim($text));
        return substr(hash('sha256', $normalized), 0, 16);
    }

    /**
     * Crée ou met à jour une question (upsert)
     * 
     * @param array $data Les données de la question avec les clés:
     *   - text: string (requis)
     *   - type: QuestionType (requis)
     *   - difficulty: int (requis)
     *   - explanation: string (requis)
     *   - resourceUrl: ?string (optionnel)
     *   - category: Category (requis)
     *   - subcategory: Subcategory (requis)
     *   - isCertification: bool (optionnel, défaut: false)
     *   - answers: array (requis) - tableau de ['text' => string, 'correct' => bool]
     *   - identifier: ?string (optionnel) - si non fourni, sera généré à partir du texte
     */
    public function upsertQuestion(array $data): Question
    {
        $identifier = $data['identifier'] ?? $this->generateIdentifier($data['text']);

        // Chercher d'abord dans le cache
        if (isset($this->questionCache[$identifier])) {
            return $this->updateQuestion($this->questionCache[$identifier], $data);
        }

        // Chercher dans la base de données
        $question = $this->entityManager->getRepository(Question::class)->findOneBy(['identifier' => $identifier]);

        if ($question) {
            $this->questionCache[$identifier] = $question;
            return $this->updateQuestion($question, $data);
        }

        // Créer une nouvelle question
        return $this->createQuestion($data, $identifier);
    }

    /**
     * Met à jour une question existante
     */
    private function updateQuestion(Question $question, array $data): Question
    {
        $question->setText($data['text']);
        $question->setTypeEnum($data['type']);
        $question->setDifficulty($data['difficulty']);
        $question->setExplanation($data['explanation']);
        $question->setResourceUrl($data['resourceUrl'] ?? null);
        $question->setCategory($data['category']);
        $question->setSubcategory($data['subcategory']);
        $question->setIsCertification($data['isCertification'] ?? false);
        $question->setUpdatedAt(new \DateTimeImmutable());

        // Mettre à jour les réponses
        $this->updateAnswers($question, $data['answers']);

        return $question;
    }

    /**
     * Crée une nouvelle question
     */
    private function createQuestion(array $data, string $identifier): Question
    {
        $question = new Question();
        $question->setIdentifier($identifier);
        $question->setText($data['text']);
        $question->setTypeEnum($data['type']);
        $question->setDifficulty($data['difficulty']);
        $question->setExplanation($data['explanation']);
        $question->setResourceUrl($data['resourceUrl'] ?? null);
        $question->setCategory($data['category']);
        $question->setSubcategory($data['subcategory']);
        $question->setIsCertification($data['isCertification'] ?? false);

        foreach ($data['answers'] as $a) {
            $answer = new Answer();
            $answer->setText($a['text']);
            $answer->setIsCorrect($a['correct']);
            $question->addAnswer($answer);
        }

        $this->entityManager->persist($question);
        $this->questionCache[$identifier] = $question;

        return $question;
    }

    /**
     * Met à jour les réponses d'une question existante
     */
    private function updateAnswers(Question $question, array $newAnswers): void
    {
        $existingAnswers = $question->getAnswers()->toArray();
        $existingCount = count($existingAnswers);
        $newCount = count($newAnswers);

        // Mettre à jour les réponses existantes
        for ($i = 0; $i < min($existingCount, $newCount); $i++) {
            $existingAnswers[$i]->setText($newAnswers[$i]['text']);
            $existingAnswers[$i]->setIsCorrect($newAnswers[$i]['correct']);
        }

        // Ajouter les nouvelles réponses si nécessaire
        for ($i = $existingCount; $i < $newCount; $i++) {
            $answer = new Answer();
            $answer->setText($newAnswers[$i]['text']);
            $answer->setIsCorrect($newAnswers[$i]['correct']);
            $question->addAnswer($answer);
        }

        // Supprimer les réponses en trop
        for ($i = $newCount; $i < $existingCount; $i++) {
            $question->removeAnswer($existingAnswers[$i]);
            $this->entityManager->remove($existingAnswers[$i]);
        }
    }

    /**
     * Flush toutes les modifications
     */
    public function flush(): void
    {
        $this->entityManager->flush();
    }

    /**
     * Vide les caches
     */
    public function clearCache(): void
    {
        $this->questionCache = [];
        $this->categoryCache = [];
        $this->subcategoryCache = [];
    }
}

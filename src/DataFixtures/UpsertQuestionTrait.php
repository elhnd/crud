<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Subcategory;
use App\Entity\Answer;
use Doctrine\Persistence\ObjectManager;

/**
 * Trait pour permettre l'upsert des questions dans les fixtures.
 * Les questions existantes sont mises à jour au lieu d'être recréées,
 * préservant ainsi les IDs et la cohérence des statistiques.
 */
trait UpsertQuestionTrait
{
    private array $questionIdentifierCache = [];

    /**
     * Génère un identifiant unique pour une question basé sur:
     * - Le texte de la question
     * - La catégorie
     * - La sous-catégorie  
     * - Les réponses (texte et si correct)
     */
    protected function generateIdentifier(array $data): string
    {
        $parts = [
            mb_strtolower(trim($data['text'])),
            $data['category']->getName(),
            $data['subcategory']->getName(),
        ];

        // Ajouter les réponses triées pour cohérence
        if (isset($data['answers']) && is_array($data['answers'])) {
            $answerStrings = [];
            foreach ($data['answers'] as $answer) {
                $answerStrings[] = mb_strtolower(trim($answer['text'])) . ':' . ($answer['correct'] ? '1' : '0');
            }
            sort($answerStrings); // Tri pour avoir un hash cohérent
            $parts[] = implode('|', $answerStrings);
        }

        $normalized = implode('###', $parts);
        return substr(hash('sha256', $normalized), 0, 16);
    }

    /**
     * Crée ou met à jour une question (upsert)
     */
    protected function upsertQuestion(ObjectManager $manager, array $data): Question
    {
        $identifier = $this->generateIdentifier($data);

        // Chercher d'abord dans le cache
        if (isset($this->questionIdentifierCache[$identifier])) {
            return $this->updateQuestion($manager, $this->questionIdentifierCache[$identifier], $data);
        }

        // Chercher dans la base de données
        $question = $manager->getRepository(Question::class)->findOneBy(['identifier' => $identifier]);

        if ($question) {
            $this->questionIdentifierCache[$identifier] = $question;
            return $this->updateQuestion($manager, $question, $data);
        }

        // Créer une nouvelle question
        return $this->createQuestion($manager, $data, $identifier);
    }

    /**
     * Met à jour une question existante
     */
    private function updateQuestion(ObjectManager $manager, Question $question, array $data): Question
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
        $this->updateAnswers($manager, $question, $data['answers']);

        return $question;
    }

    /**
     * Crée une nouvelle question
     */
    private function createQuestion(ObjectManager $manager, array $data, string $identifier): Question
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

        $manager->persist($question);
        $this->questionIdentifierCache[$identifier] = $question;

        return $question;
    }

    /**
     * Met à jour les réponses d'une question existante
     */
    private function updateAnswers(ObjectManager $manager, Question $question, array $newAnswers): void
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
            $manager->remove($existingAnswers[$i]);
        }
    }

    /**
     * Récupère ou crée une sous-catégorie
     */
    protected function getOrCreateSubcategory(
        ObjectManager $manager,
        string $name,
        Category $category,
        ?string $description = null,
        array &$cache = []
    ): Subcategory {
        $key = $category->getName() . ':' . $name;

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $subcategory = $manager->getRepository(Subcategory::class)->findOneBy([
            'name' => $name,
            'category' => $category,
        ]);

        if (!$subcategory) {
            $subcategory = new Subcategory();
            $subcategory->setName($name);
            $subcategory->setDescription($description ?? $name);
            $subcategory->setCategory($category);
            $manager->persist($subcategory);
        }

        $cache[$key] = $subcategory;
        return $subcategory;
    }
}

<?php

namespace App\Entity;

use App\Enum\QuestionType;
use App\Enum\SymfonyVersion;
use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\Index(columns: ['identifier'], name: 'idx_question_identifier')]
#[ORM\HasLifecycleCallbacks]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true, nullable: true)]
    private ?string $identifier = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $explanation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resourceUrl = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null;

    #[ORM\Column]
    private int $difficulty = 1;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Subcategory $subcategory = null;

    /**
     * @var Collection<int, Answer>
     */
    #[ORM\OneToMany(targetEntity: Answer::class, mappedBy: 'question', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $answers;

    /**
     * @var Collection<int, UserAnswer>
     */
    #[ORM\OneToMany(targetEntity: UserAnswer::class, mappedBy: 'question')]
    private Collection $userAnswers;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isCertification = false;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $symfonyVersion = null;

    /** @var Collection<int, QuestionExplanation> */
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: QuestionExplanation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $aiExplanations;

    /**
     * @var Collection<int, QuestionReport>
     */
    #[ORM\OneToMany(targetEntity: QuestionReport::class, mappedBy: 'question', orphanRemoval: true, cascade: ['remove'])]
    private Collection $reports;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->userAnswers = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->aiExplanations = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Génère un identifiant unique basé sur:
     * - Le texte de la question
     * - La catégorie
     * - La sous-catégorie
     * - Les réponses
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function generateIdentifier(): static
    {
        if ($this->text && $this->category && $this->subcategory) {
            $parts = [
                mb_strtolower(trim($this->text)),
                $this->category->getName(),
                $this->subcategory->getName(),
            ];

            // Ajouter les réponses triées pour cohérence
            if (!$this->answers->isEmpty()) {
                $answerStrings = [];
                foreach ($this->answers as $answer) {
                    $answerStrings[] = mb_strtolower(trim($answer->getText())) . ':' . ($answer->isCorrect() ? '1' : '0');
                }
                sort($answerStrings);
                $parts[] = implode('|', $answerStrings);
            }

            $normalized = implode('###', $parts);
            $this->identifier = substr(hash('sha256', $normalized), 0, 16);
        }

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setExplanation(?string $explanation): static
    {
        $this->explanation = $explanation;

        return $this;
    }

    public function getResourceUrl(): ?string
    {
        return $this->resourceUrl;
    }

    public function setResourceUrl(?string $resourceUrl): static
    {
        $this->resourceUrl = $resourceUrl;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeEnum(): QuestionType
    {
        return QuestionType::from($this->type);
    }

    public function setTypeEnum(QuestionType $type): static
    {
        $this->type = $type->value;

        return $this;
    }

    public function getDifficulty(): int
    {
        return $this->difficulty;
    }

    public function setDifficulty(int $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getSubcategory(): ?Subcategory
    {
        return $this->subcategory;
    }

    public function setSubcategory(?Subcategory $subcategory): static
    {
        $this->subcategory = $subcategory;

        return $this;
    }

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): static
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): static
    {
        if ($this->answers->removeElement($answer)) {
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserAnswer>
     */
    public function getUserAnswers(): Collection
    {
        return $this->userAnswers;
    }

    public function addUserAnswer(UserAnswer $userAnswer): static
    {
        if (!$this->userAnswers->contains($userAnswer)) {
            $this->userAnswers->add($userAnswer);
            $userAnswer->setQuestion($this);
        }

        return $this;
    }

    public function removeUserAnswer(UserAnswer $userAnswer): static
    {
        if ($this->userAnswers->removeElement($userAnswer)) {
            if ($userAnswer->getQuestion() === $this) {
                $userAnswer->setQuestion(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get correct answers for this question
     * @return Collection<int, Answer>
     */
    public function getCorrectAnswers(): Collection
    {
        return $this->answers->filter(fn(Answer $answer) => $answer->isCorrect());
    }

    public function isMultipleChoice(): bool
    {
        return $this->type === QuestionType::MULTIPLE_CHOICE->value;
    }

    public function isSingleChoice(): bool
    {
        return $this->type === QuestionType::SINGLE_CHOICE->value;
    }

    public function isTrueFalse(): bool
    {
        return $this->type === QuestionType::TRUE_FALSE->value;
    }

    public function isCertification(): bool
    {
        return $this->isCertification;
    }

    public function setIsCertification(bool $isCertification): static
    {
        $this->isCertification = $isCertification;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getSymfonyVersion(): ?string
    {
        return $this->symfonyVersion;
    }

    public function setSymfonyVersion(?string $symfonyVersion): static
    {
        $this->symfonyVersion = $symfonyVersion;

        return $this;
    }

    public function getSymfonyVersionEnum(): ?SymfonyVersion
    {
        return $this->symfonyVersion ? SymfonyVersion::from($this->symfonyVersion) : null;
    }

    public function setSymfonyVersionEnum(?SymfonyVersion $version): static
    {
        $this->symfonyVersion = $version?->value;

        return $this;
    }

    /**
     * @return Collection<int, QuestionExplanation>
     */
    public function getAiExplanations(): Collection
    {
        return $this->aiExplanations;
    }

    /**
     * Get AI explanation for a specific locale
     */
    public function getAiExplanation(?string $locale = null): ?QuestionExplanation
    {
        if ($locale === null) {
            return $this->aiExplanations->first() ?: null;
        }

        foreach ($this->aiExplanations as $explanation) {
            if ($explanation->getLocale() === $locale) {
                return $explanation;
            }
        }

        return null;
    }

    public function addAiExplanation(QuestionExplanation $explanation): static
    {
        if (!$this->aiExplanations->contains($explanation)) {
            $this->aiExplanations->add($explanation);
            $explanation->setQuestion($this);
        }

        return $this;
    }

    public function removeAiExplanation(QuestionExplanation $explanation): static
    {
        if ($this->aiExplanations->removeElement($explanation)) {
            if ($explanation->getQuestion() === $this) {
                $explanation->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * Get available locales for AI explanations
     * @return string[]
     */
    public function getAvailableExplanationLocales(): array
    {
        return $this->aiExplanations->map(fn(QuestionExplanation $e) => $e->getLocale())->toArray();
    }

    /**
     * @return Collection<int, QuestionReport>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(QuestionReport $report): static
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
            $report->setQuestion($this);
        }

        return $this;
    }

    public function removeReport(QuestionReport $report): static
    {
        if ($this->reports->removeElement($report)) {
            if ($report->getQuestion() === $this) {
                $report->setQuestion(null);
            }
        }

        return $this;
    }

    public function getPendingReportsCount(): int
    {
        return $this->reports->filter(
            fn(QuestionReport $report) => $report->getStatus() === QuestionReport::STATUS_PENDING
        )->count();
    }
}

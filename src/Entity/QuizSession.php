<?php

namespace App\Entity;

use App\Repository\QuizSessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizSessionRepository::class)]
class QuizSession
{
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ABANDONED = 'abandoned';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'quizSessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Category $category = null;

    #[ORM\ManyToOne]
    private ?Subcategory $subcategory = null;

    #[ORM\Column]
    private int $totalQuestions = 0;

    #[ORM\Column]
    private int $correctAnswers = 0;

    #[ORM\Column]
    private int $incorrectAnswers = 0;

    #[ORM\Column]
    private float $score = 0.0;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_IN_PROGRESS;

    #[ORM\Column]
    private int $currentQuestionIndex = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    /**
     * @var Collection<int, UserAnswer>
     */
    #[ORM\OneToMany(targetEntity: UserAnswer::class, mappedBy: 'quizSession', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $userAnswers;

    #[ORM\Column(length: 50)]
    private string $mode = 'random';

    #[ORM\Column(nullable: true)]
    private ?int $timeSpentSeconds = null;

    public function __construct()
    {
        $this->userAnswers = new ArrayCollection();
        $this->startedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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

    public function getTotalQuestions(): int
    {
        return $this->totalQuestions;
    }

    public function setTotalQuestions(int $totalQuestions): static
    {
        $this->totalQuestions = $totalQuestions;

        return $this;
    }

    public function getCorrectAnswers(): int
    {
        return $this->correctAnswers;
    }

    public function setCorrectAnswers(int $correctAnswers): static
    {
        $this->correctAnswers = $correctAnswers;

        return $this;
    }

    public function incrementCorrectAnswers(): static
    {
        $this->correctAnswers++;

        return $this;
    }

    public function getIncorrectAnswers(): int
    {
        return $this->incorrectAnswers;
    }

    public function setIncorrectAnswers(int $incorrectAnswers): static
    {
        $this->incorrectAnswers = $incorrectAnswers;

        return $this;
    }

    public function incrementIncorrectAnswers(): static
    {
        $this->incorrectAnswers++;

        return $this;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function setScore(float $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function calculateScore(): static
    {
        if ($this->totalQuestions > 0) {
            $this->score = ($this->correctAnswers / $this->totalQuestions) * 100;
        }

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function getCurrentQuestionIndex(): int
    {
        return $this->currentQuestionIndex;
    }

    public function setCurrentQuestionIndex(int $currentQuestionIndex): static
    {
        $this->currentQuestionIndex = $currentQuestionIndex;

        return $this;
    }

    public function incrementCurrentQuestionIndex(): static
    {
        $this->currentQuestionIndex++;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function complete(): static
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completedAt = new \DateTimeImmutable();
        $this->calculateScore();

        if ($this->startedAt) {
            $this->timeSpentSeconds = $this->completedAt->getTimestamp() - $this->startedAt->getTimestamp();
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
            $userAnswer->setQuizSession($this);
        }

        return $this;
    }

    public function removeUserAnswer(UserAnswer $userAnswer): static
    {
        if ($this->userAnswers->removeElement($userAnswer)) {
            if ($userAnswer->getQuizSession() === $this) {
                $userAnswer->setQuizSession(null);
            }
        }

        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    public function getTimeSpentSeconds(): ?int
    {
        return $this->timeSpentSeconds;
    }

    public function setTimeSpentSeconds(?int $timeSpentSeconds): static
    {
        $this->timeSpentSeconds = $timeSpentSeconds;

        return $this;
    }

    public function getFormattedTimeSpent(): string
    {
        if (!$this->timeSpentSeconds) {
            return '0:00';
        }

        $minutes = floor($this->timeSpentSeconds / 60);
        $seconds = $this->timeSpentSeconds % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getSessionLabel(): string
    {
        if ($this->subcategory) {
            return $this->subcategory->getFullName();
        }

        if ($this->category) {
            return $this->category->getName();
        }

        return 'Random Quiz';
    }
}

<?php

namespace App\Entity;

use App\Repository\UserAnswerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserAnswerRepository::class)]
class UserAnswer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?QuizSession $quizSession = null;

    #[ORM\ManyToOne(inversedBy: 'userAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    /**
     * @var array<int> Selected answer IDs
     */
    #[ORM\Column(type: Types::JSON)]
    private array $selectedAnswerIds = [];

    #[ORM\Column]
    private bool $isCorrect = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $answeredAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $timeSpentSeconds = null;

    public function __construct()
    {
        $this->answeredAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuizSession(): ?QuizSession
    {
        return $this->quizSession;
    }

    public function setQuizSession(?QuizSession $quizSession): static
    {
        $this->quizSession = $quizSession;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return array<int>
     */
    public function getSelectedAnswerIds(): array
    {
        return $this->selectedAnswerIds;
    }

    /**
     * @param array<int> $selectedAnswerIds
     */
    public function setSelectedAnswerIds(array $selectedAnswerIds): static
    {
        $this->selectedAnswerIds = $selectedAnswerIds;

        return $this;
    }

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function setIsCorrect(bool $isCorrect): static
    {
        $this->isCorrect = $isCorrect;

        return $this;
    }

    public function getAnsweredAt(): ?\DateTimeImmutable
    {
        return $this->answeredAt;
    }

    public function setAnsweredAt(\DateTimeImmutable $answeredAt): static
    {
        $this->answeredAt = $answeredAt;

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

    /**
     * Evaluate if the user's answer is correct
     */
    public function evaluate(): static
    {
        $question = $this->question;
        if (!$question) {
            $this->isCorrect = false;
            return $this;
        }

        $correctAnswerIds = $question->getCorrectAnswers()
            ->map(fn(Answer $answer) => $answer->getId())
            ->toArray();

        sort($correctAnswerIds);
        $selectedIds = $this->selectedAnswerIds;
        sort($selectedIds);

        $this->isCorrect = $correctAnswerIds === $selectedIds;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\QuestionExplanationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionExplanationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class QuestionExplanation
{
    public const LOCALE_EN = 'en';
    public const LOCALE_FR = 'fr';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'aiExplanation', targetEntity: Question::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Question $question = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(length: 5)]
    private string $locale = self::LOCALE_EN;

    #[ORM\Column]
    private ?\DateTimeImmutable $generatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $modelUsed = null;

    #[ORM\Column(nullable: true)]
    private ?int $tokensUsed = null;

    public function __construct()
    {
        $this->generatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getGeneratedAt(): ?\DateTimeImmutable
    {
        return $this->generatedAt;
    }

    public function setGeneratedAt(\DateTimeImmutable $generatedAt): static
    {
        $this->generatedAt = $generatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getModelUsed(): ?string
    {
        return $this->modelUsed;
    }

    public function setModelUsed(?string $modelUsed): static
    {
        $this->modelUsed = $modelUsed;

        return $this;
    }

    public function getTokensUsed(): ?int
    {
        return $this->tokensUsed;
    }

    public function setTokensUsed(?int $tokensUsed): static
    {
        $this->tokensUsed = $tokensUsed;

        return $this;
    }
}

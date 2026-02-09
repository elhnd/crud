<?php

namespace App\Entity;

use App\Repository\QuestionReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionReportRepository::class)]
#[ORM\HasLifecycleCallbacks]
class QuestionReport
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    public const TYPE_WRONG_ANSWER = 'wrong_answer';
    public const TYPE_TYPO = 'typo';
    public const TYPE_UNCLEAR = 'unclear';
    public const TYPE_DUPLICATE = 'duplicate';
    public const TYPE_OUTDATED = 'outdated';
    public const TYPE_OTHER = 'other';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    #[ORM\Column(length: 30)]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $adminResponse = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resolvedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_WRONG_ANSWER => 'Wrong Answer',
            self::TYPE_TYPO => 'Typo / Grammar',
            self::TYPE_UNCLEAR => 'Unclear Question',
            self::TYPE_DUPLICATE => 'Duplicate Question',
            self::TYPE_OUTDATED => 'Outdated Content',
            self::TYPE_OTHER => 'Other',
            default => $this->type,
        };
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

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

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_REVIEWED => 'Reviewed',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_DISMISSED => 'Dismissed',
            default => $this->status,
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_REVIEWED => 'blue',
            self::STATUS_RESOLVED => 'green',
            self::STATUS_DISMISSED => 'gray',
            default => 'gray',
        };
    }

    public function getAdminResponse(): ?string
    {
        return $this->adminResponse;
    }

    public function setAdminResponse(?string $adminResponse): static
    {
        $this->adminResponse = $adminResponse;

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

    public function getResolvedAt(): ?\DateTimeImmutable
    {
        return $this->resolvedAt;
    }

    public function setResolvedAt(?\DateTimeImmutable $resolvedAt): static
    {
        $this->resolvedAt = $resolvedAt;

        return $this;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function getTypes(): array
    {
        return [
            'Wrong Answer' => self::TYPE_WRONG_ANSWER,
            'Typo / Grammar' => self::TYPE_TYPO,
            'Unclear Question' => self::TYPE_UNCLEAR,
            'Duplicate Question' => self::TYPE_DUPLICATE,
            'Outdated Content' => self::TYPE_OUTDATED,
            'Other' => self::TYPE_OTHER,
        ];
    }

    public static function getStatuses(): array
    {
        return [
            'Pending' => self::STATUS_PENDING,
            'Reviewed' => self::STATUS_REVIEWED,
            'Resolved' => self::STATUS_RESOLVED,
            'Dismissed' => self::STATUS_DISMISSED,
        ];
    }
}

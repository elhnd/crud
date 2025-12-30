<?php

namespace App\Service;

use App\Entity\Question;
use App\Entity\QuestionExplanation;
use App\Repository\QuestionExplanationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClaudeAIService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const MODEL = 'claude-sonnet-4-20250514';
    private const MAX_TOKENS = 4096;
    private const TIMEOUT = 60; // seconds

    private ?string $lastError = null;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly QuestionExplanationRepository $explanationRepository,
        private readonly LoggerInterface $logger,
        private readonly string $claudeApiKey,
    ) {
    }

    /**
     * Check if the API is reachable
     */
    public function isApiReachable(): bool
    {
        try {
            $response = $this->httpClient->request('GET', 'https://api.anthropic.com', [
                'timeout' => 5,
            ]);
            return $response->getStatusCode() < 500;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate an AI explanation for a question based on Symfony documentation
     */
    public function generateExplanation(Question $question, string $locale = 'en', bool $forceRegenerate = false): ?QuestionExplanation
    {
        // Check if explanation already exists
        $existingExplanation = $this->explanationRepository->findByQuestion($question);
        
        if ($existingExplanation && !$forceRegenerate) {
            // If locale is different, regenerate
            if ($existingExplanation->getLocale() === $locale) {
                return $existingExplanation;
            }
        }

        try {
            $prompt = $this->buildPrompt($question, $locale);
            $response = $this->callClaudeAPI($prompt);

            if ($response === null) {
                return null;
            }

            $explanation = $existingExplanation ?? new QuestionExplanation();
            $explanation->setQuestion($question);
            $explanation->setContent($response['content']);
            $explanation->setLocale($locale);
            $explanation->setModelUsed(self::MODEL);
            $explanation->setTokensUsed($response['tokens'] ?? null);
            
            if ($existingExplanation) {
                $explanation->setUpdatedAt(new \DateTimeImmutable());
            } else {
                $explanation->setGeneratedAt(new \DateTimeImmutable());
            }

            $this->entityManager->persist($explanation);
            $this->entityManager->flush();

            return $explanation;
        } catch (\Exception $e) {
            $this->logger->error('Failed to generate AI explanation', [
                'question_id' => $question->getId(),
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function buildPrompt(Question $question, string $locale): string
    {
        $languageInstruction = $locale === 'fr' 
            ? 'Réponds en français.' 
            : 'Respond in English.';

        $categoryContext = sprintf(
            "Category: %s\nSubcategory: %s",
            $question->getCategory()->getName(),
            $question->getSubcategory()->getName()
        );

        // Build answers context
        $answersContext = "Possible answers:\n";
        foreach ($question->getAnswers() as $answer) {
            $marker = $answer->isCorrect() ? '✓' : '✗';
            $answersContext .= sprintf("  %s %s\n", $marker, strip_tags($answer->getText()));
        }

        $prompt = <<<PROMPT
You are an expert Symfony and PHP developer. Your task is to explain a certification exam question by providing educational content based on the official Symfony documentation.

{$languageInstruction}

**Question Context:**
{$categoryContext}

**Question:**
{$question->getText()}

{$answersContext}

**Your task:**
1. Explain the underlying concept being tested in this question
2. Reference the relevant Symfony documentation and best practices
3. Provide practical code examples that illustrate the concept
4. Explain why the correct answer(s) are correct
5. Explain common pitfalls or misconceptions related to this topic

**Format your response using Markdown with:**
- Clear section headers
- Code blocks with proper syntax highlighting (use ```php for PHP code, ```yaml for YAML, ```twig for Twig)
- Bullet points for lists
- Bold text for important concepts

**Important:** Focus on teaching the concept, not just explaining the answer. Help the learner understand the "why" behind the correct answer.
PROMPT;

        return $prompt;
    }

    private function callClaudeAPI(string $prompt): ?array
    {
        if (empty($this->claudeApiKey)) {
            $this->logger->error('Claude API key is not configured');
            return null;
        }

        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'timeout' => self::TIMEOUT,
                'headers' => [
                    'x-api-key' => $this->claudeApiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'model' => self::MODEL,
                    'max_tokens' => self::MAX_TOKENS,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ],
            ]);

            $data = $response->toArray();

            if (!isset($data['content'][0]['text'])) {
                $this->logger->error('Unexpected Claude API response format', ['response' => $data]);
                return null;
            }

            return [
                'content' => $data['content'][0]['text'],
                'tokens' => ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0),
            ];
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            $this->logger->error('Claude API request failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get existing explanation or null if not generated yet
     */
    public function getExplanation(Question $question): ?QuestionExplanation
    {
        return $this->explanationRepository->findByQuestion($question);
    }

    /**
     * Check if an explanation exists for a question
     */
    public function hasExplanation(Question $question): bool
    {
        return $this->explanationRepository->findByQuestion($question) !== null;
    }

    /**
     * Save a manual explanation (useful when API is not reachable)
     */
    public function saveManualExplanation(Question $question, string $content, string $locale = 'en'): QuestionExplanation
    {
        $existingExplanation = $this->explanationRepository->findByQuestion($question);
        
        $explanation = $existingExplanation ?? new QuestionExplanation();
        $explanation->setQuestion($question);
        $explanation->setContent($content);
        $explanation->setLocale($locale);
        $explanation->setModelUsed('manual');
        
        if ($existingExplanation) {
            $explanation->setUpdatedAt(new \DateTimeImmutable());
        } else {
            $explanation->setGeneratedAt(new \DateTimeImmutable());
        }

        $this->entityManager->persist($explanation);
        $this->entityManager->flush();

        return $explanation;
    }

    /**
     * Get the last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError ?? null;
    }
}

<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CodeFormatterExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_code', [$this, 'formatCode'], ['is_safe' => ['html']]),
            new TwigFilter('truncate_question', [$this, 'truncateQuestion'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Truncate question text for preview, handling code blocks intelligently
     * Shows text before code block, or truncates safely
     */
    public function truncateQuestion(string $text, int $maxLength = 150): string
    {
        // Check if there's a code block
        if (preg_match('/```/', $text)) {
            // Get text before the code block
            $parts = preg_split('/```/', $text, 2);
            $beforeCode = trim($parts[0]);
            
            if (strlen($beforeCode) > 20) {
                // Use text before code block
                $preview = $beforeCode;
                if (strlen($preview) > $maxLength) {
                    $preview = substr($preview, 0, $maxLength) . '...';
                } else {
                    $preview .= ' [code]';
                }
                return $this->formatCode($preview);
            } else {
                // Code block is at the beginning, show a summary
                return $this->formatCode($beforeCode . ' [code snippet...]');
            }
        }
        
        // No code block, just truncate normally
        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength) . '...';
        }
        
        return $this->formatCode($text);
    }

    /**
     * Format text with code blocks for proper syntax highlighting
     * Handles:
     * - ```php ... ``` code blocks
     * - ```yaml ... ``` code blocks
     * - ```xml ... ``` code blocks
     * - ```twig ... ``` code blocks
     * - Inline `code` snippets
     * - Plain code that looks like PHP/YAML
     * - HTML tags are rendered (not escaped)
     */
    public function formatCode(string $text): string
    {
        // Store code blocks temporarily to protect them
        $codeBlocks = [];
        $placeholder = '___CODE_BLOCK_%d___';
        
        // Extract and protect fenced code blocks: ```php ... ```
        $text = preg_replace_callback(
            '/```(php|yaml|xml|twig|json|bash|shell|text|html)?[\s\n]*(.*?)```/s',
            function ($matches) use (&$codeBlocks, $placeholder) {
                $language = $matches[1] ?: $this->detectLanguage($matches[2]);
                $code = htmlspecialchars(trim($matches[2]), ENT_QUOTES, 'UTF-8');
                $index = count($codeBlocks);
                $codeBlocks[$index] = sprintf('<pre><code class="language-%s">%s</code></pre>', $language, $code);
                return sprintf($placeholder, $index);
            },
            $text
        );
        
        // Extract and protect inline code: `code`
        $text = preg_replace_callback(
            '/`([^`]+)`/',
            function ($matches) use (&$codeBlocks, $placeholder) {
                $code = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
                $index = count($codeBlocks);
                $codeBlocks[$index] = '<code>' . $code . '</code>';
                return sprintf($placeholder, $index);
            },
            $text
        );
        
        // Convert line breaks to <br> (HTML tags are preserved, not escaped)
        $text = nl2br($text);
        
        // Restore code blocks
        foreach ($codeBlocks as $index => $block) {
            $text = str_replace(sprintf($placeholder, $index), $block, $text);
        }
        
        return $text;
    }

    /**
     * Try to detect the language of a code snippet
     */
    private function detectLanguage(string $code): string
    {
        // PHP patterns
        if (preg_match('/(<\?php|\$[a-zA-Z_]|->|::class|namespace |use |class |function |public |private |protected |new |return )/', $code)) {
            return 'php';
        }
        
        // YAML patterns
        if (preg_match('/^[a-zA-Z_]+:\s*$/m', $code) || preg_match('/^\s+-\s+/m', $code)) {
            return 'yaml';
        }
        
        // XML/HTML patterns
        if (preg_match('/<[a-zA-Z][^>]*>/', $code)) {
            return 'xml';
        }
        
        // Twig patterns
        if (preg_match('/\{[{%#]/', $code)) {
            return 'twig';
        }
        
        return 'text';
    }
}

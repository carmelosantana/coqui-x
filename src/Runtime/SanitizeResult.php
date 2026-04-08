<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitX\Runtime;

/**
 * Result of a tweet content sanitization check.
 */
final readonly class SanitizeResult
{
    /**
     * @param bool $safe Whether the text passed all safety checks
     * @param string $text The original text that was checked
     * @param array<string> $warnings List of detected issues (empty if safe)
     */
    public function __construct(
        public bool $safe,
        public string $text,
        public array $warnings = [],
    ) {}

    /**
     * Format warnings as a single message string.
     */
    public function warningMessage(): string
    {
        if ($this->warnings === []) {
            return '';
        }

        return 'Content blocked — potential prompt injection: ' . implode('; ', $this->warnings)
            . '. The text was NOT posted. Please review and rephrase the content.';
    }
}

<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitX\Runtime;

/**
 * Screens outbound tweet content for prompt injection patterns.
 *
 * Detects instruction-override, account-manipulation, and credential-exfiltration
 * patterns in text before it reaches the X API. Uses word-boundary matching to
 * minimize false positives on normal conversational content.
 */
final class TweetSanitizer
{
    /**
     * Patterns that attempt to override the bot's instructions.
     *
     * @var array<string>
     */
    private const array INSTRUCTION_OVERRIDE_PATTERNS = [
        '/\bignore\s+(all\s+)?previous\s+instructions?\b/i',
        '/\byou\s+are\s+now\b/i',
        '/\bsystem\s+prompt\b/i',
        '/\boverride\s+(your|the|all)\s+(instructions?|rules?|guidelines?)\b/i',
        '/\bforget\s+(your|all|the)\s+(instructions?|rules?|guidelines?|programming)\b/i',
        '/\bact\s+as\s+(if|though|a)\b/i',
        '/\bjailbreak\b/i',
        '/\bdisregard\s+(your|all|the|previous)\b/i',
        '/\bnew\s+instructions?\s*:/i',
        '/\bdo\s+not\s+follow\s+(your|the|any)\s+(rules?|instructions?|guidelines?)\b/i',
        '/\bpretend\s+(you|to\s+be)\b/i',
    ];

    /**
     * Patterns that attempt to manipulate the Twitter account.
     *
     * @var array<string>
     */
    private const array ACCOUNT_MANIPULATION_PATTERNS = [
        '/\bdelete\s+(my|the|this|your)\s+account\b/i',
        '/\bdeactivate\s+(my|the|this|your)\s+account\b/i',
        '/\bsuspend\s+(my|the|this|your)\s+account\b/i',
        '/\bchange\s+(my|the|this|your)\s+password\b/i',
        '/\breset\s+(my|the|this|your)\s+password\b/i',
        '/\bmodify\s+(my|the|this|your)\s+(email|phone|settings)\b/i',
        '/\brevoke\s+(all\s+)?access\b/i',
    ];

    /**
     * Patterns that attempt to exfiltrate credentials or sensitive data.
     *
     * @var array<string>
     */
    private const array EXFILTRATION_PATTERNS = [
        '/\b(send|post|tweet|share|reveal|show|display|output)\s+(my|the|your|all)?\s*(credentials?|api[_\s-]?keys?|tokens?|secrets?|passwords?|bearer)\b/i',
        '/\b(leak|exfiltrate|extract|dump)\s+(the\s+)?(credentials?|api[_\s-]?keys?|tokens?|secrets?|passwords?)\b/i',
        '/\bwhat\s+(is|are)\s+(my|the|your)\s+(api[_\s-]?keys?|tokens?|secrets?|passwords?|credentials?)\b/i',
    ];

    /**
     * Sanitize text intended for posting as a tweet.
     *
     * @return SanitizeResult Result indicating whether the text is safe
     */
    public static function sanitize(string $text): SanitizeResult
    {
        $warnings = [];

        foreach (self::INSTRUCTION_OVERRIDE_PATTERNS as $pattern) {
            if (preg_match($pattern, $text) === 1) {
                $warnings[] = 'Instruction override attempt detected';
                break;
            }
        }

        foreach (self::ACCOUNT_MANIPULATION_PATTERNS as $pattern) {
            if (preg_match($pattern, $text) === 1) {
                $warnings[] = 'Account manipulation attempt detected';
                break;
            }
        }

        foreach (self::EXFILTRATION_PATTERNS as $pattern) {
            if (preg_match($pattern, $text) === 1) {
                $warnings[] = 'Credential exfiltration attempt detected';
                break;
            }
        }

        return new SanitizeResult(
            safe: $warnings === [],
            text: $text,
            warnings: $warnings,
        );
    }
}

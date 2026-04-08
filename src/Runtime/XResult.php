<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitX\Runtime;

use CarmeloSantana\PHPAgents\Tool\ToolResult;

/**
 * Typed response value object for X API v2 responses.
 *
 * Maps the X API response envelope to a structured object, then converts
 * to ToolResult for agent consumption.
 */
final readonly class XResult
{
    /**
     * @param array<int, array<string, mixed>> $errors
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public bool $success,
        public mixed $data,
        public array $errors = [],
        public array $meta = [],
        public int $statusCode = 200,
    ) {}

    /**
     * Create an error result without hitting the API.
     */
    public static function error(string $message, int $statusCode = 0): self
    {
        return new self(
            success: false,
            data: null,
            errors: [['message' => $message]],
            statusCode: $statusCode,
        );
    }

    /**
     * Format all error messages into a single string.
     */
    public function errorMessage(): string
    {
        if ($this->errors === []) {
            return 'Unknown error';
        }

        $messages = [];

        foreach ($this->errors as $error) {
            $msg = (string) ($error['message'] ?? $error['detail'] ?? $error['title'] ?? 'Unknown error');
            $code = $error['type'] ?? $error['code'] ?? null;

            if ($code !== null) {
                $msg .= " [{$code}]";
            }

            $messages[] = $msg;
        }

        return implode('; ', $messages);
    }

    /**
     * Convert to ToolResult for the agent.
     */
    public function toToolResult(): ToolResult
    {
        if (!$this->success) {
            return ToolResult::error($this->errorMessage());
        }

        return ToolResult::success($this->formatData());
    }

    /**
     * Convert to ToolResult with a success prefix message.
     */
    public function toToolResultWith(string $successPrefix): ToolResult
    {
        if (!$this->success) {
            return ToolResult::error($this->errorMessage());
        }

        $data = $this->formatData();

        if ($data === '' || $data === '[]' || $data === 'null') {
            return ToolResult::success($successPrefix);
        }

        return ToolResult::success($successPrefix . "\n\n" . $data);
    }

    /**
     * Format the response data as readable JSON for the agent.
     */
    private function formatData(): string
    {
        if ($this->data === null) {
            return '';
        }

        if (is_string($this->data)) {
            return $this->data;
        }

        $encoded = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $encoded !== false ? $encoded : '';
    }
}

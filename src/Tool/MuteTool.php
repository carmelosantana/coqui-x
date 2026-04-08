<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitX\Tool;

use CarmeloSantana\PHPAgents\Contract\ToolInterface;
use CarmeloSantana\PHPAgents\Tool\Parameter\EnumParameter;
use CarmeloSantana\PHPAgents\Tool\Parameter\NumberParameter;
use CarmeloSantana\PHPAgents\Tool\Parameter\StringParameter;
use CarmeloSantana\PHPAgents\Tool\Tool;
use CarmeloSantana\PHPAgents\Tool\ToolResult;
use CarmeloSantana\CoquiToolkitX\Runtime\XClient;
use CarmeloSantana\CoquiToolkitX\Runtime\XResult;

/**
 * Mute and unmute users, list muted users via the X API v2.
 */
final readonly class MuteTool
{
    public function __construct(private XClient $client) {}

    public function build(): ToolInterface
    {
        return new Tool(
            name: 'x_mute',
            description: 'Manage muted users on X (Twitter) — list muted users, mute a user, or unmute a user.',
            parameters: [
                new EnumParameter(
                    'action',
                    'The mute operation to perform',
                    values: ['list', 'mute', 'unmute'],
                    required: true,
                ),
                new StringParameter(
                    'target_user_id',
                    'The user ID to mute or unmute (required for mute/unmute)',
                ),
                new NumberParameter(
                    'max_results',
                    'Number of results to return for list (1-1000, default 100)',
                ),
                new StringParameter(
                    'pagination_token',
                    'Token for retrieving the next page of results',
                ),
            ],
            callback: fn(array $args): ToolResult => $this->execute($args),
        );
    }

    /**
     * @param array<string, mixed> $args
     */
    private function execute(array $args): ToolResult
    {
        $action = trim((string) ($args['action'] ?? ''));

        return match ($action) {
            'list' => $this->listMuted($args),
            'mute' => $this->muteUser($args),
            'unmute' => $this->unmuteUser($args),
            default => ToolResult::error("Unknown action: {$action}. Use: list, mute, unmute"),
        };
    }

    /**
     * @param array<string, mixed> $args
     */
    private function listMuted(array $args): ToolResult
    {
        $oauthCheck = $this->client->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck->toToolResult();
        }

        $userId = $this->resolveAuthenticatedUserId();

        if ($userId instanceof ToolResult) {
            return $userId;
        }

        $query = [
            'user.fields' => 'username,name,verified,description,public_metrics',
        ];

        $maxResults = (int) ($args['max_results'] ?? 100);
        $query['max_results'] = max(1, min(1000, $maxResults));

        $paginationToken = trim((string) ($args['pagination_token'] ?? ''));

        if ($paginationToken !== '') {
            $query['pagination_token'] = $paginationToken;
        }

        return $this->client->get("/users/{$userId}/muting", $query)->toToolResult();
    }

    /**
     * @param array<string, mixed> $args
     */
    private function muteUser(array $args): ToolResult
    {
        $targetUserId = trim((string) ($args['target_user_id'] ?? ''));

        if ($targetUserId === '') {
            return ToolResult::error('Parameter "target_user_id" is required for mute action.');
        }

        $oauthCheck = $this->client->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck->toToolResult();
        }

        $userId = $this->resolveAuthenticatedUserId();

        if ($userId instanceof ToolResult) {
            return $userId;
        }

        return $this->client->post("/users/{$userId}/muting", ['target_user_id' => $targetUserId])
            ->toToolResultWith("Muted user {$targetUserId}.");
    }

    /**
     * @param array<string, mixed> $args
     */
    private function unmuteUser(array $args): ToolResult
    {
        $targetUserId = trim((string) ($args['target_user_id'] ?? ''));

        if ($targetUserId === '') {
            return ToolResult::error('Parameter "target_user_id" is required for unmute action.');
        }

        $oauthCheck = $this->client->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck->toToolResult();
        }

        $userId = $this->resolveAuthenticatedUserId();

        if ($userId instanceof ToolResult) {
            return $userId;
        }

        return $this->client->delete("/users/{$userId}/muting/{$targetUserId}")
            ->toToolResultWith("Unmuted user {$targetUserId}.");
    }

    private function resolveAuthenticatedUserId(): string|ToolResult
    {
        $meResult = $this->client->get('/users/me');

        if (!$meResult->success) {
            return $meResult->toToolResult();
        }

        $id = is_array($meResult->data) ? (string) ($meResult->data['id'] ?? '') : '';

        if ($id === '') {
            return ToolResult::error('Could not determine authenticated user ID.');
        }

        return $id;
    }
}

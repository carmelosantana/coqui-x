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
 * List followers/following, follow and unfollow users via the X API v2.
 */
final readonly class FollowerTool
{
    public function __construct(private XClient $client) {}

    public function build(): ToolInterface
    {
        return new Tool(
            name: 'x_follower',
            description: 'Manage followers on X (Twitter) — list a user\'s followers, list who a user is following, follow a user, or unfollow a user.',
            parameters: [
                new EnumParameter(
                    'action',
                    'The follower operation to perform',
                    values: ['followers', 'following', 'follow', 'unfollow'],
                    required: true,
                ),
                new StringParameter(
                    'user_id',
                    'The user ID (required for followers/following; for follow/unfollow this is the authenticated user — auto-resolved if omitted)',
                ),
                new StringParameter(
                    'target_user_id',
                    'The user ID to follow or unfollow (required for follow/unfollow)',
                ),
                new NumberParameter(
                    'max_results',
                    'Number of results to return (1-1000, default 100)',
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
            'followers' => $this->listFollowers($args),
            'following' => $this->listFollowing($args),
            'follow' => $this->followUser($args),
            'unfollow' => $this->unfollowUser($args),
            default => ToolResult::error("Unknown action: {$action}. Use: followers, following, follow, unfollow"),
        };
    }

    /**
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    private function buildListQuery(array $args): array
    {
        $query = [
            'user.fields' => 'username,name,verified,description,public_metrics',
        ];

        $maxResults = (int) ($args['max_results'] ?? 100);
        $query['max_results'] = max(1, min(1000, $maxResults));

        $paginationToken = trim((string) ($args['pagination_token'] ?? ''));

        if ($paginationToken !== '') {
            $query['pagination_token'] = $paginationToken;
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $args
     */
    private function listFollowers(array $args): ToolResult
    {
        $userId = trim((string) ($args['user_id'] ?? ''));

        if ($userId === '') {
            return ToolResult::error('Parameter "user_id" is required for followers action.');
        }

        return $this->client->get("/users/{$userId}/followers", $this->buildListQuery($args))->toToolResult();
    }

    /**
     * @param array<string, mixed> $args
     */
    private function listFollowing(array $args): ToolResult
    {
        $userId = trim((string) ($args['user_id'] ?? ''));

        if ($userId === '') {
            return ToolResult::error('Parameter "user_id" is required for following action.');
        }

        return $this->client->get("/users/{$userId}/following", $this->buildListQuery($args))->toToolResult();
    }

    /**
     * @param array<string, mixed> $args
     */
    private function followUser(array $args): ToolResult
    {
        $targetUserId = trim((string) ($args['target_user_id'] ?? ''));

        if ($targetUserId === '') {
            return ToolResult::error('Parameter "target_user_id" is required for follow action.');
        }

        $oauthCheck = $this->client->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck->toToolResult();
        }

        $userId = $this->resolveAuthenticatedUserId($args);

        if ($userId instanceof ToolResult) {
            return $userId;
        }

        return $this->client->post("/users/{$userId}/following", ['target_user_id' => $targetUserId])
            ->toToolResultWith("Now following user {$targetUserId}.");
    }

    /**
     * @param array<string, mixed> $args
     */
    private function unfollowUser(array $args): ToolResult
    {
        $targetUserId = trim((string) ($args['target_user_id'] ?? ''));

        if ($targetUserId === '') {
            return ToolResult::error('Parameter "target_user_id" is required for unfollow action.');
        }

        $oauthCheck = $this->client->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck->toToolResult();
        }

        $userId = $this->resolveAuthenticatedUserId($args);

        if ($userId instanceof ToolResult) {
            return $userId;
        }

        return $this->client->delete("/users/{$userId}/following/{$targetUserId}")
            ->toToolResultWith("Unfollowed user {$targetUserId}.");
    }

    /**
     * Resolve the authenticated user's ID from args or via /users/me.
     *
     * @param array<string, mixed> $args
     */
    private function resolveAuthenticatedUserId(array $args): string|ToolResult
    {
        $userId = trim((string) ($args['user_id'] ?? ''));

        if ($userId !== '') {
            return $userId;
        }

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

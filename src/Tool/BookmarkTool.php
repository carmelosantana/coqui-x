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
 * Manage tweet bookmarks via the X API v2.
 */
final readonly class BookmarkTool
{
    public function __construct(private XClient $client) {}

    public function build(): ToolInterface
    {
        return new Tool(
            name: 'x_bookmark',
            description: 'Manage bookmarks on X (Twitter) — list your bookmarked tweets, add a tweet to bookmarks, or remove a tweet from bookmarks.',
            parameters: [
                new EnumParameter(
                    'action',
                    'The bookmark operation to perform',
                    values: ['list', 'add', 'remove'],
                    required: true,
                ),
                new StringParameter(
                    'tweet_id',
                    'The tweet ID (required for add and remove)',
                ),
                new NumberParameter(
                    'max_results',
                    'Number of results to return for list (1-100, default 10)',
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
            'list' => $this->listBookmarks($args),
            'add' => $this->addBookmark($args),
            'remove' => $this->removeBookmark($args),
            default => ToolResult::error("Unknown action: {$action}. Use: list, add, remove"),
        };
    }

    /**
     * @param array<string, mixed> $args
     */
    private function listBookmarks(array $args): ToolResult
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
            'tweet.fields' => 'created_at,public_metrics,author_id,lang',
            'expansions' => 'author_id',
            'user.fields' => 'username,name,verified',
        ];

        $maxResults = (int) ($args['max_results'] ?? 10);
        $query['max_results'] = max(1, min(100, $maxResults));

        $paginationToken = trim((string) ($args['pagination_token'] ?? ''));

        if ($paginationToken !== '') {
            $query['pagination_token'] = $paginationToken;
        }

        return $this->client->get("/users/{$userId}/bookmarks", $query)->toToolResult();
    }

    /**
     * @param array<string, mixed> $args
     */
    private function addBookmark(array $args): ToolResult
    {
        $tweetId = trim((string) ($args['tweet_id'] ?? ''));

        if ($tweetId === '') {
            return ToolResult::error('Parameter "tweet_id" is required for add action.');
        }

        $oauthCheck = $this->client->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck->toToolResult();
        }

        $userId = $this->resolveAuthenticatedUserId();

        if ($userId instanceof ToolResult) {
            return $userId;
        }

        return $this->client->post("/users/{$userId}/bookmarks", ['tweet_id' => $tweetId])
            ->toToolResultWith("Tweet {$tweetId} bookmarked.");
    }

    /**
     * @param array<string, mixed> $args
     */
    private function removeBookmark(array $args): ToolResult
    {
        $tweetId = trim((string) ($args['tweet_id'] ?? ''));

        if ($tweetId === '') {
            return ToolResult::error('Parameter "tweet_id" is required for remove action.');
        }

        $oauthCheck = $this->client->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck->toToolResult();
        }

        $userId = $this->resolveAuthenticatedUserId();

        if ($userId instanceof ToolResult) {
            return $userId;
        }

        return $this->client->delete("/users/{$userId}/bookmarks/{$tweetId}")
            ->toToolResultWith("Tweet {$tweetId} removed from bookmarks.");
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

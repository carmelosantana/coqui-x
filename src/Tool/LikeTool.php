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
 * Like and unlike tweets, list liked tweets and liking users via the X API v2.
 */
final readonly class LikeTool
{
    public function __construct(private XClient $client) {}

    public function build(): ToolInterface
    {
        return new Tool(
            name: 'x_like',
            description: 'Manage tweet likes on X (Twitter) — like a tweet, unlike a tweet, list tweets liked by a user, or list users who liked a tweet.',
            parameters: [
                new EnumParameter(
                    'action',
                    'The like operation to perform',
                    values: ['like', 'unlike', 'liked_tweets', 'liking_users'],
                    required: true,
                ),
                new StringParameter(
                    'tweet_id',
                    'The tweet ID (required for like, unlike, liking_users)',
                ),
                new StringParameter(
                    'user_id',
                    'The user ID (required for liked_tweets; for like/unlike this is the authenticated user — auto-resolved if omitted)',
                ),
                new NumberParameter(
                    'max_results',
                    'Number of results to return (default 10 for liked_tweets, 100 for liking_users)',
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
            'like' => $this->likeTweet($args),
            'unlike' => $this->unlikeTweet($args),
            'liked_tweets' => $this->likedTweets($args),
            'liking_users' => $this->likingUsers($args),
            default => ToolResult::error("Unknown action: {$action}. Use: like, unlike, liked_tweets, liking_users"),
        };
    }

    /**
     * @param array<string, mixed> $args
     */
    private function likeTweet(array $args): ToolResult
    {
        $tweetId = trim((string) ($args['tweet_id'] ?? ''));

        if ($tweetId === '') {
            return ToolResult::error('Parameter "tweet_id" is required for like action.');
        }

        $oauthCheck = $this->client->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck->toToolResult();
        }

        $userId = $this->resolveAuthenticatedUserId($args);

        if ($userId instanceof ToolResult) {
            return $userId;
        }

        return $this->client->post("/users/{$userId}/likes", ['tweet_id' => $tweetId])
            ->toToolResultWith("Liked tweet {$tweetId}.");
    }

    /**
     * @param array<string, mixed> $args
     */
    private function unlikeTweet(array $args): ToolResult
    {
        $tweetId = trim((string) ($args['tweet_id'] ?? ''));

        if ($tweetId === '') {
            return ToolResult::error('Parameter "tweet_id" is required for unlike action.');
        }

        $oauthCheck = $this->client->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck->toToolResult();
        }

        $userId = $this->resolveAuthenticatedUserId($args);

        if ($userId instanceof ToolResult) {
            return $userId;
        }

        return $this->client->delete("/users/{$userId}/likes/{$tweetId}")
            ->toToolResultWith("Unliked tweet {$tweetId}.");
    }

    /**
     * @param array<string, mixed> $args
     */
    private function likedTweets(array $args): ToolResult
    {
        $userId = trim((string) ($args['user_id'] ?? ''));

        if ($userId === '') {
            return ToolResult::error('Parameter "user_id" is required for liked_tweets action.');
        }

        $maxResults = (int) ($args['max_results'] ?? 10);

        return $this->client->get("/users/{$userId}/liked_tweets", [
            'max_results' => max(1, min(100, $maxResults)),
            'tweet.fields' => 'created_at,public_metrics,author_id,lang',
            'expansions' => 'author_id',
            'user.fields' => 'username,name,verified',
        ])->toToolResult();
    }

    /**
     * @param array<string, mixed> $args
     */
    private function likingUsers(array $args): ToolResult
    {
        $tweetId = trim((string) ($args['tweet_id'] ?? ''));

        if ($tweetId === '') {
            return ToolResult::error('Parameter "tweet_id" is required for liking_users action.');
        }

        return $this->client->get("/tweets/{$tweetId}/liking_users", [
            'user.fields' => 'username,name,verified,public_metrics',
        ])->toToolResult();
    }

    /**
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

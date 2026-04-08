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

/**
 * Read user tweet timelines and mentions via the X API v2.
 */
final readonly class TimelineTool
{
    public function __construct(private XClient $client) {}

    public function build(): ToolInterface
    {
        return new Tool(
            name: 'x_timeline',
            description: 'Read tweet timelines on X (Twitter) — get a user\'s recent tweets, their mentions, or the authenticated user\'s reverse-chronological home timeline.',
            parameters: [
                new EnumParameter(
                    'action',
                    'The timeline type to retrieve',
                    values: ['user_tweets', 'mentions', 'reverse_chronological'],
                    required: true,
                ),
                new StringParameter(
                    'user_id',
                    'The user ID (required for user_tweets and mentions)',
                ),
                new NumberParameter(
                    'max_results',
                    'Number of tweets to return (1-100, default 10)',
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
            'user_tweets' => $this->userTweets($args),
            'mentions' => $this->mentions($args),
            'reverse_chronological' => $this->reverseChronological($args),
            default => ToolResult::error("Unknown action: {$action}. Use: user_tweets, mentions, reverse_chronological"),
        };
    }

    /**
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    private function buildTimelineQuery(array $args): array
    {
        $query = [
            'tweet.fields' => 'created_at,public_metrics,author_id,conversation_id,lang',
            'expansions' => 'author_id',
            'user.fields' => 'username,name,verified',
        ];

        $maxResults = (int) ($args['max_results'] ?? 10);
        $query['max_results'] = max(1, min(100, $maxResults));

        $paginationToken = trim((string) ($args['pagination_token'] ?? ''));

        if ($paginationToken !== '') {
            $query['pagination_token'] = $paginationToken;
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $args
     */
    private function userTweets(array $args): ToolResult
    {
        $userId = trim((string) ($args['user_id'] ?? ''));

        if ($userId === '') {
            return ToolResult::error('Parameter "user_id" is required for user_tweets action.');
        }

        return $this->client->get("/users/{$userId}/tweets", $this->buildTimelineQuery($args))->toToolResult();
    }

    /**
     * @param array<string, mixed> $args
     */
    private function mentions(array $args): ToolResult
    {
        $userId = trim((string) ($args['user_id'] ?? ''));

        if ($userId === '') {
            return ToolResult::error('Parameter "user_id" is required for mentions action.');
        }

        return $this->client->get("/users/{$userId}/mentions", $this->buildTimelineQuery($args))->toToolResult();
    }

    /**
     * @param array<string, mixed> $args
     */
    private function reverseChronological(array $args): ToolResult
    {
        // Requires the authenticated user's ID
        $meResult = $this->client->get('/users/me');

        if (!$meResult->success) {
            return $meResult->toToolResult();
        }

        $userId = is_array($meResult->data) ? (string) ($meResult->data['id'] ?? '') : '';

        if ($userId === '') {
            return ToolResult::error('Could not determine authenticated user ID.');
        }

        return $this->client->get(
            "/users/{$userId}/reverse_chronological",
            $this->buildTimelineQuery($args),
        )->toToolResult();
    }
}

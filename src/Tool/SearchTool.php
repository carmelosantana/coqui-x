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
 * Search tweets and get tweet counts via the X API v2.
 */
final readonly class SearchTool
{
    public function __construct(private XClient $client) {}

    public function build(): ToolInterface
    {
        return new Tool(
            name: 'x_search',
            description: 'Search for tweets on X (Twitter) — find recent tweets matching a query, or get tweet volume counts. Supports the full X search query syntax including operators, hashtags, mentions, and filters.',
            parameters: [
                new EnumParameter(
                    'action',
                    'The search operation to perform',
                    values: ['recent', 'counts'],
                    required: true,
                ),
                new StringParameter(
                    'query',
                    'The search query (required) — supports X search operators: hashtags (#topic), mentions (@user), "exact phrase", from:user, to:user, is:retweet, has:media, has:links, lang:en, -exclude',
                    required: true,
                ),
                new NumberParameter(
                    'max_results',
                    'Number of results to return (10-100, default 10)',
                ),
                new EnumParameter(
                    'sort_order',
                    'Sort order for results',
                    values: ['recency', 'relevancy'],
                ),
                new StringParameter(
                    'next_token',
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
            'recent' => $this->searchRecent($args),
            'counts' => $this->searchCounts($args),
            default => ToolResult::error("Unknown action: {$action}. Use: recent, counts"),
        };
    }

    /**
     * @param array<string, mixed> $args
     */
    private function searchRecent(array $args): ToolResult
    {
        $query = trim((string) ($args['query'] ?? ''));

        if ($query === '') {
            return ToolResult::error('Parameter "query" is required for search.');
        }

        $params = [
            'query' => $query,
            'tweet.fields' => 'created_at,public_metrics,author_id,conversation_id,lang',
            'expansions' => 'author_id',
            'user.fields' => 'username,name,verified,public_metrics',
        ];

        $maxResults = (int) ($args['max_results'] ?? 10);
        $params['max_results'] = max(10, min(100, $maxResults));

        $sortOrder = trim((string) ($args['sort_order'] ?? ''));

        if ($sortOrder !== '') {
            $params['sort_order'] = $sortOrder;
        }

        $nextToken = trim((string) ($args['next_token'] ?? ''));

        if ($nextToken !== '') {
            $params['next_token'] = $nextToken;
        }

        return $this->client->get('/tweets/search/recent', $params)->toToolResult();
    }

    /**
     * @param array<string, mixed> $args
     */
    private function searchCounts(array $args): ToolResult
    {
        $query = trim((string) ($args['query'] ?? ''));

        if ($query === '') {
            return ToolResult::error('Parameter "query" is required for counts.');
        }

        return $this->client->get('/tweets/counts/recent', [
            'query' => $query,
        ])->toToolResult();
    }
}

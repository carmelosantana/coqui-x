<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitX\Tool;

use CarmeloSantana\PHPAgents\Contract\ToolInterface;
use CarmeloSantana\PHPAgents\Tool\Parameter\EnumParameter;
use CarmeloSantana\PHPAgents\Tool\Parameter\NumberParameter;
use CarmeloSantana\PHPAgents\Tool\Parameter\StringParameter;
use CarmeloSantana\PHPAgents\Tool\Tool;
use CarmeloSantana\PHPAgents\Tool\ToolResult;
use CarmeloSantana\CoquiToolkitX\Runtime\TweetSanitizer;
use CarmeloSantana\CoquiToolkitX\Runtime\XClient;
use CarmeloSantana\CoquiToolkitX\Runtime\XResult;

/**
 * Create, delete, reply, retweet, quote, and fetch tweets via the X API v2.
 */
final readonly class TweetTool
{
    public function __construct(private XClient $client) {}

    public function build(): ToolInterface
    {
        return new Tool(
            name: 'x_tweet',
            description: 'Manage tweets on X (Twitter) — create new tweets, delete tweets, reply to tweets, retweet, quote tweet, or fetch a specific tweet by ID.',
            parameters: [
                new EnumParameter(
                    'action',
                    'The operation to perform',
                    values: ['create', 'delete', 'reply', 'retweet', 'quote', 'get'],
                    required: true,
                ),
                new StringParameter(
                    'text',
                    'The tweet text content (required for create, reply, quote)',
                ),
                new StringParameter(
                    'tweet_id',
                    'The tweet ID (required for delete, reply, retweet, quote, get)',
                ),
                new StringParameter(
                    'poll_options',
                    'Comma-separated poll options (2-4 options, only for create)',
                ),
                new NumberParameter(
                    'poll_duration',
                    'Poll duration in minutes (max 10080 = 7 days, only for create with poll_options)',
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
            'create' => $this->createTweet($args),
            'delete' => $this->deleteTweet($args),
            'reply' => $this->replyToTweet($args),
            'retweet' => $this->retweet($args),
            'quote' => $this->quoteTweet($args),
            'get' => $this->getTweet($args),
            default => ToolResult::error("Unknown action: {$action}. Use: create, delete, reply, retweet, quote, get"),
        };
    }

    /**
     * @param array<string, mixed> $args
     */
    private function createTweet(array $args): ToolResult
    {
        $text = trim((string) ($args['text'] ?? ''));

        if ($text === '') {
            return ToolResult::error('Parameter "text" is required for create action.');
        }

        $sanitizeResult = TweetSanitizer::sanitize($text);

        if (!$sanitizeResult->safe) {
            return ToolResult::error($sanitizeResult->warningMessage());
        }

        $oauthCheck = $this->client->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck->toToolResult();
        }

        /** @var array<string, mixed> $body */
        $body = ['text' => $text];

        // Handle poll if provided
        $pollOptions = trim((string) ($args['poll_options'] ?? ''));

        if ($pollOptions !== '') {
            $options = array_map('trim', explode(',', $pollOptions));
            $options = array_filter($options, static fn(string $o): bool => $o !== '');

            if (count($options) < 2 || count($options) > 4) {
                return ToolResult::error('Polls require 2-4 options.');
            }

            $duration = (int) ($args['poll_duration'] ?? 1440);

            if ($duration < 1 || $duration > 10080) {
                return ToolResult::error('Poll duration must be between 1 and 10080 minutes.');
            }

            $body['poll'] = [
                'options' => array_values($options),
                'duration_minutes' => $duration,
            ];
        }

        return $this->client->post('/tweets', $body)->toToolResultWith('Tweet created.');
    }

    /**
     * @param array<string, mixed> $args
     */
    private function deleteTweet(array $args): ToolResult
    {
        $tweetId = trim((string) ($args['tweet_id'] ?? ''));

        if ($tweetId === '') {
            return ToolResult::error('Parameter "tweet_id" is required for delete action.');
        }

        $oauthCheck = $this->client->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck->toToolResult();
        }

        return $this->client->delete("/tweets/{$tweetId}")->toToolResultWith("Tweet {$tweetId} deleted.");
    }

    /**
     * @param array<string, mixed> $args
     */
    private function replyToTweet(array $args): ToolResult
    {
        $text = trim((string) ($args['text'] ?? ''));
        $tweetId = trim((string) ($args['tweet_id'] ?? ''));

        if ($text === '') {
            return ToolResult::error('Parameter "text" is required for reply action.');
        }

        if ($tweetId === '') {
            return ToolResult::error('Parameter "tweet_id" is required for reply action.');
        }

        $sanitizeResult = TweetSanitizer::sanitize($text);

        if (!$sanitizeResult->safe) {
            return ToolResult::error($sanitizeResult->warningMessage());
        }

        $oauthCheck = $this->client->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck->toToolResult();
        }

        $body = [
            'text' => $text,
            'reply' => ['in_reply_to_tweet_id' => $tweetId],
        ];

        return $this->client->post('/tweets', $body)->toToolResultWith("Replied to tweet {$tweetId}.");
    }

    /**
     * @param array<string, mixed> $args
     */
    private function retweet(array $args): ToolResult
    {
        $tweetId = trim((string) ($args['tweet_id'] ?? ''));

        if ($tweetId === '') {
            return ToolResult::error('Parameter "tweet_id" is required for retweet action.');
        }

        $oauthCheck = $this->client->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck->toToolResult();
        }

        // We need the authenticated user ID — fetch via /users/me
        $meResult = $this->client->get('/users/me');

        if (!$meResult->success) {
            return $meResult->toToolResult();
        }

        $userId = is_array($meResult->data) ? (string) ($meResult->data['id'] ?? '') : '';

        if ($userId === '') {
            return ToolResult::error('Could not determine authenticated user ID.');
        }

        return $this->client->post("/users/{$userId}/retweets", ['tweet_id' => $tweetId])
            ->toToolResultWith("Retweeted tweet {$tweetId}.");
    }

    /**
     * @param array<string, mixed> $args
     */
    private function quoteTweet(array $args): ToolResult
    {
        $text = trim((string) ($args['text'] ?? ''));
        $tweetId = trim((string) ($args['tweet_id'] ?? ''));

        if ($text === '') {
            return ToolResult::error('Parameter "text" is required for quote action.');
        }

        if ($tweetId === '') {
            return ToolResult::error('Parameter "tweet_id" is required for quote action.');
        }

        $sanitizeResult = TweetSanitizer::sanitize($text);

        if (!$sanitizeResult->safe) {
            return ToolResult::error($sanitizeResult->warningMessage());
        }

        $oauthCheck = $this->client->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck->toToolResult();
        }

        $body = [
            'text' => $text,
            'quote_tweet_id' => $tweetId,
        ];

        return $this->client->post('/tweets', $body)->toToolResultWith("Quote tweet posted.");
    }

    /**
     * @param array<string, mixed> $args
     */
    private function getTweet(array $args): ToolResult
    {
        $tweetId = trim((string) ($args['tweet_id'] ?? ''));

        if ($tweetId === '') {
            return ToolResult::error('Parameter "tweet_id" is required for get action.');
        }

        return $this->client->get("/tweets/{$tweetId}", [
            'tweet.fields' => 'created_at,public_metrics,author_id,conversation_id,in_reply_to_user_id,lang',
            'expansions' => 'author_id',
            'user.fields' => 'username,name,verified',
        ])->toToolResult();
    }
}

<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitX\Tool;

use CarmeloSantana\PHPAgents\Contract\ToolInterface;
use CarmeloSantana\PHPAgents\Tool\Parameter\EnumParameter;
use CarmeloSantana\PHPAgents\Tool\Parameter\StringParameter;
use CarmeloSantana\PHPAgents\Tool\Tool;
use CarmeloSantana\PHPAgents\Tool\ToolResult;
use CarmeloSantana\CoquiToolkitX\Runtime\XClient;

/**
 * Look up X (Twitter) users by ID, username, or get the authenticated user's profile.
 */
final readonly class UserTool
{
    public function __construct(private XClient $client) {}

    public function build(): ToolInterface
    {
        return new Tool(
            name: 'x_user',
            description: 'Look up X (Twitter) user profiles — get the authenticated user\'s profile (me), look up a user by ID, or look up a user by username.',
            parameters: [
                new EnumParameter(
                    'action',
                    'The lookup operation to perform',
                    values: ['me', 'get', 'by_username'],
                    required: true,
                ),
                new StringParameter(
                    'user_id',
                    'The user ID (required for get action)',
                ),
                new StringParameter(
                    'username',
                    'The username without @ (required for by_username action)',
                ),
            ],
            callback: fn(array $args): ToolResult => $this->execute($args),
        );
    }

    private const string USER_FIELDS = 'created_at,description,location,public_metrics,url,verified,profile_image_url,pinned_tweet_id';

    /**
     * @param array<string, mixed> $args
     */
    private function execute(array $args): ToolResult
    {
        $action = trim((string) ($args['action'] ?? ''));

        return match ($action) {
            'me' => $this->me(),
            'get' => $this->getUser($args),
            'by_username' => $this->byUsername($args),
            default => ToolResult::error("Unknown action: {$action}. Use: me, get, by_username"),
        };
    }

    private function me(): ToolResult
    {
        return $this->client->get('/users/me', [
            'user.fields' => self::USER_FIELDS,
        ])->toToolResult();
    }

    /**
     * @param array<string, mixed> $args
     */
    private function getUser(array $args): ToolResult
    {
        $userId = trim((string) ($args['user_id'] ?? ''));

        if ($userId === '') {
            return ToolResult::error('Parameter "user_id" is required for get action.');
        }

        return $this->client->get("/users/{$userId}", [
            'user.fields' => self::USER_FIELDS,
        ])->toToolResult();
    }

    /**
     * @param array<string, mixed> $args
     */
    private function byUsername(array $args): ToolResult
    {
        $username = trim((string) ($args['username'] ?? ''));

        if ($username === '') {
            return ToolResult::error('Parameter "username" is required for by_username action.');
        }

        // Strip leading @ if present
        $username = ltrim($username, '@');

        return $this->client->get("/users/by/username/{$username}", [
            'user.fields' => self::USER_FIELDS,
        ])->toToolResult();
    }
}

<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitX;

use CarmeloSantana\PHPAgents\Contract\ToolkitInterface;
use CarmeloSantana\CoquiToolkitX\Runtime\XClient;
use CarmeloSantana\CoquiToolkitX\Tool\BookmarkTool;
use CarmeloSantana\CoquiToolkitX\Tool\FollowerTool;
use CarmeloSantana\CoquiToolkitX\Tool\LikeTool;
use CarmeloSantana\CoquiToolkitX\Tool\MuteTool;
use CarmeloSantana\CoquiToolkitX\Tool\SearchTool;
use CarmeloSantana\CoquiToolkitX\Tool\TimelineTool;
use CarmeloSantana\CoquiToolkitX\Tool\TweetTool;
use CarmeloSantana\CoquiToolkitX\Tool\UserTool;

/**
 * X (Twitter) management toolkit for Coqui Bot.
 *
 * Provides 8 tools for comprehensive X API v2 interaction:
 * tweet management, timelines, search, user lookup, followers,
 * likes, bookmarks, and mutes.
 */
final class XToolkit implements ToolkitInterface
{
    private readonly XClient $client;

    public function __construct(?XClient $client = null)
    {
        $this->client = $client ?? XClient::fromEnv();
    }

    public function tools(): array
    {
        return [
            (new TweetTool($this->client))->build(),
            (new TimelineTool($this->client))->build(),
            (new SearchTool($this->client))->build(),
            (new UserTool($this->client))->build(),
            (new FollowerTool($this->client))->build(),
            (new LikeTool($this->client))->build(),
            (new BookmarkTool($this->client))->build(),
            (new MuteTool($this->client))->build(),
        ];
    }

    public function guidelines(): string
    {
        return <<<'GUIDELINES'
        <X-GUIDELINES>
        ## X (Twitter) Toolkit

        You have access to the X (Twitter) API v2 via 8 tools prefixed with `x_`.

        ### Tool Overview

        | Tool | Purpose | Auth |
        |------|---------|------|
        | `x_tweet` | Create, delete, reply, retweet, quote, fetch tweets | OAuth (write), Bearer (read) |
        | `x_timeline` | User tweets, mentions, reverse chronological home feed | Bearer |
        | `x_search` | Search recent tweets, get tweet volume counts | Bearer |
        | `x_user` | Look up users by ID, username, or get authenticated user | Bearer |
        | `x_follower` | List followers/following, follow/unfollow users | OAuth (write), Bearer (read) |
        | `x_like` | Like/unlike tweets, list liked tweets and liking users | OAuth (write), Bearer (read) |
        | `x_bookmark` | List/add/remove bookmarked tweets | OAuth (all) |
        | `x_mute` | List/mute/unmute users | OAuth (all) |

        ### Authentication Modes

        - **Bearer Token** (`X_BEARER_TOKEN`): Required. Enables all read-only operations.
        - **OAuth 1.0a** (`X_CONSUMER_KEY`, `X_CONSUMER_SECRET`, `X_ACCESS_TOKEN`, `X_ACCESS_TOKEN_SECRET`): Optional. Required for write operations (posting, liking, following, bookmarking, muting).

        If a write operation is attempted without OAuth credentials, the tool returns an error listing exactly which credentials are missing and how to set them.

        ### Common Workflows

        **Post a tweet:**
        ```
        x_tweet(action: "create", text: "Hello from Coqui Bot!")
        ```

        **Search and engage with a topic:**
        ```
        x_search(action: "recent", query: "#php lang:en", max_results: 20, sort_order: "recency")
        x_tweet(action: "reply", tweet_id: "...", text: "Great point about PHP!")
        x_like(action: "like", tweet_id: "...")
        ```

        **Check timeline and mentions:**
        ```
        x_user(action: "me")  → get your user ID
        x_timeline(action: "mentions", user_id: "YOUR_ID", max_results: 20)
        x_timeline(action: "user_tweets", user_id: "SOME_USER_ID")
        ```

        **Manage followers:**
        ```
        x_user(action: "by_username", username: "example_user")  → get user ID
        x_follower(action: "follow", target_user_id: "...")
        x_follower(action: "followers", user_id: "YOUR_ID", max_results: 50)
        ```

        ### Search Query Syntax

        The `x_search` tool supports the full X search query syntax:
        - Keywords: `php agents`
        - Exact phrases: `"artificial intelligence"`
        - Hashtags: `#coqui #php`
        - Mentions: `@username`
        - From user: `from:username`
        - Language: `lang:en`
        - Has media: `has:media`, `has:images`, `has:videos`
        - Has links: `has:links`
        - Retweets: `is:retweet` or `-is:retweet`
        - Replies: `is:reply` or `-is:reply`
        - Exclude: `-keyword`
        - Combine with AND (space) and OR

        ### Important Notes

        - **Rate limits**: The X API enforces rate limits. If you receive a rate limit error, wait before retrying. GET endpoints: ~180 requests/15 min. POST/DELETE: ~50 requests/15 min.
        - **Tweet length**: Tweets are limited to 280 characters (or 25,000 for X Premium users).
        - **User IDs vs usernames**: Most endpoints require numeric user IDs. Use `x_user(action: "by_username")` to resolve a username to an ID first.
        - **Pagination**: Timeline and follower list results support pagination via `pagination_token`. The response includes a `next_token` in meta if more results are available.
        - **Content safety**: All outbound tweet content is automatically screened for prompt injection attempts before posting. Detected patterns are blocked with an explanation.
        - **Gated operations**: All write operations (create, delete, reply, retweet, quote, follow, unfollow, like, unlike, bookmark add/remove, mute/unmute) require user confirmation.
        </X-GUIDELINES>
        GUIDELINES;
    }
}

# Coqui Toolkit: X (Twitter)

Comprehensive X (Twitter) management toolkit for [Coqui](https://github.com/carmelosantana/coqui). Provides X API v2 access for tweets, timelines, search, user lookup, followers, likes, bookmarks, and mutes.

## Requirements

- PHP 8.4+
- Coqui Bot with `carmelosantana/php-agents` ^0.7

## Installation

```bash
composer require coquibot/coqui-toolkit-x
```

The toolkit is auto-discovered by Coqui via `extra.php-agents.toolkits` in `composer.json`.

## Configuration

### Required Credentials

| Credential | Description |
|-----------|-------------|
| `X_BEARER_TOKEN` | Bearer token for read operations (search, timelines, user lookup). Get one at [X Developer Dashboard](https://developer.x.com/en/portal/dashboard). |

### Optional Credentials (for write operations)

| Credential | Description |
|-----------|-------------|
| `X_CONSUMER_KEY` | API Key (Consumer Key) for write operations |
| `X_CONSUMER_SECRET` | API Secret (Consumer Secret) for write operations |
| `X_ACCESS_TOKEN` | User Access Token for write operations |
| `X_ACCESS_TOKEN_SECRET` | User Access Token Secret for write operations |

Set credentials via the Coqui `credentials` tool:

```
credentials(action: "set", key: "X_BEARER_TOKEN", value: "your-bearer-token")
credentials(action: "set", key: "X_CONSUMER_KEY", value: "your-consumer-key")
credentials(action: "set", key: "X_CONSUMER_SECRET", value: "your-consumer-secret")
credentials(action: "set", key: "X_ACCESS_TOKEN", value: "your-access-token")
credentials(action: "set", key: "X_ACCESS_TOKEN_SECRET", value: "your-access-token-secret")
```

### Authentication Modes

The toolkit supports two authentication modes:

- **Bearer Token** (read-only): With just `X_BEARER_TOKEN`, you can search tweets, read timelines, and look up users. This is the minimum required credential.
- **OAuth 1.0a** (read + write): With all 5 credentials, you can also post tweets, like, follow, bookmark, and mute. The toolkit uses HMAC-SHA1 signing for OAuth 1.0a.

### X Developer App Setup

1. Go to the [X Developer Portal](https://developer.x.com/en/portal/dashboard)
2. Create a Project and App
3. Enable Read and Write permissions
4. Generate your Bearer Token, Consumer Keys, and Access Tokens
5. Set them via the Coqui `credentials` tool

## Tools

### `x_tweet`

Create, delete, reply to, retweet, quote, and fetch tweets.

**Actions:** `create`, `delete`, `reply`, `retweet`, `quote`, `get`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | enum | yes | Operation to perform |
| `text` | string | for create/reply/quote | Tweet text content |
| `tweet_id` | string | for delete/reply/retweet/quote/get | Target tweet ID |
| `poll_options` | string | no | Comma-separated poll options (2-4) |
| `poll_duration` | number | no | Poll duration in minutes (max 10080) |

### `x_timeline`

Read user tweet timelines, mentions, and home feed.

**Actions:** `user_tweets`, `mentions`, `reverse_chronological`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | enum | yes | Timeline type |
| `user_id` | string | for user_tweets/mentions | Target user ID |
| `max_results` | number | no | Results per page (1-100, default 10) |
| `pagination_token` | string | no | Next page token |

### `x_search`

Search for recent tweets matching a query with full X search syntax support.

**Actions:** `recent`, `counts`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | enum | yes | Search operation |
| `query` | string | yes | Search query with operator support |
| `max_results` | number | no | Results to return (10-100, default 10) |
| `sort_order` | enum | no | `recency` or `relevancy` |
| `next_token` | string | no | Next page token |

**Search operators:** `#hashtag`, `@mention`, `"exact phrase"`, `from:user`, `to:user`, `lang:en`, `has:media`, `has:links`, `is:retweet`, `-exclude`

### `x_user`

Look up X user profiles by ID, username, or get the authenticated user.

**Actions:** `me`, `get`, `by_username`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | enum | yes | Lookup operation |
| `user_id` | string | for get | Target user ID |
| `username` | string | for by_username | Username (without @) |

### `x_follower`

List followers/following and follow/unfollow users.

**Actions:** `followers`, `following`, `follow`, `unfollow`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | enum | yes | Follower operation |
| `user_id` | string | for followers/following | Target user ID |
| `target_user_id` | string | for follow/unfollow | User to follow/unfollow |
| `max_results` | number | no | Results per page (1-1000, default 100) |
| `pagination_token` | string | no | Next page token |

### `x_like`

Like/unlike tweets, list liked tweets and users who liked a tweet.

**Actions:** `like`, `unlike`, `liked_tweets`, `liking_users`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | enum | yes | Like operation |
| `tweet_id` | string | for like/unlike/liking_users | Target tweet ID |
| `user_id` | string | for liked_tweets | Target user ID |
| `max_results` | number | no | Results per page |

### `x_bookmark`

Manage bookmarked tweets.

**Actions:** `list`, `add`, `remove`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | enum | yes | Bookmark operation |
| `tweet_id` | string | for add/remove | Target tweet ID |
| `max_results` | number | no | Results per page (1-100, default 10) |
| `pagination_token` | string | no | Next page token |

### `x_mute`

Manage muted users.

**Actions:** `list`, `mute`, `unmute`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | enum | yes | Mute operation |
| `target_user_id` | string | for mute/unmute | User to mute/unmute |
| `max_results` | number | no | Results per page (1-1000, default 100) |
| `pagination_token` | string | no | Next page token |

## Gated Operations

The following operations require user confirmation (or `--auto-approve`):

| Tool | Gated Actions |
|------|---------------|
| `x_tweet` | `create`, `delete`, `reply`, `retweet`, `quote` |
| `x_follower` | `follow`, `unfollow` |
| `x_like` | `like`, `unlike` |
| `x_bookmark` | `add`, `remove` |
| `x_mute` | `mute`, `unmute` |

## Content Safety

All outbound tweet content (create, reply, quote) is automatically screened for prompt injection patterns before reaching the X API:

- **Instruction overrides**: "ignore previous instructions", "you are now", "system prompt", "jailbreak"
- **Account manipulation**: "delete my account", "change my password", "revoke access"
- **Credential exfiltration**: "post my api key", "share the token", "leak credentials"

If a pattern is detected, the tweet is blocked with a clear explanation. Normal conversational content passes through without false positives.

## Usage Examples

```
# Post a tweet
x_tweet(action: "create", text: "Hello from Coqui Bot! 🤖")

# Post a tweet with a poll
x_tweet(action: "create", text: "What's your favorite language?", poll_options: "PHP,Python,TypeScript,Rust", poll_duration: 1440)

# Reply to a tweet
x_tweet(action: "reply", tweet_id: "1234567890", text: "Great thread!")

# Search for recent tweets about a topic
x_search(action: "recent", query: "#php #ai lang:en -is:retweet", max_results: 20, sort_order: "recency")

# Get tweet volume counts
x_search(action: "counts", query: "Coqui Bot")

# Look up a user by username
x_user(action: "by_username", username: "elonmusk")

# Get your own profile
x_user(action: "me")

# Read your timeline mentions
x_timeline(action: "mentions", user_id: "YOUR_USER_ID", max_results: 20)

# Follow a user
x_follower(action: "follow", target_user_id: "123456")

# List your followers
x_follower(action: "followers", user_id: "YOUR_USER_ID", max_results: 50)

# Like a tweet
x_like(action: "like", tweet_id: "1234567890")

# Bookmark a tweet
x_bookmark(action: "add", tweet_id: "1234567890")

# Mute a user
x_mute(action: "mute", target_user_id: "123456")
```

## Development

```bash
# Install dependencies
composer install

# Run tests
composer test

# Static analysis
composer analyse
```

## License

MIT

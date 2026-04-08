<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitX\Runtime;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * HTTP client for the X (Twitter) API v2.
 *
 * Supports two authentication modes:
 * - Bearer Token (read-only endpoints: search, timelines, user lookup)
 * - OAuth 1.0a HMAC-SHA1 (write endpoints: posting, liking, following)
 *
 * All credential values are resolved lazily from constructor args or getenv(),
 * enabling hot-reload after CredentialTool::set() without restarting.
 */
final class XClient
{
    private const string BASE_URL = 'https://api.x.com/2';
    private const int TIMEOUT = 30;
    private const int MAX_PAGINATE_PAGES = 10;

    private HttpClientInterface $httpClient;

    public function __construct(
        private readonly string $bearerToken = '',
        private readonly string $consumerKey = '',
        private readonly string $consumerSecret = '',
        private readonly string $accessToken = '',
        private readonly string $accessTokenSecret = '',
        ?HttpClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? HttpClient::create(['timeout' => self::TIMEOUT]);
    }

    /**
     * Factory — reads all credentials from environment variables.
     */
    public static function fromEnv(): self
    {
        return new self(
            bearerToken: self::envString('X_BEARER_TOKEN'),
            consumerKey: self::envString('X_CONSUMER_KEY'),
            consumerSecret: self::envString('X_CONSUMER_SECRET'),
            accessToken: self::envString('X_ACCESS_TOKEN'),
            accessTokenSecret: self::envString('X_ACCESS_TOKEN_SECRET'),
        );
    }

    /**
     * GET request using Bearer token authentication.
     *
     * @param array<string, mixed> $query
     */
    public function get(string $endpoint, array $query = []): XResult
    {
        return $this->requestWithBearer('GET', $endpoint, query: $query);
    }

    /**
     * POST request using OAuth 1.0a authentication.
     *
     * @param array<string, mixed> $body
     */
    public function post(string $endpoint, array $body = []): XResult
    {
        return $this->requestWithOAuth('POST', $endpoint, body: $body);
    }

    /**
     * PUT request using OAuth 1.0a authentication.
     *
     * @param array<string, mixed> $body
     */
    public function put(string $endpoint, array $body = []): XResult
    {
        return $this->requestWithOAuth('PUT', $endpoint, body: $body);
    }

    /**
     * DELETE request using OAuth 1.0a authentication.
     *
     * @param array<string, mixed> $query
     */
    public function delete(string $endpoint, array $query = []): XResult
    {
        return $this->requestWithOAuth('DELETE', $endpoint, query: $query);
    }

    /**
     * Auto-paginate a GET endpoint that uses next_token pagination.
     *
     * Accumulates all `data` arrays and returns a merged result.
     *
     * @param array<string, mixed> $query
     */
    public function paginate(string $endpoint, array $query = [], int $maxPages = self::MAX_PAGINATE_PAGES): XResult
    {
        /** @var array<int, mixed> $allData */
        $allData = [];
        $currentQuery = $query;
        $lastMeta = [];

        for ($page = 0; $page < $maxPages; $page++) {
            $result = $this->get($endpoint, $currentQuery);

            if (!$result->success) {
                return $result;
            }

            if (is_array($result->data)) {
                $allData = [...$allData, ...$result->data];
            }

            $lastMeta = $result->meta;
            $nextToken = $result->meta['next_token'] ?? null;

            if (!is_string($nextToken) || $nextToken === '') {
                break;
            }

            $currentQuery['pagination_token'] = $nextToken;
        }

        return new XResult(
            success: true,
            data: $allData,
            meta: $lastMeta,
        );
    }

    /**
     * Guard — returns the authenticated user ID or an error result if OAuth is not configured.
     */
    public function requireOAuthCredentials(): string|XResult
    {
        $missing = [];

        if ($this->resolveConsumerKey() === '') {
            $missing[] = 'X_CONSUMER_KEY';
        }
        if ($this->resolveConsumerSecret() === '') {
            $missing[] = 'X_CONSUMER_SECRET';
        }
        if ($this->resolveAccessToken() === '') {
            $missing[] = 'X_ACCESS_TOKEN';
        }
        if ($this->resolveAccessTokenSecret() === '') {
            $missing[] = 'X_ACCESS_TOKEN_SECRET';
        }

        if ($missing !== []) {
            return XResult::error(
                'OAuth credentials required for write operations. '
                . 'Missing: ' . implode(', ', $missing) . '. '
                . 'Set them via: credentials(action: "set", key: "KEY_NAME", value: "...")',
            );
        }

        return 'ok';
    }

    /**
     * Execute an HTTP request with Bearer token authentication.
     *
     * @param array<string, mixed> $query
     */
    private function requestWithBearer(string $method, string $endpoint, array $query = []): XResult
    {
        $token = $this->resolveBearerToken();

        if ($token === '') {
            return XResult::error('X_BEARER_TOKEN is not configured. Set it via: credentials(action: "set", key: "X_BEARER_TOKEN", value: "...")');
        }

        $url = self::BASE_URL . $endpoint;

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ];

        $filteredQuery = array_filter($query, static fn(mixed $v): bool => $v !== null && $v !== '');

        if ($filteredQuery !== []) {
            $options['query'] = $filteredQuery;
        }

        return $this->executeRequest($method, $url, $options);
    }

    /**
     * Execute an HTTP request with OAuth 1.0a HMAC-SHA1 authentication.
     *
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     */
    private function requestWithOAuth(
        string $method,
        string $endpoint,
        array $query = [],
        array $body = [],
    ): XResult {
        $oauthCheck = $this->requireOAuthCredentials();

        if ($oauthCheck instanceof XResult) {
            return $oauthCheck;
        }

        $url = self::BASE_URL . $endpoint;
        $authHeader = $this->buildOAuthHeader($method, $url, $query);

        $options = [
            'headers' => [
                'Authorization' => $authHeader,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        $filteredQuery = array_filter($query, static fn(mixed $v): bool => $v !== null && $v !== '');

        if ($filteredQuery !== []) {
            $options['query'] = $filteredQuery;
        }

        if ($body !== [] && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $options['json'] = $body;
        }

        return $this->executeRequest($method, $url, $options);
    }

    /**
     * Execute an HTTP request and parse the response into an XResult.
     *
     * @param array<string, mixed> $options
     */
    private function executeRequest(string $method, string $url, array $options): XResult
    {
        try {
            $response = $this->httpClient->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            $content = $response->getContent();

            if ($content === '') {
                return new XResult(success: true, data: null, statusCode: $statusCode);
            }

            /** @var mixed $decoded */
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($decoded)) {
                return new XResult(success: true, data: $decoded, statusCode: $statusCode);
            }

            // X API error format: {"errors": [...], "title": "...", "detail": "..."}
            if (isset($decoded['errors']) && is_array($decoded['errors'])) {
                return new XResult(
                    success: false,
                    data: null,
                    errors: $decoded['errors'],
                    statusCode: $statusCode,
                );
            }

            // X API top-level error: {"title": "...", "detail": "...", "type": "..."}
            if (isset($decoded['title']) && isset($decoded['detail']) && !isset($decoded['data'])) {
                return new XResult(
                    success: false,
                    data: null,
                    errors: [['message' => $decoded['detail'], 'title' => $decoded['title'], 'type' => $decoded['type'] ?? null]],
                    statusCode: $statusCode,
                );
            }

            return new XResult(
                success: true,
                data: $decoded['data'] ?? $decoded,
                meta: isset($decoded['meta']) && is_array($decoded['meta']) ? $decoded['meta'] : [],
                statusCode: $statusCode,
            );
        } catch (HttpExceptionInterface $e) {
            return $this->handleHttpException($e);
        } catch (\JsonException $e) {
            return XResult::error('Failed to parse X API response: ' . $e->getMessage());
        }
    }

    /**
     * Extract error details from an HTTP exception.
     */
    private function handleHttpException(HttpExceptionInterface $e): XResult
    {
        $statusCode = $e->getResponse()->getStatusCode();

        try {
            $body = $e->getResponse()->getContent(false);
            $decoded = json_decode($body, true);

            if (is_array($decoded)) {
                $errors = $decoded['errors'] ?? [['message' => $decoded['detail'] ?? $decoded['title'] ?? $body]];

                return new XResult(
                    success: false,
                    data: null,
                    errors: is_array($errors) ? $errors : [['message' => $body]],
                    statusCode: $statusCode,
                );
            }

            return XResult::error(mb_substr($body, 0, 500), $statusCode);
        } catch (\Throwable) {
            return XResult::error(sprintf('X API error (HTTP %d): %s', $statusCode, $e->getMessage()), $statusCode);
        }
    }

    /**
     * Build the OAuth 1.0a Authorization header using HMAC-SHA1 signing.
     *
     * @param array<string, mixed> $queryParams Query parameters included in the signature base
     */
    private function buildOAuthHeader(string $method, string $url, array $queryParams = []): string
    {
        $oauthParams = [
            'oauth_consumer_key' => $this->resolveConsumerKey(),
            'oauth_nonce' => bin2hex(random_bytes(16)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => (string) time(),
            'oauth_token' => $this->resolveAccessToken(),
            'oauth_version' => '1.0',
        ];

        // Merge OAuth params with query params for signature base string
        $signatureParams = [...$oauthParams];

        foreach ($queryParams as $key => $value) {
            if (is_scalar($value)) {
                $signatureParams[(string) $key] = (string) $value;
            }
        }

        // Sort parameters alphabetically by key
        ksort($signatureParams);

        // Build parameter string
        $paramParts = [];
        foreach ($signatureParams as $key => $value) {
            $paramParts[] = rawurlencode((string) $key) . '=' . rawurlencode((string) $value);
        }
        $paramString = implode('&', $paramParts);

        // Build signature base string: METHOD&url&params
        $baseString = strtoupper($method)
            . '&' . rawurlencode($url)
            . '&' . rawurlencode($paramString);

        // Build signing key: consumer_secret&token_secret
        $signingKey = rawurlencode($this->resolveConsumerSecret())
            . '&' . rawurlencode($this->resolveAccessTokenSecret());

        // Generate HMAC-SHA1 signature
        $signature = base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));

        $oauthParams['oauth_signature'] = $signature;

        // Build Authorization header
        $headerParts = [];
        foreach ($oauthParams as $key => $value) {
            $headerParts[] = rawurlencode($key) . '="' . rawurlencode($value) . '"';
        }

        return 'OAuth ' . implode(', ', $headerParts);
    }

    private function resolveBearerToken(): string
    {
        if ($this->bearerToken !== '') {
            return $this->bearerToken;
        }

        return self::envString('X_BEARER_TOKEN');
    }

    private function resolveConsumerKey(): string
    {
        if ($this->consumerKey !== '') {
            return $this->consumerKey;
        }

        return self::envString('X_CONSUMER_KEY');
    }

    private function resolveConsumerSecret(): string
    {
        if ($this->consumerSecret !== '') {
            return $this->consumerSecret;
        }

        return self::envString('X_CONSUMER_SECRET');
    }

    private function resolveAccessToken(): string
    {
        if ($this->accessToken !== '') {
            return $this->accessToken;
        }

        return self::envString('X_ACCESS_TOKEN');
    }

    private function resolveAccessTokenSecret(): string
    {
        if ($this->accessTokenSecret !== '') {
            return $this->accessTokenSecret;
        }

        return self::envString('X_ACCESS_TOKEN_SECRET');
    }

    private static function envString(string $key): string
    {
        $value = getenv($key);

        return $value !== false ? $value : '';
    }
}

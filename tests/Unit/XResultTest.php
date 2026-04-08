<?php

declare(strict_types=1);

use CarmeloSantana\PHPAgents\Enum\ToolResultStatus;
use CarmeloSantana\CoquiToolkitX\Runtime\XResult;

test('success result has correct state', function () {
    $result = new XResult(
        success: true,
        data: ['id' => '123', 'text' => 'Hello'],
        statusCode: 200,
    );

    expect($result->success)->toBeTrue();
    expect($result->data)->toBe(['id' => '123', 'text' => 'Hello']);
    expect($result->errors)->toBe([]);
    expect($result->statusCode)->toBe(200);
});

test('error result has correct state', function () {
    $result = new XResult(
        success: false,
        data: null,
        errors: [['message' => 'Not found', 'type' => 'not-found']],
        statusCode: 404,
    );

    expect($result->success)->toBeFalse();
    expect($result->data)->toBeNull();
    expect($result->statusCode)->toBe(404);
});

test('errorMessage formats single error', function () {
    $result = new XResult(
        success: false,
        data: null,
        errors: [['message' => 'Rate limit exceeded', 'type' => 'rate-limit']],
    );

    expect($result->errorMessage())->toBe('Rate limit exceeded [rate-limit]');
});

test('errorMessage formats multiple errors', function () {
    $result = new XResult(
        success: false,
        data: null,
        errors: [
            ['message' => 'First error'],
            ['message' => 'Second error', 'code' => 42],
        ],
    );

    expect($result->errorMessage())->toBe('First error; Second error [42]');
});

test('errorMessage handles empty errors', function () {
    $result = new XResult(success: false, data: null, errors: []);

    expect($result->errorMessage())->toBe('Unknown error');
});

test('errorMessage uses detail fallback', function () {
    $result = new XResult(
        success: false,
        data: null,
        errors: [['detail' => 'Detailed error info', 'type' => 'about:blank']],
    );

    expect($result->errorMessage())->toBe('Detailed error info [about:blank]');
});

test('toToolResult returns success with JSON data', function () {
    $result = new XResult(success: true, data: ['id' => '123']);
    $toolResult = $result->toToolResult();

    expect($toolResult->status)->toBe(ToolResultStatus::Success);
    expect($toolResult->content)->toContain('"id": "123"');
});

test('toToolResult returns error with message', function () {
    $result = new XResult(
        success: false,
        data: null,
        errors: [['message' => 'Forbidden']],
    );
    $toolResult = $result->toToolResult();

    expect($toolResult->status)->toBe(ToolResultStatus::Error);
    expect($toolResult->content)->toContain('Forbidden');
});

test('toToolResult handles null data', function () {
    $result = new XResult(success: true, data: null);
    $toolResult = $result->toToolResult();

    expect($toolResult->status)->toBe(ToolResultStatus::Success);
    expect($toolResult->content)->toBe('');
});

test('toToolResult handles string data', function () {
    $result = new XResult(success: true, data: 'plain text response');
    $toolResult = $result->toToolResult();

    expect($toolResult->status)->toBe(ToolResultStatus::Success);
    expect($toolResult->content)->toBe('plain text response');
});

test('toToolResultWith includes prefix', function () {
    $result = new XResult(success: true, data: ['liked' => true]);
    $toolResult = $result->toToolResultWith('Tweet liked.');

    expect($toolResult->status)->toBe(ToolResultStatus::Success);
    expect($toolResult->content)->toStartWith('Tweet liked.');
    expect($toolResult->content)->toContain('"liked": true');
});

test('toToolResultWith returns prefix only for null data', function () {
    $result = new XResult(success: true, data: null);
    $toolResult = $result->toToolResultWith('Operation completed.');

    expect($toolResult->status)->toBe(ToolResultStatus::Success);
    expect($toolResult->content)->toBe('Operation completed.');
});

test('toToolResultWith returns error for failed result', function () {
    $result = new XResult(
        success: false,
        data: null,
        errors: [['message' => 'Unauthorized']],
    );
    $toolResult = $result->toToolResultWith('Should not see this.');

    expect($toolResult->status)->toBe(ToolResultStatus::Error);
    expect($toolResult->content)->toContain('Unauthorized');
    expect($toolResult->content)->not->toContain('Should not see this');
});

test('error factory creates error result', function () {
    $result = XResult::error('Something went wrong', 500);

    expect($result->success)->toBeFalse();
    expect($result->data)->toBeNull();
    expect($result->errors)->toBe([['message' => 'Something went wrong']]);
    expect($result->statusCode)->toBe(500);
});

test('meta is preserved', function () {
    $result = new XResult(
        success: true,
        data: [['id' => '1']],
        meta: ['next_token' => 'abc123', 'result_count' => 1],
    );

    expect($result->meta)->toBe(['next_token' => 'abc123', 'result_count' => 1]);
});

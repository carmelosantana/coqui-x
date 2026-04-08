<?php

declare(strict_types=1);

use CarmeloSantana\PHPAgents\Contract\ToolInterface;
use CarmeloSantana\PHPAgents\Contract\ToolkitInterface;
use CarmeloSantana\CoquiToolkitX\XToolkit;

test('toolkit implements ToolkitInterface', function () {
    $toolkit = new XToolkit();

    expect($toolkit)->toBeInstanceOf(ToolkitInterface::class);
});

test('tools returns all 8 tools', function () {
    $toolkit = new XToolkit();

    expect($toolkit->tools())->toHaveCount(8);
});

test('each tool implements ToolInterface', function () {
    $toolkit = new XToolkit();

    foreach ($toolkit->tools() as $tool) {
        expect($tool)->toBeInstanceOf(ToolInterface::class);
    }
});

test('tool names are unique', function () {
    $toolkit = new XToolkit();
    $names = array_map(fn(ToolInterface $tool) => $tool->name(), $toolkit->tools());

    expect($names)->toHaveCount(count(array_unique($names)));
});

test('all tool names start with x_', function () {
    $toolkit = new XToolkit();

    foreach ($toolkit->tools() as $tool) {
        expect($tool->name())->toStartWith('x_');
    }
});

test('expected tool names are registered', function () {
    $toolkit = new XToolkit();
    $names = array_map(fn(ToolInterface $tool) => $tool->name(), $toolkit->tools());

    $expected = [
        'x_tweet',
        'x_timeline',
        'x_search',
        'x_user',
        'x_follower',
        'x_like',
        'x_bookmark',
        'x_mute',
    ];

    foreach ($expected as $name) {
        expect($names)->toContain($name);
    }
});

test('each tool produces a valid function schema', function () {
    $toolkit = new XToolkit();

    foreach ($toolkit->tools() as $tool) {
        $schema = $tool->toFunctionSchema();

        expect($schema)->toBeArray()
            ->toHaveKey('type')
            ->toHaveKey('function');

        expect($schema['type'])->toBe('function');
        expect($schema['function'])->toHaveKey('name');
        expect($schema['function'])->toHaveKey('description');
        expect($schema['function'])->toHaveKey('parameters');
        expect($schema['function']['name'])->toBe($tool->name());
        expect($schema['function']['description'])->toBeString()->not->toBeEmpty();
    }
});

test('guidelines contain XML tags', function () {
    $toolkit = new XToolkit();
    $guidelines = $toolkit->guidelines();

    expect($guidelines)->toContain('<X-GUIDELINES>');
    expect($guidelines)->toContain('</X-GUIDELINES>');
});

test('guidelines mention all tool names', function () {
    $toolkit = new XToolkit();
    $guidelines = $toolkit->guidelines();

    foreach ($toolkit->tools() as $tool) {
        expect($guidelines)->toContain($tool->name());
    }
});

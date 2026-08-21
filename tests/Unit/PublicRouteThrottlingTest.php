<?php

use Symfony\Component\Process\Process;

it('rate limits public news subscription writes', function () {
    $root = dirname(__DIR__, 2);
    $process = new Process([
        PHP_BINARY,
        $root.'/artisan',
        'route:list',
        '--name=news.subscribe',
        '--json',
    ], $root);
    $process->setTimeout(60);
    $process->mustRun();

    $route = collect(json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR))
        ->firstWhere('name', 'news.subscribe');

    expect($route)->not->toBeNull()
        ->and($route['method'])->toBe('POST')
        ->and($route['middleware'])->toContain('throttle:10,1');
});

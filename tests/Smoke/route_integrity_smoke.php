<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$names = [];
$signatures = [];
$failures = [];
$resolvedControllers = [];
$routeCount = 0;

foreach (app('router')->getRoutes() as $route) {
    $routeCount++;
    $name = $route->getName();
    $signature = implode('|', $route->methods()).' '.$route->uri();

    if ($name !== null && $name !== '') {
        if (isset($names[$name])) {
            $failures[] = "Duplicate route name [{$name}].";
        }

        $names[$name] = true;
    }

    if (isset($signatures[$signature])) {
        $failures[] = "Duplicate route signature [{$signature}].";
    }

    $signatures[$signature] = true;

    $action = $route->getActionName();
    if ($action === 'Closure') {
        continue;
    }

    if (! str_contains($action, '@')) {
        if (! class_exists($action) || ! method_exists($action, '__invoke')) {
            $failures[] = "Invalid invokable route action [{$action}] for [{$signature}].";
        } elseif (! (new ReflectionMethod($action, '__invoke'))->isPublic()) {
            $failures[] = "Non-public invokable route action [{$action}] for [{$signature}].";
        }

        continue;
    }

    [$controller, $method] = explode('@', $action, 2);
    if (! class_exists($controller)) {
        $failures[] = "Missing controller [{$controller}] for [{$signature}].";
        continue;
    }

    if (! isset($resolvedControllers[$controller])) {
        try {
            app()->make($controller);
            $resolvedControllers[$controller] = true;
        } catch (Throwable $exception) {
            $failures[] = "Unresolvable controller [{$controller}]: {$exception->getMessage()}";
            $resolvedControllers[$controller] = false;
        }
    }

    if (! method_exists($controller, $method)) {
        $failures[] = "Missing action [{$controller}@{$method}] for [{$signature}].";
        continue;
    }

    if (! (new ReflectionMethod($controller, $method))->isPublic()) {
        $failures[] = "Non-public action [{$controller}@{$method}] for [{$signature}].";
    }
}

if ($failures !== []) {
    throw new RuntimeException(implode(PHP_EOL, $failures));
}

echo "Route integrity smoke passed: {$routeCount} routes checked.\n";

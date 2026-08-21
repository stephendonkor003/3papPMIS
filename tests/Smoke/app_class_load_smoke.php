<?php

$loader = require __DIR__.'/../../vendor/autoload.php';
$appPath = realpath(__DIR__.'/../../app').DIRECTORY_SEPARATOR;
$failures = [];
$checked = 0;

foreach ($loader->getClassMap() as $class => $path) {
    $resolvedPath = realpath($path);

    if ($resolvedPath === false || ! str_starts_with($resolvedPath, $appPath)) {
        continue;
    }

    $checked++;

    try {
        $loaded = class_exists($class)
            || interface_exists($class)
            || trait_exists($class)
            || (function_exists('enum_exists') && enum_exists($class));

        if (! $loaded) {
            $failures[] = "Unable to autoload [{$class}] from [{$resolvedPath}].";
        }
    } catch (Throwable $exception) {
        $failures[] = "Unable to autoload [{$class}]: {$exception->getMessage()}";
    }
}

if ($failures !== []) {
    throw new RuntimeException(implode(PHP_EOL, $failures));
}

echo "Application class-load smoke passed: {$checked} classes checked.\n";

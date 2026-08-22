<?php

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(ConsoleKernel::class)->bootstrap();
$kernel = $app->make(HttpKernel::class);
$locales = ['en', 'fr', 'ar', 'pt', 'es', 'sw'];
$domains = ['procurement_public', 'news_public', 'world_indicators', 'events', 'gallery'];

$flatten = static function (array $values, string $prefix = '') use (&$flatten): array {
    $keys = [];

    foreach ($values as $key => $value) {
        $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
        is_array($value) ? array_push($keys, ...$flatten($value, $path)) : $keys[] = $path;
    }

    return $keys;
};

foreach ($domains as $domain) {
    $referenceKeys = $flatten(require base_path("lang/en/{$domain}.php"));

    foreach ($locales as $locale) {
        $localizedKeys = $flatten(require base_path("lang/{$locale}/{$domain}.php"));

        if ($localizedKeys !== $referenceKeys) {
            fwrite(STDERR, "{$locale} {$domain} catalog key structure differs from English.\n");
            exit(1);
        }
    }
}

foreach ($locales as $locale) {
    $direction = config("localization.supported.{$locale}.direction", 'ltr');
    $pages = [
        '/public/procurement' => (require base_path("lang/{$locale}/procurement_public.php"))['hero_title'],
        '/news' => (require base_path("lang/{$locale}/news_public.php"))['hero']['title'],
        '/world-indicators-performance' => (require base_path("lang/{$locale}/world_indicators.php"))['map']['title'],
        '/events' => (require base_path("lang/{$locale}/events.php"))['hero']['title'],
        '/gallery' => (require base_path("lang/{$locale}/gallery.php"))['hero']['title'],
    ];

    foreach ($pages as $path => $marker) {
        $request = Request::create("{$path}?lang={$locale}", 'GET', [], [], [], [
            'HTTP_HOST' => '127.0.0.1:8000',
            'SERVER_PORT' => 8000,
        ]);
        $response = $kernel->handle($request);
        $content = (string) $response->getContent();

        if ($response->getStatusCode() !== 200
            || ! str_contains($content, "<html lang=\"{$locale}\" dir=\"{$direction}\">")
            || ! str_contains($content, e($marker))) {
            fwrite(STDERR, "{$path} failed its {$locale} localized render check (HTTP {$response->getStatusCode()}).\n");
            exit(1);
        }

        if ($locale === 'ar' && ! str_contains($content, 'assets/css/rtl.css')) {
            fwrite(STDERR, "{$path} did not load RTL styles for Arabic.\n");
            exit(1);
        }
    }
}

echo "PUBLIC_LOCALIZED_DESTINATIONS_OK\n";

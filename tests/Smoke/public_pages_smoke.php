<?php

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(ConsoleKernel::class)->bootstrap();

$paths = [
    '/' => ['3PAP PMIS', 'Portfolio Management Information System', 'Access PMIS'],
    '/login' => ['3PAP PMIS', 'Welcome back'],
    '/forgot-password' => ['3PAP PMIS', 'Forgot password?'],
    '/events' => ['Events &amp; Webinars', 'Upcoming public sessions', '3PAP PMIS'],
    '/callforproposal' => ['Opportunities', 'No open calls', '3PAP PMIS'],
    '/faq' => ['Frequently asked questions', 'Understanding PMIS', '3PAP PMIS'],
    '/news' => ['News &amp; Updates', 'Subscribe to 3PAP PMIS News'],
    '/careers' => ['Build Accountable Delivery With 3PAP PMIS'],
    '/gallery' => ['Media, insights, and delivery stories.', 'Portfolio insights'],
    '/grievances/submit' => ['Speak up. We are listening.', 'Submit anonymously', 'What happens next?', 'Your identity will be hidden'],
    '/public/procurement' => ['Open Procurement Opportunities', '3PAP PMIS'],
    '/impact-map' => ['3PAP PMIS Portfolio Impact Analytics', 'Strategic Framework'],
    '/world-indicators-performance' => ['3PAP PMIS', 'Performance'],
    '/discussion/current' => ['Current Insights', '3PAP PMIS'],
    '/discussion/thematic-areas' => ['Capability Areas', '3PAP PMIS'],
    '/discussion/join' => ['Connect With 3PAP', '3PAP PMIS'],
];

foreach ($paths as $path => $needles) {
    $request = Request::create($path, 'GET', [], [], [], [
        'HTTP_HOST' => '127.0.0.1:8000',
        'SERVER_PORT' => 8000,
    ]);
    $response = $app->make(HttpKernel::class)->handle($request);
    $content = (string) $response->getContent();

    if ($response->getStatusCode() !== 200) {
        fwrite(STDERR, "Expected 200 from {$path}, got {$response->getStatusCode()}.\n");
        fwrite(STDERR, substr($content, 0, 1200)."\n");
        exit(1);
    }

    foreach ($needles as $needle) {
        if (! str_contains($content, $needle)) {
            fwrite(STDERR, "Public page {$path} is missing expected text: {$needle}\n");
            exit(1);
        }
    }

    $visibleContent = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $content) ?? $content;
    $visibleContent = html_entity_decode(strip_tags($visibleContent), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    if (preg_match('/African\s+Union|African\s+Think\s+Tank\s+Platform|\bAUC\b|\bATTP\b|Agenda\s+2063|attpinfo@/i', $visibleContent, $legacyMatch)) {
        fwrite(STDERR, "Public page {$path} still exposes visible legacy branding: {$legacyMatch[0]}\n");
        exit(1);
    }

    foreach (['assets/images/au.png', 'assets/images/attp-logo.jpeg', 'attpinfo@africanunion.org'] as $legacyBrand) {
        if (str_contains($content, $legacyBrand)) {
            fwrite(STDERR, "Public page {$path} still exposes legacy branding: {$legacyBrand}\n");
            exit(1);
        }
    }
}

$homeRequest = Request::create('/', 'GET', [], [], [], [
    'HTTP_HOST' => '127.0.0.1:8000',
    'SERVER_PORT' => 8000,
]);
$homeResponse = $app->make(HttpKernel::class)->handle($homeRequest);
$home = (string) $homeResponse->getContent();

if (! str_contains($home, 'http://127.0.0.1:8000/assets/style.css')) {
    fwrite(STDERR, "Homepage assets are not using APP_URL http://127.0.0.1:8000.\n");
    exit(1);
}

echo "PUBLIC_PAGES_OK\n";

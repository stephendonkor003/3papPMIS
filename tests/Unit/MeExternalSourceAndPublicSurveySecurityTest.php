<?php

use App\Http\Controllers\MeDataSourceController;
use App\Support\MeSurvey;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Process\Process;

function surveySecurityDataSourceController(): MeDataSourceController
{
    return new class extends MeDataSourceController
    {
        public array $testAddresses = ['93.184.216.34'];

        public array $testAllowedHosts = ['feed.example.test'];

        public string $testLocalRoot = '';

        public int $testMaxBytes = 1024;

        public function inspectRemote(string $url): array
        {
            return $this->validateRemoteSource($url);
        }

        public function inspectLocal(string $path): ?array
        {
            return $this->resolveLocalSourceNode($path);
        }

        public function inspectRemoteBody(HttpResponse $response): string
        {
            return $this->readLimitedRemoteBody($response);
        }

        protected function resolveRemoteHostAddresses(string $host): array
        {
            return $this->testAddresses;
        }

        protected function configuredRemoteAllowedHosts(): array
        {
            return $this->testAllowedHosts;
        }

        protected function configuredLocalSourceRoot(): ?string
        {
            $root = realpath($this->testLocalRoot);

            return $root ?: null;
        }

        protected function configuredRemoteMaxBytes(): int
        {
            return $this->testMaxBytes;
        }
    };
}

function surveySecurityUploadedFile(string $name, string $contents): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'survey-security-');
    file_put_contents($path, $contents);

    return new UploadedFile($path, $name, null, null, true);
}

it('accepts only the documented survey attachment extension and detected MIME combinations', function () {
    $png = surveySecurityUploadedFile(
        'evidence.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2pZkAAAAASUVORK5CYII=')
    );
    $script = surveySecurityUploadedFile('payload.php', '<?php echo "unsafe";');
    $fakePdf = surveySecurityUploadedFile('report.pdf', '<script>alert(1)</script>');

    expect(MeSurvey::surveyUploadErrors($png))->toBe([])
        ->and(MeSurvey::surveyUploadErrors($script))->toContain('Allowed file types are PDF, PNG, JPG, CSV, DOCX, and XLSX.')
        ->and(MeSurvey::surveyUploadErrors($fakePdf))->toContain('The file contents do not match the selected file type.')
        ->and(MeSurvey::SURVEY_UPLOAD_MAX_BYTES)->toBe(10 * 1024 * 1024)
        ->and(MeSurvey::SURVEY_UPLOAD_ACCEPT)->toBe('.pdf,.png,.jpg,.jpeg,.csv,.docx,.xlsx');
});

it('stores survey uploads privately with randomized paths and exposes only a scoped download', function () {
    $root = dirname(__DIR__, 2);
    $publicController = file_get_contents($root.'/app/Http/Controllers/PublicIndicatorSurveyController.php');
    $surveyController = file_get_contents($root.'/app/Http/Controllers/MeSurveyController.php');
    $routes = file_get_contents($root.'/routes/web.php');

    expect($publicController)
        ->toContain("Str::random(40).'.'.\$extension")
        ->toContain("'me-survey-uploads/'.Str::uuid()")
        ->toContain("'local'")
        ->toContain("'disk' => 'local'")
        ->not->toContain("Storage::disk('public')->url")
        ->not->toContain("'url' =>")
        ->and($surveyController)
        ->toContain("'downloadAttachment'")
        ->toContain("'Content-Type' => 'application/octet-stream'")
        ->toContain("'X-Content-Type-Options' => 'nosniff'")
        ->and($routes)
        ->toContain("->name('responses.attachments.download')");
});

it('denies remote sync unless an exact HTTPS host resolves exclusively to public addresses', function () {
    $controller = surveySecurityDataSourceController();

    expect($controller->inspectRemote('https://feed.example.test/data.json'))
        ->toMatchArray(['host' => 'feed.example.test']);

    foreach ([
        'http://feed.example.test/data.json',
        'https://user:secret@feed.example.test/data.json',
        'https://feed.example.test:8443/data.json',
        "https://feed.example.test/data.json\r\nX-Test: injected",
    ] as $url) {
        expect(fn () => $controller->inspectRemote($url))->toThrow(RuntimeException::class);
    }

    $controller->testAllowedHosts = [];
    expect(fn () => $controller->inspectRemote('https://feed.example.test/data.json'))
        ->toThrow(RuntimeException::class, 'not in the configured allowlist');

    $controller->testAllowedHosts = ['feed.example.test'];
    $controller->testAddresses = ['93.184.216.34', '127.0.0.1'];
    expect(fn () => $controller->inspectRemote('https://feed.example.test/data.json'))
        ->toThrow(RuntimeException::class, 'private, local, or reserved');
});

it('caps streamed remote response bodies before they can be buffered without limit', function () {
    $controller = surveySecurityDataSourceController();

    expect($controller->inspectRemoteBody(new HttpResponse(new PsrResponse(200, [], str_repeat('a', 1024)))))
        ->toHaveLength(1024);

    expect(fn () => $controller->inspectRemoteBody(
        new HttpResponse(new PsrResponse(200, [], str_repeat('a', 1025)))
    ))->toThrow(RuntimeException::class, 'exceeds the configured size limit');

    expect(fn () => $controller->inspectRemoteBody(
        new HttpResponse(new PsrResponse(200, ['Content-Length' => '2048'], ''))
    ))->toThrow(RuntimeException::class, 'exceeds the configured size limit');
});

it('confines persisted local sources to canonical non-traversing paths under the import root', function () {
    $base = sys_get_temp_dir().DIRECTORY_SEPARATOR.'me-source-security-'.bin2hex(random_bytes(6));
    $root = $base.DIRECTORY_SEPARATOR.'imports';
    mkdir($root, 0777, true);
    file_put_contents($root.DIRECTORY_SEPARATOR.'inside.csv', "period,actual\n2026,1\n");
    file_put_contents($base.DIRECTORY_SEPARATOR.'outside.csv', "period,actual\n2026,2\n");

    $controller = surveySecurityDataSourceController();
    $controller->testLocalRoot = $root;

    try {
        expect($controller->inspectLocal('inside.csv'))
            ->toMatchArray(['type' => 'file', 'path' => realpath($root.DIRECTORY_SEPARATOR.'inside.csv')])
            ->and($controller->inspectLocal('../outside.csv'))->toBeNull()
            ->and($controller->inspectLocal(realpath($root.DIRECTORY_SEPARATOR.'inside.csv')))->toBeNull()
            ->and($controller->inspectLocal($base.DIRECTORY_SEPARATOR.'outside.csv'))->toBeNull()
            ->and($controller->inspectLocal('file://'.str_replace('\\', '/', realpath($root.DIRECTORY_SEPARATOR.'inside.csv'))))->toBeNull()
            ->and($controller->inspectLocal('php://filter/resource=inside.csv'))->toBeNull();
    } finally {
        unlink($root.DIRECTORY_SEPARATOR.'inside.csv');
        unlink($base.DIRECTORY_SEPARATOR.'outside.csv');
        rmdir($root);
        rmdir($base);
    }
});

it('throttles both public survey routes and protects attachment downloads with reviewer permission', function () {
    $root = dirname(__DIR__, 2);
    $process = new Process([PHP_BINARY, $root.'/artisan', 'route:list', '--json'], $root);
    $process->setTimeout(60);
    $process->mustRun();

    $routes = collect(json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR));
    $show = $routes->firstWhere('name', 'public.me.indicators.surveys.show');
    $submit = $routes->firstWhere('name', 'public.me.indicators.surveys.submit');
    $download = $routes->firstWhere('name', 'budget.me.surveys.responses.attachments.download');

    expect($show['middleware'] ?? [])->toContain('throttle:60,1')
        ->and($submit['middleware'] ?? [])->toContain('throttle:10,1')
        ->and($download['middleware'] ?? [])->toContain('auth')
        ->toContain('permission:me.configuration.view|me.configuration.manage');
});

it('keeps arbitrary project paths and redirecting remote requests out of the sync implementation', function () {
    $source = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/MeDataSourceController.php');

    expect($source)
        ->not->toContain('base_path(ltrim($value')
        ->not->toContain("storage_path('app/' . ltrim(\$value")
        ->not->toContain('Http::timeout(25)->get($sourceValue)')
        ->not->toContain("str_starts_with(strtolower(\$value), 'file://')")
        ->toContain('->withoutRedirecting()')
        ->toContain("constant('CURLOPT_RESOLVE')")
        ->toContain("'stream' => true")
        ->toContain('configuredLocalSourceRoot()');
});

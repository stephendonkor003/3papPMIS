<?php

use App\Http\Controllers\DataWarehouseController;
use Symfony\Component\Process\Process;

function dataWarehouseRegisteredRoutes(): array
{
    static $routes;

    if (is_array($routes)) {
        return $routes;
    }

    $root = dirname(__DIR__, 2);
    $process = new Process([PHP_BINARY, $root.'/artisan', 'route:list', '--json'], $root);
    $process->setTimeout(60);
    $process->mustRun();

    return $routes = collect(json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR))
        ->filter(fn (array $route): bool => str_starts_with((string) ($route['name'] ?? ''), 'data-warehouse.'))
        ->values()
        ->all();
}

it('requires audit permission on every data warehouse route', function () {
    $routes = dataWarehouseRegisteredRoutes();
    $unprotected = collect($routes)
        ->reject(fn (array $route): bool => in_array('permission:system.audit.view', $route['middleware'], true))
        ->pluck('name')
        ->all();

    expect($routes)->not->toBeEmpty()
        ->and($unprotected)->toBe([]);
});

it('accepts only documented upload extensions and server validated MIME types', function () {
    $root = dirname(__DIR__, 2);
    $reflection = new ReflectionClass(DataWarehouseController::class);
    $extensions = $reflection->getConstant('ALLOWED_UPLOAD_EXTENSIONS');
    $mimeTypes = $reflection->getConstant('ALLOWED_UPLOAD_MIME_TYPES');
    $controller = file_get_contents($root.'/app/Http/Controllers/DataWarehouseController.php');
    $form = file_get_contents($root.'/resources/views/data-warehouse/create.blade.php');
    $accept = '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.jpg,.jpeg,.png,.zip';

    expect($extensions)->toBe([
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'csv', 'txt', 'jpg', 'jpeg', 'png', 'zip',
    ])->and($mimeTypes)
        ->toContain('application/pdf', 'image/jpeg', 'image/png', 'application/zip')
        ->not->toContain('image/svg+xml', 'text/html', 'application/javascript')
        ->and($controller)
        ->toContain("'extensions:'.implode(',', self::ALLOWED_UPLOAD_EXTENSIONS)")
        ->toContain("'mimetypes:'.implode(',', self::ALLOWED_UPLOAD_MIME_TYPES)")
        ->toContain("storeAs('data-warehouse/'.now()->format('Y/m'), \$fileName, 'local')")
        ->toContain("'disk' => 'local'")
        ->toContain("'disk_column' => 'disk'")
        ->toContain("'disk' => \$file->disk ?: 'public'")
        ->toContain('$uploadedFile->getMimeType()')
        ->not->toContain('$uploadedFile->getClientMimeType()')
        ->and(substr_count($form, 'accept="'.$accept.'"'))->toBe(2)
        ->and($form)->not->toContain('other knowledge files');
});

it('rejects unsafe decoded file references and sanitizes download names', function () {
    $controller = new DataWarehouseController;
    $validate = new ReflectionMethod($controller, 'validatedFileReference');

    expect($validate->invoke($controller, [
        'disk' => 'public',
        'path' => 'data-warehouse/legacy report.pdf',
        'name' => "folder/legacy\r\nreport.pdf",
    ]))->toBe([
        'disk' => 'public',
        'path' => 'data-warehouse/legacy report.pdf',
        'name' => 'legacyreport.pdf',
    ]);

    foreach ([
        ['disk' => 's3', 'path' => 'data-warehouse/file.pdf'],
        ['disk' => 'local', 'path' => '../.env'],
        ['disk' => 'local', 'path' => 'data-warehouse/../../.env'],
        ['disk' => 'local', 'path' => '/absolute/file.pdf'],
        ['disk' => 'local', 'path' => 'C:\\private\\file.pdf'],
        ['disk' => 'local', 'path' => 'https://example.test/file.pdf'],
        ['disk' => 'local', 'path' => 'data-warehouse//file.pdf'],
        ['disk' => 'local', 'path' => "data-warehouse/file\0.pdf"],
        ['disk' => 'local'],
    ] as $payload) {
        expect($validate->invoke($controller, $payload))->toBeNull();
    }
});

it('inlines only conservative content types with private response headers', function () {
    $controller = new DataWarehouseController;
    $inline = new ReflectionMethod($controller, 'isSafeInlineMimeType');
    $headers = new ReflectionMethod($controller, 'secureFileHeaders');

    foreach ([
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/bmp',
        'image/tiff',
        'text/plain; charset=UTF-8',
        'text/csv',
    ] as $mimeType) {
        expect($inline->invoke($controller, $mimeType))->toBeTrue();
    }

    foreach ([
        'image/svg+xml',
        'text/html',
        'application/xhtml+xml',
        'application/javascript',
        'application/xml',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/zip',
        'application/octet-stream',
    ] as $mimeType) {
        expect($inline->invoke($controller, $mimeType))->toBeFalse();
    }

    expect($headers->invoke($controller))->toBe([
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
        'Expires' => '0',
        'X-Content-Type-Options' => 'nosniff',
    ]);
});

it('uses detected storage MIME and forces unsafe previews to download', function () {
    $source = file_get_contents(
        dirname(__DIR__, 2).'/app/Http/Controllers/DataWarehouseController.php'
    );
    $libraryMethod = substr(
        $source,
        strpos($source, 'public function libraryFile'),
        strpos($source, 'private function knowledgeModules') - strpos($source, 'public function libraryFile')
    );

    expect($libraryMethod)
        ->toContain('$this->safeMimeType($disk, $path)')
        ->toContain('! $this->isSafeInlineMimeType($mimeType)')
        ->toContain('->download($path, $name, $headers)')
        ->toContain("->response(\$path, \$name, \$headers, 'inline')")
        ->not->toContain("\$payload['mime_type']");

    expect($source)->toContain('$this->validatedFileReference($payload)');
});

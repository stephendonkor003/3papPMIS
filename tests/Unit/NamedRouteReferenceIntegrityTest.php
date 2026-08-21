<?php

use App\Http\Controllers\BudgetReportController;
use App\Http\Controllers\DepartmentController;
use App\Models\Program;
use Symfony\Component\Process\Process;

function attpRegisteredRoutes(): array
{
    static $routes;

    if (is_array($routes)) {
        return $routes;
    }

    $root = dirname(__DIR__, 2);
    $process = new Process([PHP_BINARY, $root.'/artisan', 'route:list', '--json'], $root);
    $process->setTimeout(60);
    $process->mustRun();

    return $routes = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
}

it('binds each repaired route to a public controller action', function () {
    $routes = collect(attpRegisteredRoutes());

    expect((new ReflectionMethod(DepartmentController::class, 'destroy'))->isPublic())->toBeTrue()
        ->and((new ReflectionMethod(BudgetReportController::class, 'exportProgramPdf'))->isPublic())->toBeTrue()
        ->and((new ReflectionMethod(BudgetReportController::class, 'exportProgramExcel'))->isPublic())->toBeTrue()
        ->and($routes->firstWhere('name', 'finance.departments.destroy')['action'] ?? null)
        ->toBe(DepartmentController::class.'@destroy')
        ->and($routes->firstWhere('name', 'budget.reports.export.pdf')['action'] ?? null)
        ->toBe(BudgetReportController::class.'@exportProgramPdf')
        ->and($routes->firstWhere('name', 'budget.reports.export.excel')['action'] ?? null)
        ->toBe(BudgetReportController::class.'@exportProgramExcel')
        ->and($routes->contains(fn (array $route): bool => str_ends_with(
            (string) ($route['action'] ?? ''),
            '@sendForRework'
        )))->toBeFalse();
});

it('delegates program exports through the existing scoped exporters', function () {
    $controller = new class extends BudgetReportController
    {
        public array $calls = [];

        public function exportPDF($type, $id)
        {
            $this->calls[] = ['pdf', $type, $id];

            return 'pdf-response';
        }

        public function exportExcel($type, $id)
        {
            $this->calls[] = ['excel', $type, $id];

            return 'excel-response';
        }
    };
    $program = (new Program)->forceFill(['id' => 'program-uuid']);

    expect($controller->exportProgramPdf($program))->toBe('pdf-response')
        ->and($controller->exportProgramExcel($program))->toBe('excel-response')
        ->and($controller->calls)->toBe([
            ['pdf', 'program', 'program-uuid'],
            ['excel', 'program', 'program-uuid'],
        ]);
});

it('checks every known department dependency before deletion', function () {
    $root = dirname(__DIR__, 2);
    $source = file_get_contents($root.'/app/Http/Controllers/DepartmentController.php');
    $deleteOffset = strpos($source, '$department->delete()');

    expect($source)->toContain('$department->programs()->exists()')
        ->and($source)->toContain('$department->programFundings()->exists()')
        ->and($source)->toContain("->where('department_id', \$department->getKey())")
        ->and(strpos($source, '$department->programs()->exists()'))->toBeLessThan($deleteOffset)
        ->and(strpos($source, '$department->programFundings()->exists()'))->toBeLessThan($deleteOffset)
        ->and(strpos($source, "->where('department_id', \$department->getKey())"))->toBeLessThan($deleteOffset);
});

it('redirects new procurement submissions through the surviving route name', function () {
    $root = dirname(__DIR__, 2);
    $source = file_get_contents($root.'/app/Http/Controllers/Procurement/FormSubmissionController.php');

    expect($source)->toContain("route('procurement.submissions.show', \$submission)")
        ->and($source)->not->toContain("route('submissions.show', \$submission)");
});

it('keeps excluded legacy modules outside the registered route collection', function () {
    $legacyControllers = [
        'App\\Http\\Controllers\\AssignmentController',
        'App\\Http\\Controllers\\BudgetAllocationController',
        'App\\Http\\Controllers\\CategoryController',
        'App\\Http\\Controllers\\CommitteeController',
        'App\\Http\\Controllers\\CommitteeMemberController',
        'App\\Http\\Controllers\\FinancialController',
        'App\\Http\\Controllers\\ProjectBudgetController',
        'App\\Http\\Controllers\\SiteVisitEvaluationController',
        'App\\Http\\Controllers\\ThinkDatasetController',
    ];
    $actions = collect(attpRegisteredRoutes())->pluck('action')->filter();
    $reachableLegacyControllers = collect($legacyControllers)
        ->filter(fn (string $controller): bool => $actions->contains(
            fn (string $action): bool => str_starts_with($action, $controller.'@')
        ))
        ->values()
        ->all();

    expect($reachableLegacyControllers)->toBe([]);
});

it('uses only registered literal route names in reachable application code', function () {
    $root = dirname(__DIR__, 2);
    $registeredNames = collect(attpRegisteredRoutes())
        ->pluck('name')
        ->filter()
        ->flip();

    /*
     * Keep exclusions path-scoped so dormant prototypes cannot mask a broken
     * link in reachable code. The first group has no registered controller
     * action. The second contains superseded view namespaces, and the final
     * group contains individual templates that no controller/include returns.
     */
    $legacyPaths = [
        // Unregistered controller/view modules.
        'app/Http/Controllers/AssignmentController.php',
        'app/Http/Controllers/CategoryController.php',
        'app/Http/Controllers/CommitteeController.php',
        'app/Http/Controllers/CommitteeMemberController.php',
        'app/Http/Controllers/FinancialController.php',
        'app/Http/Controllers/ProjectBudgetController.php',
        'app/Http/Controllers/SiteVisitEvaluationController.php',
        'app/Http/Controllers/ThinkDatasetController.php',
        'resources/views/assignments/',
        'resources/views/budget_allocations/',
        'resources/views/categories/',
        'resources/views/committee_members/',
        'resources/views/committees/',
        'resources/views/financial/',
        'resources/views/project_budget/',
        'resources/views/sitevisit/',
        'resources/views/think_datasets/',

        // Superseded by budget/programs, portfolio routes, and public/careers.
        'resources/views/programs/',
        'resources/views/budget/sectors/',
        'resources/views/careers/',

        // Unreturned legacy templates and an unreferenced delete partial.
        'resources/views/budget/activities/show.blade.php',
        'resources/views/budget/dashboard/summary.blade.php',
        'resources/views/budget/subactivities/show.blade.php',
        'resources/views/projects/partials/delete.blade.php',
        'resources/views/system/users/show.blade.php',
    ];

    $missing = [];
    $routePattern = '/(?<![A-Za-z0-9_])(?:route|to_route)\s*\(\s*([\'\"])([^\'\"]+)\1/';

    foreach (['app', 'resources', 'routes'] as $directory) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root.'/'.$directory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            $isLegacy = collect($legacyPaths)->contains(
                fn (string $path): bool => $relativePath === $path
                    || (str_ends_with($path, '/') && str_starts_with($relativePath, $path))
            );

            if ($isLegacy) {
                continue;
            }

            $source = file_get_contents($file->getPathname());
            $source = preg_replace([
                '/\{\{--.*?--\}\}/s',
                '/<!--.*?-->/s',
                '/\/\*.*?\*\//s',
                '/^\s*\/\/.*$/m',
            ], '', $source);

            preg_match_all($routePattern, $source, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[2] as [$name, $offset]) {
                $prefix = substr($source, max(0, $offset - 80), min(80, $offset));

                // route() on Request retrieves a route parameter; it does not
                // generate a URL and therefore is not a named-route reference.
                if (preg_match('/(?:\$request|request\(\))\s*->\s*route\s*\(\s*[\'\"]$/', $prefix)) {
                    continue;
                }

                if (! $registeredNames->has($name)) {
                    $line = substr_count(substr($source, 0, $offset), "\n") + 1;
                    $missing[] = $relativePath.':'.$line.' -> '.$name;
                }
            }
        }
    }

    expect($missing)->toBe([]);
});

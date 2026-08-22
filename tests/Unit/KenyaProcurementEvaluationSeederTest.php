<?php

it('runs the Kenya procurement lifecycle after the Kenya government dataset by default', function () {
    $source = file_get_contents(dirname(__DIR__, 2).'/database/seeders/DatabaseSeeder.php');

    expect($source)
        ->toContain('KenyaGovernmentSeeder::class')
        ->toContain('KenyaProcurementEvaluationSeeder::class');

    $governmentSeeder = strpos($source, 'KenyaGovernmentSeeder::class');
    $procurementSeeder = strpos($source, 'KenyaProcurementEvaluationSeeder::class');

    expect($governmentSeeder)->not->toBeFalse()
        ->and($procurementSeeder)->not->toBeFalse()
        ->and($governmentSeeder)->toBeLessThan($procurementSeeder);
});

it('seeds a deterministic modern procurement application and evaluation lifecycle', function () {
    $root = dirname(__DIR__, 2);
    $seederPath = $root.'/database/seeders/KenyaProcurementEvaluationSeeder.php';

    expect(file_exists($seederPath))->toBeTrue();

    $source = file_get_contents($seederPath);
    $catalogue = file_get_contents($root.'/database/seeders/data/kenya_procurement_evaluation.php');
    $lifecycleSource = $source."\n".$catalogue;

    expect($source)
        ->toContain('KenyaProcurementEvaluationDataCleaner::DATASET_TAG')
        ->toContain('KenyaProcurementEvaluationDataCleaner::PROCUREMENT_PREFIX')
        ->toContain("UUID_NAMESPACE = 'kenya-procurement-evaluation-demo-v1'")
        ->toContain('DB::transaction(function')
        ->toContain('KenyaGovernmentSeeder::PROJECT_PREFIX')
        ->toContain("DB::table('myb_projects")
        ->toContain('foreach ($projects->values() as $index => $project)');

    foreach ([
        'dynamic_forms',
        'dynamic_form_fields',
        'form_submissions',
        'form_submission_values',
        'prescreening_assignments',
        'prescreening_evaluations',
        'prescreening_results',
        'evaluation_assignments',
        'evaluation_submissions',
        'evaluation_section_scores',
        'evaluation_criteria_scores',
    ] as $table) {
        expect($source)->toContain("'{$table}'");
    }

    expect($lifecycleSource)
        ->toContain("'evaluation_phase'")
        ->toContain("'technical'")
        ->toContain("'financial'");
});

it('creates exactly one procurement lifecycle for every generated Kenya project', function () {
    $seederPath = dirname(__DIR__, 2).'/database/seeders/KenyaProcurementEvaluationSeeder.php';

    expect(file_exists($seederPath))->toBeTrue();

    $source = file_get_contents($seederPath);

    expect($source)
        ->toContain("->where('project.project_id', 'like', KenyaGovernmentSeeder::PROJECT_PREFIX.'%')")
        ->toContain('foreach ($projects->values() as $index => $project)')
        ->toContain("\$this->uuid('procurement', \$projectKey)")
        ->toContain("'procurements'");
});

it('links awards to the existing KES purchase order invoice and disbursement chain', function () {
    $seederPath = dirname(__DIR__, 2).'/database/seeders/KenyaProcurementEvaluationSeeder.php';

    expect(file_exists($seederPath))->toBeTrue();

    $source = file_get_contents($seederPath);

    foreach ([
        'procurement_purchase_orders',
        'procurement_invoices',
        'procurement_disbursements',
    ] as $table) {
        expect($source)->toContain("'{$table}'");
    }

    expect($source)
        ->toContain("'currency' => 'KES'")
        ->toContain("'procurement_id'")
        ->toContain("'awarded_submission_id'")
        ->toContain("'awarded_vendor_id'")
        ->toContain("'purchase_order_id'")
        ->toContain("'invoice_id'");
});

it('does not repopulate the superseded think tank procurement tables', function () {
    $seederPath = dirname(__DIR__, 2).'/database/seeders/KenyaProcurementEvaluationSeeder.php';

    expect(file_exists($seederPath))->toBeTrue();

    $source = file_get_contents($seederPath);
    $legacyTables = 'applicants|bids|financial_evaluations|committees';

    expect($source)
        ->not->toMatch('/DB::table\(\s*[\'\"](?:'.$legacyTables.')[\'\"]\s*\)\s*->\s*(?:insert|upsert|updateOrInsert)/s')
        ->not->toMatch('/\$this->(?:insert|upsert|updateOrInsert)\(\s*[\'\"](?:'.$legacyTables.')[\'\"]/s')
        ->not->toMatch('/use\s+App\\\\Models\\\\(?:Applicant|Bid|FinancialEvaluation|Committee);/');
});

it('adds an explicit nullable portfolio relationship to procurements', function () {
    $root = dirname(__DIR__, 2);
    $migrationPath = $root.'/database/migrations/2026_08_22_000001_add_portfolio_id_to_procurements_table.php';

    expect(file_exists($migrationPath))->toBeTrue();

    $migration = file_get_contents($migrationPath);
    $model = file_get_contents($root.'/app/Models/Procurement.php');

    expect($migration)
        ->toContain("Schema::table('procurements'")
        ->toContain("foreignUuid('portfolio_id')")
        ->toContain("constrained('myb_sectors')")
        ->toContain('->nullable()')
        ->toContain('->nullOnDelete()')
        ->toContain('HAVING COUNT(*) = 1');

    expect($model)
        ->toContain("'portfolio_id'")
        ->toContain("belongsTo(Sector::class, 'portfolio_id')");
});

it('prefers explicit procurement portfolio ids in every evaluation assignment controller', function () {
    $root = dirname(__DIR__, 2);
    $controllers = [
        'app/Http/Controllers/EvaluationAssignmentController.php',
        'app/Http/Controllers/ProcurementEvaluationController.php',
        'app/Http/Controllers/Procurement/EvaluationController.php',
    ];

    foreach ($controllers as $controller) {
        $source = file_get_contents($root.'/'.$controller);
        $methodStart = strpos($source, 'private function portfolioIdForProcurement(');

        expect($methodStart)->not->toBeFalse();

        $method = substr($source, $methodStart);
        $explicitPortfolio = strpos($method, 'if ($procurement->portfolio_id)');
        $governanceFallback = strpos($method, 'if (! $procurement->governance_node_id)');
        $sectorLookup = strpos($method, 'Sector::query()');

        expect($method)
            ->toContain('return (string) $procurement->portfolio_id;');

        expect($explicitPortfolio)->not->toBeFalse()
            ->and($governanceFallback)->not->toBeFalse()
            ->and($sectorLookup)->not->toBeFalse()
            ->and($explicitPortfolio)->toBeLessThan($governanceFallback)
            ->and($explicitPortfolio)->toBeLessThan($sectorLookup);
    }
});

<?php

use Database\Seeders\KenyaGovernmentSeeder;

it('ships a sourced Kenya government catalogue with broad national coverage', function () {
    $catalogue = require dirname(__DIR__, 2).'/database/seeders/data/kenya_government.php';
    $ministries = collect($catalogue['ministries']);
    $departments = $ministries->flatMap(fn (array $ministry): array => $ministry['departments']);
    $agencies = $departments->flatMap(fn (array $department): array => $department['agencies']);
    $executiveOffices = collect($catalogue['executive_offices']);
    $independentInstitutions = collect($catalogue['independent_institutions']);

    expect($catalogue['snapshot_date'])->toBe('2026-08-22')
        ->and($catalogue['sources'])->toHaveCount(4)
        ->and($executiveOffices)->toHaveCount(3)
        ->and($ministries->count())->toBeGreaterThanOrEqual(23)
        ->and($departments->count())->toBeGreaterThanOrEqual(50)
        ->and($agencies->count())->toBeGreaterThanOrEqual(100)
        ->and($agencies->unique()->count())->toBe($agencies->count())
        ->and($independentInstitutions->count())->toBeGreaterThanOrEqual(15);

    $ministries->each(function (array $ministry): void {
        expect($ministry['code'])->toStartWith('KE-')
            ->and(count($ministry['portfolios']))->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual(2)
            ->and($ministry['departments'])->not->toBeEmpty();
    });

    $departments->each(fn (array $department) => expect($department['code'])->toStartWith('KE-')
        ->and($department['agencies'])->toBeArray());
    $executiveOffices->merge($independentInstitutions)->each(
        fn (array $entity) => expect($entity['code'])->toStartWith('KE-')
    );
});

it('makes the Kenya dataset the only demo content in ordinary database seeding', function () {
    $source = file_get_contents(dirname(__DIR__, 2).'/database/seeders/DatabaseSeeder.php');

    expect($source)
        ->toContain('KenyaGovernmentSeeder::class')
        ->not->toContain('LegacyThinkTankProcurementLifecycleSeeder::class')
        ->not->toContain('LegacySiteVisitSeeder::class')
        ->not->toContain('AttpBudgetStructureSeeder::class')
        ->not->toContain('AttpWorkPlan2025Seeder::class')
        ->not->toContain('AttpMelFrameworkSeeder::class')
        ->not->toContain('DiscussionSeeder::class');
});

it('builds deterministic Kenya planning and complete KES execution lifecycles', function () {
    $source = file_get_contents(dirname(__DIR__, 2).'/database/seeders/KenyaGovernmentSeeder.php');

    expect(KenyaGovernmentSeeder::START_YEAR)->toBe(2026)
        ->and(KenyaGovernmentSeeder::END_YEAR)->toBe(2028)
        ->and($source)
        ->toContain('$projectCount = 3 +')
        ->toContain("'currency' => 'KES'")
        ->toContain("'work_plan_source' => \$workPlanSource")
        ->toContain("'project_allocations'")
        ->toContain("'activity_allocations'")
        ->toContain("'sub_activity_allocations'")
        ->toContain("'purchase_requests'")
        ->toContain("'commitments'")
        ->toContain("'work_plans'")
        ->toContain("'work_plan_item_reviews'")
        ->toContain("'invoices'")
        ->toContain("'purchase_orders'")
        ->toContain("'purchase_order_evidence'")
        ->toContain("'disbursements'")
        ->toContain("[\$office['name'].' Coordination and Delivery']")
        ->toContain("[\$department['name'].' Service Delivery']")
        ->toContain("[\$agencyName.' Service Delivery']")
        ->toContain("md5('kenya-government-demo-v1|'")
        ->toContain('DB::transaction(function () use ($catalogue): array');
});

it('preserves KES when an approved work plan is created or edited', function () {
    $source = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/ApprovedWorkPlanController.php');
    $lifecycleMethods = substr(
        $source,
        strpos($source, 'public function store(Request $request)'),
        strpos($source, 'public function approve(', strpos($source, 'public function store(Request $request)'))
            - strpos($source, 'public function store(Request $request)')
    );

    expect($lifecycleMethods)
        ->not->toContain("'currency' => 'USD'")
        ->and($source)
        ->toContain('$data[\'currency\'] = $commitment->resolved_currency;')
        ->toContain('$funding?->resolved_currency ?: $fallbackCurrency ?: \'USD\'')
        ->toContain('allocation of {$currency} ');
});

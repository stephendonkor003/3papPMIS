<?php

namespace Database\Seeders;

use App\Services\LegacyAttpDemoDataCleaner;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class KenyaGovernmentSeeder extends Seeder
{
    public const DATASET_TAG = LegacyAttpDemoDataCleaner::KENYA_TAG;

    public const PROGRAM_PREFIX = 'KEN-PROG-';

    public const PROJECT_PREFIX = 'KEN-PRJ-';

    public const PURCHASE_REQUEST_PREFIX = 'KEN-PR-';

    public const WORK_PLAN_PREFIX = 'KEN-AWP-';

    public const PURCHASE_ORDER_PREFIX = 'KEN-PO-';

    public const INVOICE_PREFIX = 'KEN-INV-';

    public const DISBURSEMENT_PREFIX = 'KEN-PAY-';

    public const START_YEAR = 2026;

    public const END_YEAR = 2028;

    private const REQUIRED_TABLES = [
        'users',
        'myb_governance_levels',
        'myb_governance_nodes',
        'myb_governance_reporting_lines',
        'myb_sectors',
        'myb_programs',
        'myb_program_fundings',
        'myb_projects',
        'myb_project_allocations',
        'myb_activities',
        'myb_activity_allocations',
        'myb_sub_activities',
        'myb_sub_activity_allocations',
        'myb_funders',
        'myb_resource_categories',
        'myb_resources',
        'myb_purchase_requests',
        'myb_purchase_request_items',
        'myb_budget_commitments',
        'approved_work_plans',
        'approved_work_plan_item_reviews',
        'procurement_invoices',
        'procurement_purchase_orders',
        'procurement_purchase_order_item_evidence',
        'procurement_disbursements',
    ];

    private string $now;

    public function run(): void
    {
        $this->assertRequiredTablesExist();
        $catalogue = require database_path('seeders/data/kenya_government.php');
        $this->validateCatalogue($catalogue);
        $this->now = now()->format('Y-m-d H:i:s');

        $result = DB::transaction(function () use ($catalogue): array {
            $removed = app(LegacyAttpDemoDataCleaner::class)->purge();
            $adminId = $this->resolveAdminId();
            $levelIds = $this->seedLevels();
            $governance = $this->seedGovernance($catalogue, $levelIds, $adminId);
            $funderId = $this->seedFunder();
            $resources = $this->seedResources($governance['node_ids']['KE-GOK'], $adminId);
            $evidenceDocument = $this->seedEvidenceDocument();
            $finance = $this->seedPortfoliosAndFinance(
                $governance['delivery_entities'],
                $funderId,
                $resources,
                $evidenceDocument,
                $adminId,
            );

            $summary = [
                'removed' => $removed,
                'governance' => $governance['summary'],
                'finance' => $finance,
            ];
            $this->validateSeededDataset($governance, $finance);

            return $summary;
        }, 3);

        $this->command?->info(sprintf(
            'Kenya government demo dataset ready: %d nodes, %d portfolios, %d programs, %d projects, %d activities, %d sub-activities, %d commitments, and %d paid receipts.',
            $result['governance']['nodes'],
            $result['finance']['sectors'],
            $result['finance']['programs'],
            $result['finance']['projects'],
            $result['finance']['activities'],
            $result['finance']['sub_activities'],
            $result['finance']['commitments'],
            $result['finance']['disbursements'],
        ));
        $this->command?->warn(
            'All KES amounts installed by KenyaGovernmentSeeder are synthetic demonstration figures, not official appropriations or expenditure.'
        );
    }

    private function assertRequiredTablesExist(): void
    {
        $missing = collect(self::REQUIRED_TABLES)
            ->reject(fn (string $table): bool => Schema::hasTable($table))
            ->values();

        if ($missing->isNotEmpty()) {
            throw new RuntimeException(
                'Run all migrations before KenyaGovernmentSeeder. Missing tables: '.$missing->implode(', ')
            );
        }
    }

    /** @param array<string, mixed> $catalogue */
    private function validateCatalogue(array $catalogue): void
    {
        if (($catalogue['snapshot_date'] ?? null) !== '2026-08-22' || count($catalogue['sources'] ?? []) < 3) {
            throw new RuntimeException('The Kenya government catalogue must retain its source date and official source list.');
        }

        $ministryCodes = collect($catalogue['ministries'] ?? [])->pluck('code');
        $departmentCodes = collect($catalogue['ministries'] ?? [])
            ->flatMap(fn (array $ministry): array => $ministry['departments'] ?? [])
            ->pluck('code');
        $fixedCodes = collect($catalogue['executive_offices'] ?? [])->pluck('code')
            ->merge($ministryCodes)
            ->merge($departmentCodes)
            ->merge(collect($catalogue['independent_institutions'] ?? [])->pluck('code'));

        if ($fixedCodes->contains(fn (mixed $code): bool => ! is_string($code) || ! str_starts_with($code, 'KE-'))) {
            throw new RuntimeException('Every Kenya governance code must use the KE- namespace.');
        }
        if ($fixedCodes->duplicates()->isNotEmpty()) {
            throw new RuntimeException('Duplicate Kenya governance codes: '.$fixedCodes->duplicates()->implode(', '));
        }
        if ($ministryCodes->count() < 23) {
            throw new RuntimeException('The catalogue must include the current Cabinet portfolios and State Law Office.');
        }

        foreach ($catalogue['ministries'] as $ministry) {
            $portfolioCount = count($ministry['portfolios'] ?? []);
            if ($portfolioCount < 1 || $portfolioCount > 2) {
                throw new RuntimeException("{$ministry['name']} must manage one or two portfolios.");
            }
        }

        $agencyNames = collect($catalogue['ministries'])
            ->flatMap(fn (array $ministry) => collect($ministry['departments'] ?? [])->flatMap(
                fn (array $department): array => $department['agencies'] ?? []
            ));
        if ($agencyNames->count() < 100) {
            throw new RuntimeException('The Kenya catalogue must include at least 100 national agencies and state corporations.');
        }
        if ($agencyNames->duplicates()->isNotEmpty()) {
            throw new RuntimeException('Duplicate Kenya agencies: '.$agencyNames->duplicates()->implode(', '));
        }
    }

    /** @return array<string, string> */
    private function seedLevels(): array
    {
        $levels = [
            ['key' => 'national_government', 'name' => 'National Government', 'sort_order' => 10, 'description' => 'The Government of the Republic of Kenya national root.'],
            ['key' => 'executive_office', 'name' => 'Executive Office', 'sort_order' => 20, 'description' => 'Constitutional and coordinating offices of Kenya\'s National Executive.'],
            ['key' => 'ministry', 'name' => 'Ministry / State Law Office', 'sort_order' => 30, 'description' => 'Cabinet-level ministry or the State Law Office of Kenya.'],
            ['key' => 'government_department', 'name' => 'State Department', 'sort_order' => 40, 'description' => 'Principal-Secretary-led State Department in Kenya\'s National Government.'],
            ['key' => 'directorate_unit', 'name' => 'Directorate / Unit', 'sort_order' => 50, 'description' => 'Directorate, secretariat, or specialist delivery unit.'],
            ['key' => 'agency_authority', 'name' => 'Agency / Authority', 'sort_order' => 60, 'description' => 'Kenyan statutory agency, authority, board, service, or regulatory body.'],
            ['key' => 'constitutional_body', 'name' => 'Constitutional Institution / Independent Office', 'sort_order' => 70, 'description' => 'Constitutional commission, independent office, Parliament, or Judiciary.'],
            ['key' => 'parastatal_soe', 'name' => 'State Corporation / Parastatal', 'sort_order' => 80, 'description' => 'Kenyan state corporation, public company, fund, or parastatal.'],
            ['key' => 'local_authority', 'name' => 'County Government', 'sort_order' => 90, 'description' => 'County government level reserved for future devolved-government datasets.'],
        ];

        $ids = [];
        foreach ($levels as $level) {
            $id = DB::table('myb_governance_levels')->where('key', $level['key'])->value('id');
            if ($id) {
                DB::table('myb_governance_levels')->where('id', $id)->update([
                    ...$level,
                    'updated_at' => $this->now,
                ]);
            } else {
                $id = $this->uuid('governance-level', $level['key']);
                DB::table('myb_governance_levels')->insert([
                    'id' => $id,
                    ...$level,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]);
            }
            $ids[$level['key']] = (string) $id;
        }

        return $ids;
    }

    /**
     * @param  array<string, mixed>  $catalogue
     * @param  array<string, string>  $levelIds
     * @return array{node_ids:array<string,string>,delivery_entities:array<int,array<string,mixed>>,summary:array<string,int>}
     */
    private function seedGovernance(array $catalogue, array $levelIds, ?string $adminId): array
    {
        $definitions = [];
        $lines = [];
        $deliveryEntities = [];
        $addNode = function (
            string $code,
            string $name,
            string $level,
            ?string $parentCode,
            string $kind,
            array $portfolios = []
        ) use (&$definitions, &$lines, &$deliveryEntities): void {
            if (isset($definitions[$code])) {
                throw new RuntimeException("Duplicate generated governance node code: {$code}");
            }
            $definitions[$code] = compact('code', 'name', 'level', 'parentCode', 'kind');
            if ($parentCode) {
                $lines[] = ['child' => $code, 'parent' => $parentCode];
            }
            if ($portfolios !== []) {
                $deliveryEntities[] = compact('code', 'name', 'kind', 'portfolios');
            }
        };

        $addNode('KE-GOK', 'Government of the Republic of Kenya', 'national_government', null, 'root');
        foreach ($catalogue['executive_offices'] as $index => $office) {
            $addNode(
                $office['code'],
                $office['name'],
                'executive_office',
                $index === 0 ? 'KE-GOK' : 'KE-EOP',
                'executive_office',
                [$office['name'].' Coordination and Delivery']
            );
        }

        foreach ($catalogue['ministries'] as $ministry) {
            $addNode($ministry['code'], $ministry['name'], 'ministry', 'KE-EOP', 'ministry', $ministry['portfolios']);
            foreach ($ministry['departments'] as $department) {
                $addNode(
                    $department['code'],
                    $department['name'],
                    'government_department',
                    $ministry['code'],
                    'state_department',
                    [$department['name'].' Service Delivery']
                );
                foreach ($department['agencies'] as $agencyName) {
                    $agencyCode = $this->agencyCode($agencyName);
                    $kind = $this->agencyKind($agencyName);
                    $level = $kind === 'state_corporation' ? 'parastatal_soe' : 'agency_authority';
                    $addNode(
                        $agencyCode,
                        $agencyName,
                        $level,
                        $department['code'],
                        $kind,
                        [$agencyName.' Service Delivery']
                    );
                }
            }
        }

        foreach ($catalogue['independent_institutions'] as $institution) {
            $addNode(
                $institution['code'],
                $institution['name'],
                'constitutional_body',
                'KE-GOK',
                'independent_institution',
                [$institution['portfolio']]
            );
        }

        $codes = array_keys($definitions);
        $existingIds = DB::table('myb_governance_nodes')->whereIn('code', $codes)->pluck('id', 'code');
        $nodeIds = [];
        $rows = [];
        foreach ($definitions as $definition) {
            $code = $definition['code'];
            $nodeIds[$code] = (string) ($existingIds[$code] ?? $this->uuid('governance-node', $code));
            $rows[] = [
                'id' => $nodeIds[$code],
                'level_id' => $levelIds[$definition['level']],
                'name' => $definition['name'],
                'code' => $code,
                'description' => self::DATASET_TAG.' Official-structure snapshot node for '.$definition['name'].'.',
                'status' => 'active',
                'effective_start' => '2026-08-22',
                'effective_end' => null,
                'created_by' => $adminId,
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'deleted_at' => null,
            ];
        }

        $controlledNodeIds = DB::table('myb_governance_nodes')
            ->whereRaw("coalesce(description, '') like ?", ['%'.self::DATASET_TAG.'%'])
            ->pluck('id')
            ->merge(array_values($nodeIds))
            ->unique()
            ->values();
        if ($controlledNodeIds->isNotEmpty()) {
            DB::table('myb_governance_reporting_lines')
                ->whereIn('child_node_id', $controlledNodeIds->all())
                ->orWhereIn('parent_node_id', $controlledNodeIds->all())
                ->delete();
        }
        DB::table('myb_governance_nodes')
            ->whereRaw("coalesce(description, '') like ?", ['%'.self::DATASET_TAG.'%'])
            ->whereNotIn('code', $codes)
            ->delete();

        $this->upsertRows(
            'myb_governance_nodes',
            $rows,
            ['code'],
            ['level_id', 'name', 'description', 'status', 'effective_start', 'effective_end', 'created_by', 'updated_at', 'deleted_at']
        );

        $lineRows = collect($lines)->map(function (array $line) use ($nodeIds, $adminId): array {
            $key = $line['parent'].'>'.$line['child'];

            return [
                'id' => $this->uuid('governance-line', $key),
                'child_node_id' => $nodeIds[$line['child']],
                'parent_node_id' => $nodeIds[$line['parent']],
                'line_type' => 'primary',
                'effective_start' => '2026-08-22',
                'effective_end' => null,
                'created_by' => $adminId,
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'deleted_at' => null,
            ];
        })->all();
        $this->upsertRows('myb_governance_reporting_lines', $lineRows);

        foreach ($deliveryEntities as &$entity) {
            $entity['node_id'] = $nodeIds[$entity['code']];
        }
        unset($entity);

        return [
            'node_ids' => $nodeIds,
            'delivery_entities' => $deliveryEntities,
            'summary' => [
                'nodes' => count($rows),
                'reporting_lines' => count($lineRows),
                'ministries' => count($catalogue['ministries']),
                'agencies' => collect($deliveryEntities)->whereIn('kind', ['agency', 'state_corporation'])->count(),
                'independent_institutions' => count($catalogue['independent_institutions']),
            ],
        ];
    }

    private function seedFunder(): string
    {
        $name = 'Government of Kenya - National Exchequer';
        $id = DB::table('myb_funders')->where('name', $name)->value('id')
            ?: $this->uuid('funder', 'national-exchequer');

        DB::table('myb_funders')->updateOrInsert(
            ['id' => $id],
            [
                'name' => $name,
                'type' => 'government',
                'currency' => 'KES',
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]
        );

        return (string) $id;
    }

    /** @return array{categories:array<int,string>,resources:array<int,string>} */
    private function seedResources(string $rootNodeId, ?string $adminId): array
    {
        $categories = [
            ['key' => 'delivery', 'name' => 'Kenya Government Programme Delivery Services'],
            ['key' => 'assurance', 'name' => 'Kenya Government Monitoring and Assurance Services'],
        ];
        $resources = [
            ['key' => 'delivery', 'category' => 'delivery', 'name' => 'Programme implementation and delivery services'],
            ['key' => 'assurance', 'category' => 'assurance', 'name' => 'Monitoring, verification and assurance services'],
        ];

        $categoryIds = [];
        $categoryRows = [];
        foreach ($categories as $category) {
            $categoryIds[$category['key']] = $this->uuid('resource-category', $category['key']);
            $categoryRows[] = [
                'id' => $categoryIds[$category['key']],
                'name' => $category['name'],
                'description' => self::DATASET_TAG.' Shared category for synthetic Kenya government execution records.',
                'governance_node_id' => $rootNodeId,
                'status' => 'active',
                'created_by' => $adminId,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];
        }
        $this->upsertRows('myb_resource_categories', $categoryRows);

        $resourceIds = [];
        $resourceRows = [];
        foreach ($resources as $resource) {
            $resourceIds[$resource['key']] = $this->uuid('resource', $resource['key']);
            $resourceRows[] = [
                'id' => $resourceIds[$resource['key']],
                'resource_category_id' => $categoryIds[$resource['category']],
                'governance_node_id' => $rootNodeId,
                'name' => $resource['name'],
                'reference_code' => 'KEN-RES-'.strtoupper($resource['key']),
                'description' => self::DATASET_TAG.' Synthetic shared resource used for the Kenya demo lifecycle.',
                'status' => 'active',
                'is_human_resource' => false,
                'created_by' => $adminId,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];
        }
        $this->upsertRows('myb_resources', $resourceRows);

        return ['categories' => $categoryIds, 'resources' => $resourceIds];
    }

    /** @return array{path:string,name:string,mime_type:string,size:int} */
    private function seedEvidenceDocument(): array
    {
        $path = 'kenya-government-demo/evidence/synthetic-procurement-evidence.pdf';
        $contents = Pdf::loadHTML(<<<'HTML'
            <!doctype html>
            <html lang="en">
            <head><meta charset="utf-8"><title>Synthetic procurement evidence</title></head>
            <body style="font-family: sans-serif; color: #1f2937; padding: 32px;">
                <h1 style="color: #006b3f;">Kenya Government Demo Evidence</h1>
                <p><strong>Synthetic record - not an official procurement, appropriation, invoice, or payment document.</strong></p>
                <p>This shared fixture supports the demonstration purchase-order, delivery-verification, and signed-payment workflows created by KenyaGovernmentSeeder.</p>
                <p>Dataset snapshot: 22 August 2026. Currency: Kenya shillings (KES).</p>
            </body>
            </html>
            HTML)
            ->setPaper('a4')
            ->output();
        $disk = Storage::disk('local');

        if (! $disk->put($path, $contents)) {
            throw new RuntimeException('Unable to create the Kenya government synthetic evidence fixture.');
        }

        return [
            'path' => $path,
            'name' => 'kenya-government-synthetic-procurement-evidence.pdf',
            'mime_type' => 'application/pdf',
            'size' => strlen($contents),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $entities
     * @param  array{categories:array<int,string>,resources:array<int,string>}  $resourceMap
     * @return array<string, int>
     */
    private function seedPortfoliosAndFinance(
        array $entities,
        string $funderId,
        array $resourceMap,
        array $evidenceDocument,
        ?string $adminId
    ): array {
        $summary = collect($this->newFinanceRows())
            ->map(fn (): int => 0)
            ->all();

        foreach ($entities as $entity) {
            $rows = $this->newFinanceRows();

            foreach (array_values($entity['portfolios']) as $portfolioIndex => $portfolioLabel) {
                $portfolioKey = $entity['code'].'|portfolio|'.($portfolioIndex + 1);
                $portfolioId = $this->uuid('portfolio', $portfolioKey);
                $portfolioName = str_ends_with($portfolioLabel, 'Portfolio')
                    ? $portfolioLabel
                    : $portfolioLabel.' Portfolio';
                $managerToken = substr(hash('sha256', $entity['code']), 0, 12);
                $managerEmail = "portfolio.{$managerToken}@kenya-government.example.invalid";
                $rows['sectors'][] = [
                    'id' => $portfolioId,
                    'name' => $this->limit($portfolioName),
                    'description' => self::DATASET_TAG.' Synthetic planning portfolio managed by '.$entity['name'].'. Figures are demonstration data, not official appropriations.',
                    'status' => 'active',
                    'currency' => 'KES',
                    'governance_node_id' => $entity['node_id'],
                    'portfolio_manager_user_id' => null,
                    'portfolio_manager_name' => 'Accounting Officer, '.$entity['name'],
                    'portfolio_manager_email' => $managerEmail,
                    'portfolio_manager_role' => 'Accounting Officer / Portfolio Manager',
                    'ttl_name' => 'Programme Lead, '.$entity['name'],
                    'ttl_email' => $managerEmail,
                    'me_manager_user_id' => null,
                    'me_manager_name' => 'Monitoring and Evaluation Lead, '.$entity['name'],
                    'me_manager_email' => "me.{$managerToken}@kenya-government.example.invalid",
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];

                $programCode = self::PROGRAM_PREFIX.strtoupper(substr(hash('sha256', $portfolioKey), 0, 12));
                $programId = $this->uuid('program', $programCode);
                $projectCount = 3 + ($this->stableNumber($portfolioKey, 2));
                $programmeProjects = [];
                for ($projectIndex = 0; $projectIndex < $projectCount; $projectIndex++) {
                    $programmeProjects[] = $this->buildProjectRows(
                        $rows,
                        $entity,
                        $portfolioName,
                        $programId,
                        $programCode,
                        $projectIndex,
                        $adminId,
                    );
                }
                $programBudget = round(collect($programmeProjects)->sum('budget'), 2);

                $rows['programs'][] = [
                    'id' => $programId,
                    'program_id' => $programCode,
                    'sector_id' => $portfolioId,
                    'department_id' => null,
                    'governance_node_id' => $entity['node_id'],
                    'ttl_user_id' => null,
                    'ttl_name' => 'Programme Lead, '.$entity['name'],
                    'ttl_email' => $managerEmail,
                    'ttl_notified_at' => null,
                    'name' => $this->limit(str_replace(' Portfolio', '', $portfolioName).' Implementation Programme'),
                    'description' => self::DATASET_TAG.' Three-year synthetic implementation programme for '.$entity['name'].'.',
                    'expected_outcome_type' => 'text',
                    'expected_outcome_value' => 'Improved, measurable and accountable public service delivery.',
                    'currency' => 'KES',
                    'start_year' => self::START_YEAR,
                    'end_year' => self::END_YEAR,
                    'total_years' => 3,
                    'total_budget' => $programBudget,
                    'created_by' => $adminId,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
                $rows['fundings'][] = [
                    'id' => $this->uuid('program-funding', $programCode),
                    'department_id' => null,
                    'program_id' => $programId,
                    'program_name' => str_replace(' Portfolio', '', $portfolioName).' Implementation Programme',
                    'description' => self::DATASET_TAG.' Synthetic National Exchequer allocation for application demonstration only.',
                    'funder_id' => $funderId,
                    'governance_node_id' => $entity['node_id'],
                    'funding_type' => 'allocation',
                    'approved_amount' => $programBudget,
                    'currency' => 'KES',
                    'start_year' => self::START_YEAR,
                    'end_year' => self::END_YEAR,
                    'status' => 'approved',
                    'is_continental_initiative' => false,
                    'rejection_reason' => null,
                    'rejected_by' => null,
                    'rejected_at' => null,
                    'approved_by' => $adminId,
                    'approved_at' => '2026-07-01 09:00:00',
                    'created_by' => $adminId,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];

                $fundingId = $rows['fundings'][array_key_last($rows['fundings'])]['id'];
                foreach ($programmeProjects as $project) {
                    $this->appendExecutionRows(
                        $rows,
                        $entity,
                        $programId,
                        $fundingId,
                        $funderId,
                        $project,
                        $resourceMap,
                        $evidenceDocument,
                        $adminId,
                    );
                }
            }

            $this->persistFinanceRows($rows);
            foreach ($rows as $key => $tableRows) {
                $summary[$key] += count($tableRows);
            }
        }

        return $summary;
    }

    /** @return array<string, array<int, array<string, mixed>>> */
    private function newFinanceRows(): array
    {
        $rows = [
            'sectors' => [],
            'programs' => [],
            'fundings' => [],
            'projects' => [],
            'project_allocations' => [],
            'activities' => [],
            'activity_allocations' => [],
            'sub_activities' => [],
            'sub_activity_allocations' => [],
            'purchase_requests' => [],
            'purchase_request_items' => [],
            'commitments' => [],
            'work_plans' => [],
            'work_plan_item_reviews' => [],
            'invoices' => [],
            'purchase_orders' => [],
            'purchase_order_evidence' => [],
            'disbursements' => [],
        ];

        return $rows;
    }

    /** @param array<string, array<int, array<string, mixed>>> $rows */
    private function persistFinanceRows(array $rows): void
    {
        $this->upsertRows('myb_sectors', $rows['sectors']);
        $this->upsertRows('myb_programs', $rows['programs']);
        $this->upsertRows('myb_program_fundings', $rows['fundings']);
        $this->upsertRows('myb_projects', $rows['projects']);
        $this->upsertRows('myb_project_allocations', $rows['project_allocations']);
        $this->upsertRows('myb_activities', $rows['activities']);
        $this->upsertRows('myb_activity_allocations', $rows['activity_allocations']);
        $this->upsertRows('myb_sub_activities', $rows['sub_activities']);
        $this->upsertRows('myb_sub_activity_allocations', $rows['sub_activity_allocations']);
        $this->upsertRows('myb_purchase_requests', $rows['purchase_requests']);
        $this->upsertRows('myb_purchase_request_items', $rows['purchase_request_items']);
        $this->upsertRows('myb_budget_commitments', $rows['commitments']);
        $this->upsertRows('approved_work_plans', $rows['work_plans']);
        $this->upsertRows('approved_work_plan_item_reviews', $rows['work_plan_item_reviews']);
        $this->upsertRows('procurement_invoices', $rows['invoices']);
        $this->upsertRows('procurement_purchase_orders', $rows['purchase_orders']);
        $this->upsertRows('procurement_purchase_order_item_evidence', $rows['purchase_order_evidence']);
        $this->upsertRows('procurement_disbursements', $rows['disbursements']);
    }

    /**
     * @param  array<string, array<int, array<string, mixed>>>  $rows
     * @param  array<string, mixed>  $entity
     * @return array{project_id:string,project_code:string,project_name:string,budget:float,cases:array<int,array<string,mixed>>}
     */
    private function buildProjectRows(
        array &$rows,
        array $entity,
        string $portfolioName,
        string $programId,
        string $programCode,
        int $projectIndex,
        ?string $adminId,
    ): array {
        $projectTemplates = [
            'Service Delivery Modernisation',
            'Infrastructure and Asset Renewal',
            'Workforce and Institutional Capacity',
            'Monitoring, Accountability and Citizen Engagement',
        ];
        $activityTemplates = [
            [
                'name' => 'Planning, design and baseline validation',
                'subs' => ['Validate the annual implementation plan', 'Establish baselines, targets and delivery standards'],
            ],
            [
                'name' => 'Procurement and implementation',
                'subs' => ['Prepare and approve procurement packages', 'Deliver prioritised programme interventions'],
            ],
            [
                'name' => 'Monitoring, evaluation and close-out',
                'subs' => ['Conduct quarterly performance and financial reviews', 'Publish completion results and lessons learned'],
            ],
        ];

        $projectKey = $programCode.'|project|'.($projectIndex + 1);
        $projectCode = self::PROJECT_PREFIX.strtoupper(substr(hash('sha256', $projectKey), 0, 12));
        $projectId = $this->uuid('project', $projectCode);
        $projectName = $this->limit(
            str_replace(' Portfolio', '', $portfolioName).': '.$projectTemplates[$projectIndex]
        );
        $budget = $this->projectBudget($entity['kind'], $projectKey);
        $rows['projects'][] = [
            'id' => $projectId,
            'program_id' => $programId,
            'project_id' => $projectCode,
            'governance_node_id' => $entity['node_id'],
            'name' => $projectName,
            'description' => self::DATASET_TAG.' Synthetic project managed by '.$entity['name'].'.',
            'expected_outcome_type' => 'text',
            'expected_outcome_value' => 'Planned outputs delivered on time, within budget, and with verified public value.',
            'currency' => 'KES',
            'start_year' => self::START_YEAR,
            'end_year' => self::END_YEAR,
            'total_years' => 3,
            'total_budget' => $budget,
            'created_by' => $adminId,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];

        $projectByYear = $this->splitAmount($budget, [30, 40, 30]);
        foreach (range(self::START_YEAR, self::END_YEAR) as $yearIndex => $year) {
            $rows['project_allocations'][] = [
                'id' => $this->uuid('project-allocation', "{$projectCode}|{$year}"),
                'project_id' => $projectId,
                'year' => $year,
                'year_number' => $yearIndex + 1,
                'actual_year' => $year,
                'amount' => $projectByYear[$yearIndex],
            ];
        }

        $cases = [];
        foreach ($activityTemplates as $activityIndex => $activityTemplate) {
            $activityKey = $projectCode.'|activity|'.($activityIndex + 1);
            $activityId = $this->uuid('activity', $activityKey);
            $activityName = $this->limit(($activityIndex + 1).'. '.$activityTemplate['name']);
            $rows['activities'][] = [
                'id' => $activityId,
                'project_id' => $projectId,
                'governance_node_id' => $entity['node_id'],
                'name' => $activityName,
                'description' => self::DATASET_TAG.' '.$activityTemplate['name'].' for '.$projectName.'.',
                'expected_outcome_type' => 'text',
                'expected_outcome_value' => 'Activity milestones completed and independently verifiable.',
                'created_by' => $adminId,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];

            $activityByYear = [];
            foreach ($projectByYear as $yearIndex => $projectYearAmount) {
                $split = $this->splitAmount($projectYearAmount, [40, 35, 25]);
                $activityByYear[$yearIndex] = $split[$activityIndex];
                $year = self::START_YEAR + $yearIndex;
                $rows['activity_allocations'][] = [
                    'id' => $this->uuid('activity-allocation', "{$activityKey}|{$year}"),
                    'activity_id' => $activityId,
                    'year' => $year,
                    'amount' => $activityByYear[$yearIndex],
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
            }

            foreach ($activityTemplate['subs'] as $subIndex => $subName) {
                $subKey = $activityKey.'|sub|'.($subIndex + 1);
                $subActivityId = $this->uuid('sub-activity', $subKey);
                $rows['sub_activities'][] = [
                    'id' => $subActivityId,
                    'activity_id' => $activityId,
                    'governance_node_id' => $entity['node_id'],
                    'name' => $this->limit(($activityIndex + 1).'.'.($subIndex + 1).' '.$subName),
                    'description' => self::DATASET_TAG.' '.$subName.' under '.$projectName.'.',
                    'expected_outcome_type' => 'text',
                    'expected_outcome_value' => 'A documented and quality-assured deliverable is completed.',
                    'created_by' => $adminId,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];

                $currentYearAllocation = 0.0;
                foreach ($activityByYear as $yearIndex => $activityYearAmount) {
                    $split = $this->splitAmount($activityYearAmount, [60, 40]);
                    $year = self::START_YEAR + $yearIndex;
                    $rows['sub_activity_allocations'][] = [
                        'id' => $this->uuid('sub-activity-allocation', "{$subKey}|{$year}"),
                        'sub_activity_id' => $subActivityId,
                        'year' => $year,
                        'amount' => $split[$subIndex],
                        'created_at' => $this->now,
                        'updated_at' => $this->now,
                    ];
                    if ($year === self::START_YEAR) {
                        $currentYearAllocation = $split[$subIndex];
                    }
                }

                if ($subIndex === 0 && $activityIndex < 2) {
                    $cases[] = [
                        'case' => $activityIndex + 1,
                        'activity_name' => $activityName,
                        'sub_activity_id' => $subActivityId,
                        'sub_activity_name' => $subName,
                        'allocation' => $currentYearAllocation,
                    ];
                }
            }
        }

        return compact('projectId', 'projectCode', 'projectName', 'budget', 'cases') + [
            'project_id' => $projectId,
            'project_code' => $projectCode,
            'project_name' => $projectName,
        ];
    }

    /**
     * @param  array<string, array<int, array<string, mixed>>>  $rows
     * @param  array<string, mixed>  $entity
     * @param  array<string, mixed>  $project
     * @param  array{categories:array<int,string>,resources:array<int,string>}  $resourceMap
     */
    private function appendExecutionRows(
        array &$rows,
        array $entity,
        string $programId,
        string $fundingId,
        string $funderId,
        array $project,
        array $resourceMap,
        array $evidenceDocument,
        ?string $adminId,
    ): void {
        foreach ($project['cases'] as $case) {
            $caseNumber = (int) $case['case'];
            $executionKey = $project['project_code'].'|execution|'.$caseNumber;
            $referenceToken = strtoupper(substr(hash('sha256', $executionKey), 0, 12));
            $commitmentRatio = $caseNumber === 1 ? 80 : 65;
            $paymentRatio = $caseNumber === 1 ? 100 : 60;
            $amount = $this->percentage((float) $case['allocation'], $commitmentRatio);
            $itemAmounts = $this->splitAmount($amount, [65, 35]);
            $purchaseRequestId = $this->uuid('purchase-request', $executionKey);
            $commitmentId = $this->uuid('commitment', $executionKey);
            $invoiceId = $this->uuid('invoice', $executionKey);
            $purchaseOrderId = $this->uuid('purchase-order', $executionKey);
            $workPlanSource = 'Kenya Government Annual Work Plan FY 2026/2027';

            $rows['purchase_requests'][] = [
                'id' => $purchaseRequestId,
                'reference_no' => self::PURCHASE_REQUEST_PREFIX.'2026-'.$referenceToken.'-0'.$caseNumber,
                'program_funding_id' => $fundingId,
                'governance_node_id' => $entity['node_id'],
                'allocation_level' => 'sub_activity',
                'allocation_id' => $case['sub_activity_id'],
                'start_year' => self::START_YEAR,
                'commitment_date' => '2026-07-15',
                'delivery_date' => '2027-06-30',
                'currency' => 'KES',
                'total_amount' => $amount,
                'description' => self::DATASET_TAG.' Execution request for '.$case['sub_activity_name'].'.',
                'status' => 'approved',
                'approved_by' => $adminId,
                'approved_at' => '2026-07-20 10:00:00',
                'rejection_reason' => null,
                'rejected_by' => null,
                'rejected_at' => null,
                'work_plan_source' => $workPlanSource,
                'work_plan_component' => $project['project_name'],
                'work_plan_sub_component' => $case['activity_name'],
                'created_by' => $adminId,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];

            $itemIds = [];
            foreach ([
                ['key' => 'delivery', 'label' => 'Implementation milestone'],
                ['key' => 'assurance', 'label' => 'Verification and assurance milestone'],
            ] as $itemIndex => $itemDefinition) {
                $itemKey = $executionKey.'|item|'.($itemIndex + 1);
                $itemIds[$itemIndex] = $this->uuid('purchase-request-item', $itemKey);
                $rows['purchase_request_items'][] = [
                    'id' => $itemIds[$itemIndex],
                    'purchase_request_id' => $purchaseRequestId,
                    'resource_category_id' => $resourceMap['categories'][$itemDefinition['key']],
                    'resource_id' => $resourceMap['resources'][$itemDefinition['key']],
                    'deliverable_id' => null,
                    'milestone' => $itemDefinition['label'].' for '.$case['sub_activity_name'],
                    'milestone_date' => $caseNumber === 1 ? '2026-07-31' : '2026-08-15',
                    'unit_price' => $itemAmounts[$itemIndex],
                    'quantity' => 1,
                    'amount' => $itemAmounts[$itemIndex],
                    'work_plan_source' => $workPlanSource,
                    'work_plan_sort_order' => $itemIndex + 1,
                    'work_plan_serial' => $caseNumber.'.'.($itemIndex + 1),
                    'implemented_by' => $entity['name'],
                    'budget_code' => $project['project_code'].'-'.$caseNumber,
                    'object_type' => $itemDefinition['key'] === 'delivery' ? 'Services' : 'Monitoring and Assurance',
                    'estimated_amount' => $itemAmounts[$itemIndex],
                    'work_plan_months' => json_encode(['jul', 'aug', 'sep', 'oct', 'nov', 'dec']),
                    'work_plan_audience' => 'Kenyan public and programme stakeholders',
                    'work_plan_units' => 1,
                    'work_plan_payment_basis' => 'scheduled',
                    'work_plan_unit_rate' => $itemAmounts[$itemIndex],
                    'work_plan_person_months' => null,
                    'work_plan_monthly_amount' => null,
                    'intermediate_indicator' => 'Milestone completed and verified',
                    'result_indicator' => 'Planned public-service output delivered',
                    'observations' => 'Synthetic Kenya government demo record.',
                    'world_bank_comments' => null,
                    'attp_secretariat_comments' => null,
                    'world_bank_amount' => null,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
                $rows['work_plan_item_reviews'][] = [
                    'id' => $this->uuid('approved-work-plan-item-review', $itemKey),
                    'purchase_request_item_id' => $itemIds[$itemIndex],
                    'program_funding_id' => $fundingId,
                    'funder_id' => $funderId,
                    'status' => 'approved',
                    'reviewed_by' => $adminId,
                    'reviewed_at' => '2026-07-20 10:00:00',
                    'review_notes' => self::DATASET_TAG.' Approved synthetic annual work-plan line.',
                    'document_type' => null,
                    'document_path' => null,
                    'document_name' => null,
                    'document_uploaded_by' => null,
                    'document_uploaded_at' => null,
                    'tor_path' => null,
                    'tor_name' => null,
                    'tor_uploaded_at' => null,
                    'concept_note_path' => null,
                    'concept_note_name' => null,
                    'concept_note_uploaded_at' => null,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
            }

            $rows['commitments'][] = [
                'id' => $commitmentId,
                'program_funding_id' => $fundingId,
                'purchase_request_id' => $purchaseRequestId,
                'governance_node_id' => $entity['node_id'],
                'allocation_level' => 'sub_activity',
                'allocation_id' => $case['sub_activity_id'],
                'resource_category_id' => null,
                'resource_id' => null,
                'commitment_amount' => $amount,
                'commitment_year' => self::START_YEAR,
                'status' => 'approved',
                'description' => self::DATASET_TAG.' Approved commitment for '.$case['sub_activity_name'].'.',
                'rejection_reason' => null,
                'created_by' => $adminId,
                'approved_by' => $adminId,
                'approved_at' => '2026-07-20 10:00:00',
            ];
            $rows['work_plans'][] = [
                'id' => $this->uuid('approved-work-plan', $executionKey),
                'awp_code' => self::WORK_PLAN_PREFIX.'2026-'.$referenceToken.'-0'.$caseNumber,
                'title' => $this->limit($case['sub_activity_name']),
                'budget_commitment_id' => $commitmentId,
                'program_funding_id' => $fundingId,
                'governance_node_id' => $entity['node_id'],
                'fiscal_year' => (string) self::START_YEAR,
                'planned_amount' => $amount,
                'currency' => 'KES',
                'start_date' => '2026-07-01',
                'end_date' => '2027-06-30',
                'status' => 'approved',
                'description' => $workPlanSource,
                'expected_outputs' => 'A verified output for '.$project['project_name'].'.',
                'implementation_notes' => self::DATASET_TAG.' Managed by '.$entity['name'].'.',
                'created_by' => $adminId,
                'approved_by' => $adminId,
                'approved_at' => '2026-07-20 10:00:00',
                'review_notes' => 'Approved as synthetic demonstration data.',
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];
            $rows['invoices'][] = [
                'id' => $invoiceId,
                'procurement_id' => null,
                'vendor_id' => null,
                'sub_activity_id' => $case['sub_activity_id'],
                'governance_node_id' => $entity['node_id'],
                'invoice_month' => $caseNumber === 1 ? '2026-07-01' : '2026-08-01',
                'reference_no' => self::INVOICE_PREFIX.'2026-'.$referenceToken.'-0'.$caseNumber,
                'amount' => $amount,
                'currency' => 'KES',
                'status' => $caseNumber === 1 ? 'paid' : 'approved',
                'created_by' => $adminId,
                'approved_by' => $adminId,
                'approved_at' => $caseNumber === 1 ? '2026-08-10 09:00:00' : '2026-08-18 09:00:00',
                'notes' => self::DATASET_TAG.' Synthetic invoice linked to an approved purchase order.',
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];
            $rows['purchase_orders'][] = [
                'id' => $purchaseOrderId,
                'procurement_id' => null,
                'negotiation_id' => null,
                'invoice_id' => $invoiceId,
                'budget_commitment_id' => $commitmentId,
                'purchase_request_id' => $purchaseRequestId,
                'vendor_id' => null,
                'sub_activity_id' => $case['sub_activity_id'],
                'governance_node_id' => $entity['node_id'],
                'consortium_id' => null,
                'think_tank_member_id' => null,
                'reference_no' => self::PURCHASE_ORDER_PREFIX.'2026-'.$referenceToken.'-0'.$caseNumber,
                'po_title' => $this->limit('Purchase order: '.$case['sub_activity_name']),
                'po_type' => 'procurement',
                'supplier_reference' => 'DEMO-SUP-'.$referenceToken.'-'.$caseNumber,
                'contract_reference' => 'DEMO-CON-'.$referenceToken.'-'.$caseNumber,
                'buyer_contact_name' => 'Accounting Officer, '.$entity['name'],
                'buyer_contact_email' => 'procurement.'.strtolower($referenceToken).'@kenya-government.example.invalid',
                'buyer_contact_phone' => null,
                'vendor_contact_name' => 'Government Framework Supplier (Synthetic)',
                'vendor_contact_email' => 'supplier.'.strtolower($referenceToken).'@example.invalid',
                'vendor_contact_phone' => null,
                'billing_address' => 'National Treasury Building, Harambee Avenue, Nairobi, Kenya',
                'shipping_address' => $entity['name'].', Kenya',
                'delivery_location' => $entity['name'].', Kenya',
                'incoterm' => 'DAP',
                'delivery_terms' => 'Delivery after milestone verification and acceptance.',
                'payment_terms' => $caseNumber === 1 ? 'Payment on verified completion.' : 'Progress payment against verified delivery.',
                'warranty_terms' => 'Remedial action required for any rejected synthetic demonstration output.',
                'inspection_requirements' => 'Technical inspection and accounting-officer acceptance are required.',
                'special_instructions' => self::DATASET_TAG.' Synthetic purchase order for application demonstration.',
                'terms_conditions' => 'Subject to applicable Kenyan public procurement and financial management controls.',
                'supporting_document_path' => $evidenceDocument['path'],
                'supporting_document_name' => $evidenceDocument['name'],
                'supporting_document_mime_type' => $evidenceDocument['mime_type'],
                'supporting_document_size' => $evidenceDocument['size'],
                'amount' => $amount,
                'currency' => 'KES',
                'status' => $caseNumber === 1 ? 'paid' : 'partial_paid',
                'created_by' => $adminId,
                'issued_at' => '2026-07-25 09:00:00',
                'expected_delivery_date' => $caseNumber === 1 ? '2026-07-31' : '2026-08-15',
                'valid_until' => '2027-06-30',
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];

            foreach ($itemIds as $itemIndex => $itemId) {
                $paidAmount = $this->percentage($itemAmounts[$itemIndex], $paymentRatio);
                $receiptKey = $executionKey.'|receipt|'.($itemIndex + 1);
                $deliveryDate = $caseNumber === 1 ? '2026-07-31' : '2026-08-15';
                $rows['purchase_order_evidence'][] = [
                    'id' => $this->uuid('purchase-order-evidence', $receiptKey),
                    'purchase_order_id' => $purchaseOrderId,
                    'purchase_request_item_id' => $itemId,
                    'deliverable_id' => null,
                    'invoice_id' => $invoiceId,
                    'is_met' => true,
                    'notes' => self::DATASET_TAG.' Synthetic goods/service receipt and milestone verification.',
                    'documents' => json_encode([[
                        ...$evidenceDocument,
                        'display_name' => 'Synthetic delivery and verification evidence',
                        'uploaded_by' => $adminId,
                        'uploaded_at' => '2026-08-21T09:00:00+00:00',
                    ]]),
                    'deliverable_date' => $deliveryDate,
                    'delivered_unit_price' => $itemAmounts[$itemIndex],
                    'delivered_quantity' => 1,
                    'delivered_amount' => $itemAmounts[$itemIndex],
                    'vendor_submission_status' => null,
                    'vendor_submitted_at' => null,
                    'vendor_resubmission_requested_at' => null,
                    'vendor_resubmission_requested_by' => null,
                    'vendor_resubmission_note' => null,
                    'created_by' => $adminId,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
                $rows['disbursements'][] = [
                    'id' => $this->uuid('disbursement', $receiptKey),
                    'purchase_order_id' => $purchaseOrderId,
                    'purchase_request_item_id' => $itemId,
                    'deliverable_id' => null,
                    'procurement_id' => null,
                    'vendor_id' => null,
                    'sub_activity_id' => $case['sub_activity_id'],
                    'governance_node_id' => $entity['node_id'],
                    'consortium_id' => null,
                    'think_tank_member_id' => null,
                    'fund_allocation_id' => null,
                    'consortium_disbursement_request_id' => null,
                    'reference_no' => self::DISBURSEMENT_PREFIX.'2026-'.$referenceToken.'-0'.$caseNumber.'-0'.($itemIndex + 1),
                    'amount' => $paidAmount,
                    'currency' => 'KES',
                    'payment_method' => 'Bank Transfer',
                    'transfer_reference' => 'EFT-'.$referenceToken.'-'.$caseNumber.($itemIndex + 1),
                    'status' => 'paid',
                    'recipient_confirmation_status' => 'pending',
                    'recipient_confirmed_by' => null,
                    'recipient_confirmed_at' => null,
                    'recipient_confirmation_notes' => null,
                    'paid_at' => $caseNumber === 1 ? '2026-08-15 12:00:00' : '2026-08-21 12:00:00',
                    'created_by' => $adminId,
                    'notes' => self::DATASET_TAG.' Synthetic paid receipt for application demonstration.',
                    'signed_documents' => json_encode([[
                        ...$evidenceDocument,
                        'display_name' => 'Synthetic signed payment evidence',
                        'uploaded_by' => $adminId,
                        'uploaded_at' => '2026-08-21T09:00:00+00:00',
                        'is_digital_signature' => true,
                        'digital_signature_code' => 'KEN-DEMO-SIGN-'.$referenceToken.'-'.$caseNumber.($itemIndex + 1),
                    ]]),
                    'procurement_processing_status' => 'goods_receipt_recorded',
                    'procurement_notified_at' => $caseNumber === 1 ? '2026-08-10 09:00:00' : '2026-08-18 09:00:00',
                    'goods_receipt_reference' => 'GRN-'.$referenceToken.'-'.$caseNumber.($itemIndex + 1),
                    'goods_receipt_generated_at' => $caseNumber === 1 ? '2026-08-12 09:00:00' : '2026-08-19 09:00:00',
                    'goods_receipt_generated_by' => $adminId,
                    'sap_52_series_reference' => 'SAP52-'.$referenceToken.'-'.$caseNumber.($itemIndex + 1),
                    'sap_52_series_entered_at' => $caseNumber === 1 ? '2026-08-13 09:00:00' : '2026-08-20 09:00:00',
                    'sap_52_series_entered_by' => $adminId,
                    'procurement_processing_notes' => 'Synthetic Kenya government lifecycle completed.',
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
            }
        }
    }

    /**
     * @param  array<string, mixed>  $governance
     * @param  array<string, int>  $finance
     */
    private function validateSeededDataset(array $governance, array $finance): void
    {
        $errors = collect();
        $nodeIds = array_values($governance['node_ids']);
        $this->expect($errors, DB::table('myb_governance_nodes')->whereIn('id', $nodeIds)->count() === count($nodeIds), 'all Kenya governance nodes');
        $this->expect(
            $errors,
            DB::table('myb_governance_reporting_lines')->whereIn('child_node_id', $nodeIds)->count() === count($nodeIds) - 1,
            'one primary hierarchy line for every non-root Kenya node'
        );
        $this->expect(
            $errors,
            DB::table('myb_governance_nodes')->whereIn('code', [
                'GOM', 'OPC', 'MOAIWD', 'MOEST', 'MOFEPD', 'MOHS', 'MRA', 'RBM', 'MACRA',
            ])->doesntExist(),
            'no Malawi governance nodes'
        );
        $this->expect(
            $errors,
            DB::table('myb_programs')->where('program_id', 'PROG00001')->doesntExist(),
            'no legacy ATTP programme'
        );

        foreach (['sectors', 'programs', 'fundings', 'projects', 'activities', 'sub_activities', 'purchase_requests', 'purchase_request_items', 'commitments', 'work_plans', 'work_plan_item_reviews', 'purchase_orders', 'purchase_order_evidence', 'invoices', 'disbursements'] as $key) {
            $this->expect($errors, ($finance[$key] ?? 0) > 0, "seeded {$key}");
        }
        $this->expect($errors, $finance['sectors'] === $finance['programs'], 'one program per portfolio');
        $this->expect($errors, $finance['purchase_requests'] === $finance['commitments'], 'one commitment per execution request');
        $this->expect($errors, $finance['purchase_request_items'] === $finance['purchase_requests'] * 2, 'two items per execution request');
        $this->expect($errors, $finance['commitments'] === $finance['work_plans'], 'one approved work plan per commitment');
        $this->expect($errors, $finance['work_plan_item_reviews'] === $finance['purchase_request_items'], 'one approved review per work-plan item');
        $this->expect($errors, $finance['purchase_orders'] === $finance['invoices'], 'one invoice per purchase order');
        $this->expect($errors, $finance['purchase_order_evidence'] === $finance['purchase_request_items'], 'one receipt-evidence record per ordered item');
        $this->expect($errors, $finance['disbursements'] === $finance['purchase_orders'] * 2, 'two line-item receipts per purchase order');

        $invalidCommitmentStatuses = DB::table('myb_budget_commitments as commitments')
            ->join('myb_purchase_requests as requests', 'requests.id', '=', 'commitments.purchase_request_id')
            ->where('requests.reference_no', 'like', self::PURCHASE_REQUEST_PREFIX.'%')
            ->where('commitments.status', '<>', 'approved')
            ->count();
        $this->expect($errors, $invalidCommitmentStatuses === 0, 'all Kenya commitments remain approved and reportable');

        $invalidProjectCounts = DB::table('myb_programs as programs')
            ->select('programs.id')
            ->leftJoin('myb_projects as projects', 'projects.program_id', '=', 'programs.id')
            ->where('programs.program_id', 'like', self::PROGRAM_PREFIX.'%')
            ->groupBy('programs.id')
            ->havingRaw('count(projects.id) not between 3 and 4')
            ->get()
            ->count();
        $this->expect($errors, $invalidProjectCounts === 0, 'three or four projects per Kenya portfolio');

        $duplicateProjectAllocations = DB::table('myb_project_allocations as allocations')
            ->select('allocations.project_id', 'allocations.year')
            ->join('myb_projects as projects', 'projects.id', '=', 'allocations.project_id')
            ->where('projects.project_id', 'like', self::PROJECT_PREFIX.'%')
            ->groupBy('allocations.project_id', 'allocations.year')
            ->havingRaw('count(*) <> 1')
            ->get()
            ->count();
        $this->expect($errors, $duplicateProjectAllocations === 0, 'one project allocation per calendar year');

        $projectBudgetMismatches = DB::table('myb_projects as projects')
            ->select('projects.id', 'projects.total_budget')
            ->leftJoin('myb_project_allocations as allocations', 'allocations.project_id', '=', 'projects.id')
            ->where('projects.project_id', 'like', self::PROJECT_PREFIX.'%')
            ->groupBy('projects.id', 'projects.total_budget')
            ->havingRaw('abs(coalesce(sum(allocations.amount), 0) - projects.total_budget) > 0.01')
            ->get()
            ->count();
        $this->expect($errors, $projectBudgetMismatches === 0, 'project allocations reconcile to project budgets');

        $requestMismatches = DB::table('myb_purchase_requests as requests')
            ->where('requests.reference_no', 'like', self::PURCHASE_REQUEST_PREFIX.'%')
            ->whereRaw('(select coalesce(sum(items.amount), 0) from myb_purchase_request_items items where items.purchase_request_id = requests.id) <> requests.total_amount')
            ->orWhere(function (Builder $query): void {
                $query->where('requests.reference_no', 'like', self::PURCHASE_REQUEST_PREFIX.'%')
                    ->whereRaw('(select coalesce(sum(commitments.commitment_amount), 0) from myb_budget_commitments commitments where commitments.purchase_request_id = requests.id and commitments.status <> ?) <> requests.total_amount', ['cancelled']);
            })
            ->count();
        $this->expect($errors, $requestMismatches === 0, 'purchase requests reconcile to items and commitments');

        $overpaidOrders = DB::table('procurement_purchase_orders as orders')
            ->where('orders.reference_no', 'like', self::PURCHASE_ORDER_PREFIX.'%')
            ->whereRaw('(select coalesce(sum(disbursements.amount), 0) from procurement_disbursements disbursements where disbursements.purchase_order_id = orders.id and disbursements.paid_at is not null and disbursements.status in (?, ?, ?)) > orders.amount', ['completed', 'paid', 'fully_paid'])
            ->count();
        $this->expect($errors, $overpaidOrders === 0, 'no purchase order is overpaid');

        if ($errors->isNotEmpty()) {
            throw new RuntimeException('Kenya government seed validation failed: '.$errors->implode('; ').'.');
        }
    }

    private function resolveAdminId(): ?string
    {
        $configuredEmails = collect(config('super-admins.accounts', []))
            ->pluck('email')
            ->filter(fn (mixed $email): bool => is_string($email) && trim($email) !== '')
            ->map(fn (string $email): string => strtolower(trim($email)))
            ->unique()
            ->values()
            ->all();
        $configuredAdminId = $configuredEmails === []
            ? null
            : DB::table('users')
                ->whereIn(DB::raw('lower(email)'), $configuredEmails)
                ->orderBy('created_at')
                ->value('id');

        return $configuredAdminId
            ?: DB::table('users')->where('user_type', 'admin')->orderBy('created_at')->value('id')
            ?: DB::table('users')->orderBy('created_at')->value('id');
    }

    private function agencyCode(string $name): string
    {
        $words = preg_split('/[^A-Za-z0-9]+/', strtoupper($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $stopWords = ['AND', 'OF', 'THE', 'FOR', 'IN', 'TO'];
        $acronym = collect($words)
            ->reject(fn (string $word): bool => in_array($word, $stopWords, true))
            ->map(fn (string $word): string => substr($word, 0, 1))
            ->join('');
        $acronym = substr($acronym ?: 'AG', 0, 8);

        return 'KE-AG-'.$acronym.'-'.strtoupper(substr(hash('sha256', $name), 0, 6));
    }

    private function agencyKind(string $name): string
    {
        return preg_match('/\b(company|corporation|bank|fund|kenya power|kengen|ketraco|kenya seed)\b/i', $name)
            ? 'state_corporation'
            : 'agency';
    }

    private function projectBudget(string $kind, string $key): float
    {
        [$base, $step, $bands] = match ($kind) {
            'ministry' => [350_000_000, 25_000_000, 7],
            'independent_institution' => [180_000_000, 20_000_000, 7],
            'state_corporation' => [140_000_000, 15_000_000, 8],
            default => [90_000_000, 10_000_000, 12],
        };

        return (float) ($base + ($this->stableNumber($key, $bands) * $step));
    }

    /** @param array<int, int> $weights @return array<int, float> */
    private function splitAmount(float $amount, array $weights): array
    {
        if (array_sum($weights) !== 100) {
            throw new RuntimeException('Allocation weights must total 100 percent.');
        }

        $totalCents = (int) round($amount * 100);
        $remaining = $totalCents;
        $amounts = [];
        foreach ($weights as $index => $weight) {
            $cents = $index === array_key_last($weights)
                ? $remaining
                : intdiv($totalCents * $weight, 100);
            $amounts[] = $cents / 100;
            $remaining -= $cents;
        }

        return $amounts;
    }

    private function percentage(float $amount, int $percentage): float
    {
        return round($amount * ($percentage / 100), 2);
    }

    private function stableNumber(string $key, int $modulus): int
    {
        return hexdec(substr(hash('sha256', $key), 0, 8)) % $modulus;
    }

    private function limit(string $value, int $limit = 250): string
    {
        return mb_strlen($value) <= $limit ? $value : rtrim(mb_substr($value, 0, $limit - 3)).'...';
    }

    private function uuid(string $scope, string $key): string
    {
        $hash = md5('kenya-government-demo-v1|'.$scope.'|'.$key);

        return sprintf(
            '%s-%s-4%s-%s%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 13, 3),
            dechex((hexdec($hash[16]) & 0x3) | 0x8),
            substr($hash, 17, 3),
            substr($hash, 20, 12),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $uniqueBy
     * @param  array<int, string>|null  $updateColumns
     */
    private function upsertRows(
        string $table,
        array $rows,
        array $uniqueBy = ['id'],
        ?array $updateColumns = null,
    ): void {
        if ($rows === []) {
            return;
        }

        $updateColumns ??= array_values(array_diff(array_keys($rows[0]), $uniqueBy, ['id', 'created_at']));
        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table($table)->upsert($chunk, $uniqueBy, $updateColumns);
        }
    }

    private function expect(Collection $errors, bool $condition, string $expectation): void
    {
        if (! $condition) {
            $errors->push($expectation);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Services\KenyaProcurementEvaluationDataCleaner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class KenyaProcurementEvaluationSeeder extends Seeder
{
    public const UUID_NAMESPACE = 'kenya-procurement-evaluation-demo-v1';

    public const REFERENCE_TIME = '2026-08-22 12:00:00';

    private const REQUIRED_TABLES = [
        'users',
        'vendor_categories',
        'myb_sectors',
        'myb_programs',
        'myb_projects',
        'myb_activities',
        'myb_sub_activities',
        'myb_resource_categories',
        'myb_resources',
        'procurements',
        'dynamic_forms',
        'dynamic_form_fields',
        'procurement_form_assignments',
        'form_submissions',
        'form_submission_values',
        'procurement_submission_screenings',
        'prescreening_templates',
        'prescreening_sections',
        'prescreening_criteria',
        'prescreening_template_procurements',
        'prescreening_assignments',
        'prescreening_evaluations',
        'prescreening_results',
        'evaluations',
        'evaluation_sections',
        'evaluation_criteria',
        'procurement_evaluations',
        'evaluation_assignments',
        'evaluation_submissions',
        'evaluation_criteria_scores',
        'evaluation_section_scores',
        'procurement_contract_negotiations',
        'procurement_deliverables',
        'procurement_purchase_orders',
        'procurement_purchase_order_deliverables',
        'procurement_invoices',
        'procurement_invoice_deliverables',
        'procurement_disbursements',
        'vendor_sub_activity_assignments',
        'vendor_reports',
        'procurement_audit_logs',
    ];

    /** @var array<string, mixed> */
    private array $catalogue;

    /** @var array<int, string> */
    private array $adminIds = [];

    /** @var array<int, array<string, mixed>> */
    private array $vendors = [];

    /** @var array<string, array<string, mixed>> */
    private array $portfolioStructures = [];

    /** @var array<string, mixed> */
    private array $prescreeningStructure = [];

    public function run(): void
    {
        $this->assertSchemaIsReady();
        $this->catalogue = require database_path('seeders/data/kenya_procurement_evaluation.php');

        $result = DB::transaction(function (): array {
            $removed = app(KenyaProcurementEvaluationDataCleaner::class)->purge();
            $this->adminIds = $this->resolveAdminIds();
            $this->vendors = $this->seedVendorCatalogue();

            [$projects, $paidPurchaseOrders] = $this->loadProjectExecutionScope();
            $this->portfolioStructures = $this->seedPortfolioStructures($projects);
            $this->prescreeningStructure = $this->seedPrescreeningStructure();

            $totals = $this->seedProjectLifecycles($projects, $paidPurchaseOrders);
            $this->validateSeededDataset($projects, $totals);

            return compact('removed', 'totals');
        }, 3);

        $totals = $result['totals'];
        $this->command?->info(sprintf(
            'Kenya procurement and evaluation dataset ready: %d procurements, %d vendors, %d applications, %d prescreening decisions, %d technical/financial submissions, and %d linked paid KES purchase orders.',
            $totals['procurements'],
            count($this->vendors),
            $totals['form_submissions'],
            $totals['prescreening_results'],
            $totals['evaluation_submissions'],
            $totals['financial_links'],
        ));
        $this->command?->warn(
            'Every vendor identity, bid, score and procurement amount installed by KenyaProcurementEvaluationSeeder is synthetic demonstration data.'
        );
    }

    private function assertSchemaIsReady(): void
    {
        $missing = collect(self::REQUIRED_TABLES)
            ->reject(fn (string $table): bool => Schema::hasTable($table))
            ->values();

        if ($missing->isNotEmpty()) {
            throw new RuntimeException(
                'Run all migrations before KenyaProcurementEvaluationSeeder. Missing tables: '.$missing->implode(', ')
            );
        }

        if (! Schema::hasColumn('procurements', 'portfolio_id')) {
            throw new RuntimeException(
                'Run migrations before KenyaProcurementEvaluationSeeder; procurements.portfolio_id is required.'
            );
        }
    }

    /** @return array<int, string> */
    private function resolveAdminIds(): array
    {
        $configuredEmails = collect(config('super-admins.accounts', []))
            ->pluck('email')
            ->filter(fn (mixed $email): bool => is_string($email) && trim($email) !== '')
            ->map(fn (string $email): string => strtolower(trim($email)))
            ->unique()
            ->values();

        $configured = $configuredEmails->isEmpty()
            ? collect()
            : DB::table('users')
                ->whereIn(DB::raw('lower(email)'), $configuredEmails->all())
                ->where('user_type', 'admin')
                ->where(fn ($query) => $query->whereNull('is_disabled')->orWhere('is_disabled', false))
                ->where(fn ($query) => $query->whereNull('is_blacklisted')->orWhere('is_blacklisted', false))
                ->get(['id', 'email'])
                ->keyBy(fn (object $user): string => strtolower((string) $user->email));

        $adminIds = $configuredEmails
            ->map(fn (string $email): ?string => $configured->get($email)?->id)
            ->filter()
            ->values();

        if ($adminIds->count() < 2) {
            $adminIds = $adminIds->merge(
                DB::table('users')
                    ->where('user_type', 'admin')
                    ->where(fn ($query) => $query->whereNull('is_disabled')->orWhere('is_disabled', false))
                    ->where(fn ($query) => $query->whereNull('is_blacklisted')->orWhere('is_blacklisted', false))
                    ->orderBy('created_at')
                    ->pluck('id')
            )->unique()->values();
        }

        if ($adminIds->count() < 2) {
            throw new RuntimeException(
                'At least two active administrator accounts are required. Run MasterAdminSeeder or the default DatabaseSeeder first.'
            );
        }

        return $adminIds->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function seedVendorCatalogue(): array
    {
        foreach ($this->catalogue['vendor_categories'] as $category) {
            $existingId = DB::table('vendor_categories')
                ->where('name', $category['name'])
                ->value('id');

            if ($existingId) {
                continue;
            }

            DB::table('vendor_categories')->insert([
                'id' => $this->uuid('vendor-category', $category['name']),
                'name' => $category['name'],
                'description' => KenyaProcurementEvaluationDataCleaner::DATASET_TAG.' '.$category['description'],
                'is_active' => true,
                'created_by' => $this->adminIds[0],
                'created_at' => self::REFERENCE_TIME,
                'updated_at' => self::REFERENCE_TIME,
            ]);
        }

        $vendors = [];
        foreach (array_values($this->catalogue['vendors']) as $index => $definition) {
            $number = $index + 1;
            $email = sprintf('kenya.demo.vendor.%02d@example.invalid', $number);
            $id = $this->uuid('vendor', $email);
            $existing = DB::table('users')
                ->whereRaw('lower(email) = ?', [$email])
                ->first();

            if ($existing && (string) $existing->id !== $id) {
                throw new RuntimeException("Synthetic vendor email collision for {$email}.");
            }

            $profile = [
                'name' => $definition['name'],
                'email' => $email,
                'user_type' => 'vendor',
                'vendor_category' => $definition['category'],
                'payment_method_preference' => 'bank_transfer',
                'payment_bank_name' => 'Synthetic Kenya Demo Bank',
                'payment_account_name' => $definition['name'],
                'payment_account_number' => sprintf('DEMO-KE-%06d', $number),
                'payment_swift_code' => 'DEMOkenx',
                'payment_iban' => null,
                'payment_mobile_provider' => null,
                'payment_mobile_number' => null,
                'payment_tax_id' => sprintf('DEMO-PIN-%06d', $number),
                'payment_address' => 'Synthetic vendor address, Nairobi, Kenya',
                'updated_at' => self::REFERENCE_TIME,
            ];

            if (! $existing) {
                $row = array_merge($profile, [
                    'id' => $id,
                    'email_verified_at' => self::REFERENCE_TIME,
                    'otp_verified_at' => self::REFERENCE_TIME,
                    'must_change_password' => true,
                    'password_changed_at' => null,
                    'is_disabled' => false,
                    'disabled_at' => null,
                    'disabled_reason' => null,
                    'is_blacklisted' => false,
                    'blacklisted_at' => null,
                    'blacklisted_reason' => null,
                    'role_id' => null,
                    'created_at' => self::REFERENCE_TIME,
                ]);
                $row['password'] = Hash::make(Str::password(64));
                DB::table('users')->insert($row);
            } else {
                DB::table('users')->where('id', $id)->update($profile);
            }

            $active = DB::table('users')->where('id', $id)->first(['is_disabled', 'is_blacklisted']);
            if (($active->is_disabled ?? false) || ($active->is_blacklisted ?? false)) {
                throw new RuntimeException(
                    "Synthetic vendor {$email} is disabled or blacklisted. Resolve that security state before seeding."
                );
            }

            $vendors[] = [
                'id' => $id,
                'name' => $definition['name'],
                'email' => $email,
                'category' => $definition['category'],
                'registration' => sprintf('DEMO-BR-%06d', $number),
                'tax_reference' => sprintf('DEMO-TCC-%06d', $number),
            ];
        }

        return $vendors;
    }

    /**
     * @return array{0:Collection<int, object>,1:Collection<string, object>}
     */
    private function loadProjectExecutionScope(): array
    {
        $projects = DB::table('myb_projects as project')
            ->join('myb_programs as program', 'program.id', '=', 'project.program_id')
            ->join('myb_sectors as portfolio', 'portfolio.id', '=', 'program.sector_id')
            ->where('program.program_id', 'like', KenyaGovernmentSeeder::PROGRAM_PREFIX.'%')
            ->where('project.project_id', 'like', KenyaGovernmentSeeder::PROJECT_PREFIX.'%')
            ->whereRaw("coalesce(project.description, '') like ?", ['%'.KenyaGovernmentSeeder::DATASET_TAG.'%'])
            ->orderBy('program.program_id')
            ->orderBy('project.project_id')
            ->get([
                'project.id as project_uuid',
                'project.project_id as project_code',
                'project.name as project_name',
                'project.total_budget',
                'program.id as program_uuid',
                'program.program_id as program_code',
                'portfolio.id as portfolio_id',
                'portfolio.name as portfolio_name',
                'portfolio.governance_node_id',
            ]);

        if ($projects->isEmpty()) {
            throw new RuntimeException(
                'No Kenya government projects were found. Run KenyaGovernmentSeeder before this standalone seeder.'
            );
        }

        $purchaseOrders = DB::table('procurement_purchase_orders as purchase_order')
            ->join('myb_sub_activities as sub_activity', 'sub_activity.id', '=', 'purchase_order.sub_activity_id')
            ->join('myb_activities as activity', 'activity.id', '=', 'sub_activity.activity_id')
            ->where('purchase_order.reference_no', 'like', KenyaGovernmentSeeder::PURCHASE_ORDER_PREFIX.'%')
            ->where('purchase_order.status', 'paid')
            ->whereIn('activity.project_id', $projects->pluck('project_uuid')->all())
            ->orderBy('purchase_order.reference_no')
            ->get([
                'purchase_order.id',
                'purchase_order.invoice_id',
                'purchase_order.reference_no',
                'purchase_order.amount',
                'purchase_order.currency',
                'purchase_order.sub_activity_id',
                'purchase_order.governance_node_id',
                'activity.id as activity_id',
                'activity.project_id as project_uuid',
            ])
            ->groupBy('project_uuid');

        $invalidProject = $projects->first(function (object $project) use ($purchaseOrders): bool {
            $orders = $purchaseOrders->get($project->project_uuid, collect());

            return $orders->count() !== 1
                || ! $orders->first()?->invoice_id
                || (float) $orders->first()?->amount <= 0
                || $orders->first()?->currency !== 'KES'
                || (string) $orders->first()?->governance_node_id !== (string) $project->governance_node_id;
        });

        if ($invalidProject) {
            throw new RuntimeException(
                "Kenya project {$invalidProject->project_code} must have exactly one paid KES purchase order on the same governance node."
            );
        }

        return [
            $projects,
            $purchaseOrders->map(fn (Collection $orders): object => $orders->first()),
        ];
    }

    /**
     * @param  Collection<int, object>  $projects
     * @return array<string, array<string, mixed>>
     */
    private function seedPortfolioStructures(Collection $projects): array
    {
        $structures = [];
        $resourceCategories = [];
        $resources = [];
        $evaluations = [];
        $sections = [];
        $criteria = [];

        foreach ($projects->unique('portfolio_id')->values() as $portfolio) {
            $portfolioId = (string) $portfolio->portfolio_id;
            $categoryId = $this->uuid('resource-category', $portfolioId);
            $resourceId = $this->uuid('resource', $portfolioId);
            $resourceCategories[] = [
                'id' => $categoryId,
                'governance_node_id' => $portfolio->governance_node_id,
                'name' => $this->limit($portfolio->portfolio_name.' Procurement Resources'),
                'description' => KenyaProcurementEvaluationDataCleaner::DATASET_TAG.' Synthetic procurement resource category for this Kenya portfolio.',
                'status' => 'active',
                'created_by' => $this->adminIds[0],
                'created_at' => self::REFERENCE_TIME,
                'updated_at' => self::REFERENCE_TIME,
            ];
            $resources[] = [
                'id' => $resourceId,
                'resource_category_id' => $categoryId,
                'governance_node_id' => $portfolio->governance_node_id,
                'name' => $this->limit($portfolio->portfolio_name.' Competitive Procurement Resource'),
                'reference_code' => KenyaProcurementEvaluationDataCleaner::RESOURCE_PREFIX.strtoupper(substr(hash('sha256', $portfolioId), 0, 12)),
                'description' => KenyaProcurementEvaluationDataCleaner::DATASET_TAG.' Synthetic portfolio-scoped resource for public procurement applications.',
                'status' => 'active',
                'is_human_resource' => false,
                'created_by' => $this->adminIds[0],
                'created_at' => self::REFERENCE_TIME,
                'updated_at' => self::REFERENCE_TIME,
            ];

            $structures[$portfolioId] = ['resource_id' => $resourceId, 'evaluations' => []];
            foreach ($this->catalogue['evaluation_phases'] as $phase => $phaseDefinition) {
                $evaluationId = $this->uuid('evaluation', $portfolioId.'|'.$phase);
                $structures[$portfolioId]['evaluations'][$phase] = [
                    'id' => $evaluationId,
                    'sections' => [],
                ];
                $evaluations[] = [
                    'id' => $evaluationId,
                    'name' => $this->limit($portfolio->portfolio_name.' '.$phaseDefinition['name']),
                    'description' => KenyaProcurementEvaluationDataCleaner::DATASET_TAG.' Reusable 100-point '.$phase.' evaluation template for '.$portfolio->portfolio_name.'.',
                    'status' => 'active',
                    'type' => 'services',
                    'portfolio_id' => $portfolioId,
                    'is_portfolio_custom' => true,
                    'think_tank_member_id' => null,
                    'evaluation_phase' => $phase,
                    'procurement_id' => null,
                    'created_by' => $this->adminIds[0],
                    'created_at' => self::REFERENCE_TIME,
                    'updated_at' => self::REFERENCE_TIME,
                ];

                foreach (array_values($phaseDefinition['sections']) as $sectionIndex => $sectionDefinition) {
                    $sectionId = $this->uuid('evaluation-section', $portfolioId.'|'.$phase.'|'.$sectionDefinition['key']);
                    $sectionMap = ['id' => $sectionId, 'criteria' => []];
                    $sections[] = [
                        'id' => $sectionId,
                        'evaluation_id' => $evaluationId,
                        'parent_section_id' => null,
                        'name' => $sectionDefinition['name'],
                        'description' => 'Synthetic '.$phase.' scoring section.',
                        'show_subtotal' => true,
                        'sort_order' => $sectionIndex + 1,
                        'created_at' => self::REFERENCE_TIME,
                        'updated_at' => self::REFERENCE_TIME,
                    ];

                    foreach ($sectionDefinition['criteria'] as $criterionDefinition) {
                        $criterionId = $this->uuid(
                            'evaluation-criterion',
                            $portfolioId.'|'.$phase.'|'.$sectionDefinition['key'].'|'.$criterionDefinition['key']
                        );
                        $sectionMap['criteria'][] = [
                            'id' => $criterionId,
                            'max_score' => (float) $criterionDefinition['max_score'],
                        ];
                        $criteria[] = [
                            'id' => $criterionId,
                            'evaluation_section_id' => $sectionId,
                            'name' => $criterionDefinition['name'],
                            'description' => 'Score against the published '.$phase.' evaluation requirements.',
                            'max_score' => $criterionDefinition['max_score'],
                            'created_at' => self::REFERENCE_TIME,
                            'updated_at' => self::REFERENCE_TIME,
                        ];
                    }
                    $structures[$portfolioId]['evaluations'][$phase]['sections'][] = $sectionMap;
                }
            }
        }

        $this->upsertRows('myb_resource_categories', $resourceCategories);
        $this->upsertRows('myb_resources', $resources);
        $this->upsertRows('evaluations', $evaluations);
        $this->upsertRows('evaluation_sections', $sections);
        $this->upsertRows('evaluation_criteria', $criteria);

        return $structures;
    }

    /** @return array<string, mixed> */
    private function seedPrescreeningStructure(): array
    {
        $templateId = $this->uuid('prescreening-template', 'national-standard');
        $this->upsertRows('prescreening_templates', [[
            'id' => $templateId,
            'name' => 'Kenya Demo Standard Vendor Eligibility Prescreening',
            'description' => KenyaProcurementEvaluationDataCleaner::DATASET_TAG.' Shared nine-gate vendor eligibility and application completeness template.',
            'is_active' => true,
            'created_by' => $this->adminIds[0],
            'created_at' => self::REFERENCE_TIME,
            'updated_at' => self::REFERENCE_TIME,
        ]]);

        $sections = [];
        $criteria = [];
        $criterionMap = [];
        foreach (array_values($this->catalogue['prescreening_sections']) as $sectionIndex => $definition) {
            $sectionId = $this->uuid('prescreening-section', $definition['key']);
            $sections[] = [
                'id' => $sectionId,
                'prescreening_template_id' => $templateId,
                'name' => $definition['name'],
                'description' => 'Mandatory synthetic eligibility checks for seeded vendor applications.',
                'sort_order' => $sectionIndex + 1,
                'created_at' => self::REFERENCE_TIME,
                'updated_at' => self::REFERENCE_TIME,
            ];

            foreach (array_values($definition['criteria']) as $criterionIndex => $criterion) {
                $criterionId = $this->uuid('prescreening-criterion', $definition['key'].'|'.$criterion['key']);
                $criterionMap[] = ['id' => $criterionId, 'field_key' => $criterion['key']];
                $criteria[] = [
                    'id' => $criterionId,
                    'prescreening_template_id' => $templateId,
                    'prescreening_section_id' => $sectionId,
                    'name' => $criterion['name'],
                    'description' => 'Mandatory yes/no eligibility gate.',
                    'field_key' => $criterion['key'],
                    'evaluation_type' => 'yes_no',
                    'min_value' => null,
                    'is_mandatory' => true,
                    'sort_order' => (string) ($criterionIndex + 1),
                    'created_at' => self::REFERENCE_TIME,
                    'updated_at' => self::REFERENCE_TIME,
                ];
            }
        }
        $this->upsertRows('prescreening_sections', $sections);
        $this->upsertRows('prescreening_criteria', $criteria);

        return ['id' => $templateId, 'criteria' => $criterionMap];
    }

    /**
     * @param  Collection<int, object>  $projects
     * @param  Collection<string, object>  $purchaseOrders
     * @return array<string, int>
     */
    private function seedProjectLifecycles(Collection $projects, Collection $purchaseOrders): array
    {
        $totals = [];
        $rows = $this->newLifecycleRows();

        foreach ($projects->values() as $index => $project) {
            $this->appendProjectLifecycle(
                $rows,
                $project,
                $purchaseOrders->get($project->project_uuid),
                $index,
            );

            if (($index + 1) % 40 === 0) {
                $this->persistLifecycleRows($rows, $totals);
                $rows = $this->newLifecycleRows();
            }
        }

        $this->persistLifecycleRows($rows, $totals);

        return $totals;
    }

    /** @return array<string, array<int, array<string, mixed>>> */
    private function newLifecycleRows(): array
    {
        return collect([
            'procurements',
            'dynamic_forms',
            'dynamic_form_fields',
            'procurement_form_assignments',
            'form_submissions',
            'form_submission_values',
            'procurement_submission_screenings',
            'prescreening_template_procurements',
            'prescreening_assignments',
            'prescreening_evaluations',
            'prescreening_results',
            'procurement_evaluations',
            'evaluation_assignments',
            'evaluation_submissions',
            'evaluation_criteria_scores',
            'evaluation_section_scores',
            'procurement_contract_negotiations',
            'procurement_deliverables',
            'procurement_purchase_order_deliverables',
            'procurement_invoice_deliverables',
            'vendor_sub_activity_assignments',
            'vendor_reports',
            'procurement_audit_logs',
            'financial_links',
        ])->mapWithKeys(fn (string $table): array => [$table => []])->all();
    }

    /**
     * @param  array<string, array<int, array<string, mixed>>>  $rows
     */
    private function appendProjectLifecycle(array &$rows, object $project, object $purchaseOrder, int $projectIndex): void
    {
        $projectKey = (string) $project->project_uuid;
        $procurementId = $this->uuid('procurement', $projectKey);
        $formId = $this->uuid('application-form', $projectKey);
        $negotiationId = $this->uuid('negotiation', $projectKey);
        $referenceToken = strtoupper(substr(hash('sha256', $projectKey), 0, 12));
        $portfolio = $this->portfolioStructures[(string) $project->portfolio_id];
        $prescreenerId = $this->adminIds[($projectIndex + 1) % count($this->adminIds)];
        $reviewerIds = $this->reviewerPair($projectIndex);
        $bidderOffset = $this->stableNumber($projectKey, count($this->vendors));
        $bidderIndexes = [
            $bidderOffset,
            ($bidderOffset + 7) % count($this->vendors),
            ($bidderOffset + 13) % count($this->vendors),
        ];
        $bidders = array_map(fn (int $vendorIndex): array => $this->vendors[$vendorIndex], $bidderIndexes);
        $submissionIds = array_map(
            fn (int $rank): string => $this->uuid('application', $projectKey.'|'.($rank + 1)),
            [0, 1, 2]
        );
        $agreedAmount = round((float) $purchaseOrder->amount, 2);
        $bidAmounts = [
            round($agreedAmount * 1.02, 2),
            round($agreedAmount * 1.08, 2),
            round($agreedAmount * 0.95, 2),
        ];

        $rows['procurements'][] = [
            'id' => $procurementId,
            'consortium_id' => null,
            'think_tank_member_id' => null,
            'think_tank_procurement_plan_id' => null,
            'procurement_owner_type' => 'secretariat',
            'oversight_status' => 'visible',
            'resource_id' => $portfolio['resource_id'],
            'governance_node_id' => $project->governance_node_id,
            'portfolio_id' => $project->portfolio_id,
            'title' => $this->limit($project->project_name.' - Competitive Delivery Package'),
            'slug' => 'kenya-procurement-'.strtolower($referenceToken),
            'reference_no' => KenyaProcurementEvaluationDataCleaner::PROCUREMENT_PREFIX.$referenceToken,
            'description' => KenyaProcurementEvaluationDataCleaner::DATASET_TAG.' Synthetic competitive procurement for Kenya project '.$project->project_code.'. Includes application, screening, technical and financial evaluation, award and paid execution evidence.',
            'fiscal_year' => 2026,
            'application_start_date' => '2026-06-01',
            'application_end_date' => '2026-06-15',
            'application_duration_days' => 15,
            'estimated_budget' => round($agreedAmount * 1.15, 2),
            'status' => 'awarded',
            'visibility_type' => 'public',
            'cover_image_path' => null,
            'publication_version' => 1,
            'recalled_at' => null,
            'recalled_by' => null,
            'recall_reason' => null,
            'republished_at' => null,
            'vendor_categories' => json_encode(array_values(array_unique(array_column($bidders, 'category'))), JSON_THROW_ON_ERROR),
            'awarded_submission_id' => $submissionIds[0],
            'awarded_vendor_id' => $bidders[0]['id'],
            'awarded_at' => '2026-06-28 14:00:00',
            'created_by' => $this->adminIds[0],
            'created_at' => '2026-05-25 09:00:00',
            'updated_at' => self::REFERENCE_TIME,
        ];

        $rows['dynamic_forms'][] = [
            'id' => $formId,
            'resource_id' => $portfolio['resource_id'],
            'name' => $this->limit('Vendor Bid Application - '.$project->project_code),
            'applies_to' => 'submission',
            'status' => 'approved',
            'is_active' => true,
            'created_by' => $this->adminIds[0],
            'procurement_id' => $procurementId,
            'submitted_at' => '2026-05-26 10:00:00',
            'approved_at' => '2026-05-27 10:00:00',
            'approved_by' => $this->adminIds[0],
            'rejection_reason' => null,
            'created_at' => '2026-05-25 10:00:00',
            'updated_at' => self::REFERENCE_TIME,
        ];
        foreach (array_values($this->catalogue['application_fields']) as $fieldIndex => $field) {
            $rows['dynamic_form_fields'][] = [
                'id' => $this->uuid('application-field', $projectKey.'|'.$field['key']),
                'form_id' => $formId,
                'label' => $field['label'],
                'help_text' => 'Synthetic demonstration response field.',
                'placeholder' => null,
                'field_key' => $field['key'],
                'field_type' => $field['type'],
                'is_required' => $field['required'],
                'options' => isset($field['options']) ? implode(', ', $field['options']) : null,
                'validation_rules' => null,
                'sort_order' => (string) $fieldIndex,
                'created_by' => $this->adminIds[0],
                'created_at' => '2026-05-25 10:00:00',
                'updated_at' => self::REFERENCE_TIME,
            ];
        }
        $rows['procurement_form_assignments'][] = [
            'id' => $this->uuid('form-assignment', $projectKey),
            'procurement_id' => $procurementId,
            'form_id' => $formId,
            'stage' => 'submission',
            'created_by' => $this->adminIds[0],
            'created_at' => '2026-05-27 10:00:00',
            'updated_at' => self::REFERENCE_TIME,
        ];
        $rows['prescreening_template_procurements'][] = [
            'id' => $this->uuid('prescreening-link', $projectKey),
            'procurement_id' => $procurementId,
            'prescreening_template_id' => $this->prescreeningStructure['id'],
            'assigned_by' => $this->adminIds[0],
            'assigned_at' => '2026-05-27 11:00:00',
        ];
        $rows['prescreening_assignments'][] = [
            'id' => $this->uuid('prescreening-assignment', $projectKey),
            'procurement_id' => $procurementId,
            'user_id' => $prescreenerId,
            'assigned_by' => $this->adminIds[0],
            'assigned_at' => '2026-06-16 08:00:00',
        ];
        $rows['procurement_evaluations'][] = [
            'id' => $this->uuid('procurement-evaluation', $projectKey),
            'procurement_id' => $procurementId,
            'evaluation_id' => $portfolio['evaluations']['technical']['id'],
            'created_at' => '2026-06-18 17:00:00',
            'updated_at' => self::REFERENCE_TIME,
        ];

        foreach ($bidders as $candidateIndex => $vendor) {
            $passed = $candidateIndex < 2;
            $submissionId = $submissionIds[$candidateIndex];
            $submittedAt = sprintf('2026-06-%02d 10:00:00', 10 + $candidateIndex);
            $rows['form_submissions'][] = [
                'id' => $submissionId,
                'procurement_id' => $procurementId,
                'procurement_submission_code' => sprintf('KEN-APP-%s-%d', $referenceToken, $candidateIndex + 1),
                'form_id' => $formId,
                'submitted_by' => $vendor['id'],
                'assigned_prescreener_id' => $prescreenerId,
                'status' => $passed ? 'prescreen_passed' : 'prescreen_failed',
                'submitted_at' => $submittedAt,
                'publication_version' => 1,
                'vendor_response' => null,
                'resubmitted_at' => null,
                'withdrawn_at' => null,
                'withdrawal_reason' => null,
                'created_at' => $submittedAt,
                'updated_at' => self::REFERENCE_TIME,
            ];

            foreach ($this->applicationValues($vendor, $candidateIndex, $bidAmounts[$candidateIndex]) as $fieldKey => $value) {
                $rows['form_submission_values'][] = [
                    'id' => $this->uuid('application-value', $projectKey.'|'.$candidateIndex.'|'.$fieldKey),
                    'submission_id' => $submissionId,
                    'field_key' => $fieldKey,
                    'value' => $this->limit((string) $value),
                    'created_at' => $submittedAt,
                    'updated_at' => self::REFERENCE_TIME,
                ];
            }

            $rows['procurement_submission_screenings'][] = [
                'id' => $this->uuid('international-screening', $projectKey.'|'.$candidateIndex),
                'submission_id' => $submissionId,
                'checked_by' => $prescreenerId,
                'reviewed_by' => $prescreenerId,
                'provider' => 'synthetic-seed',
                'checked_via' => 'seeded_demo',
                'request_status' => 'success',
                'review_decision' => 'fit',
                'entity_name' => $vendor['name'],
                'entity_country' => 'Kenya',
                'risk_level' => 'clear',
                'total_matches' => 0,
                'is_flagged' => false,
                'error_message' => null,
                'review_notes' => 'Synthetic clear screening result; no live sanctions provider was queried.',
                'last_checked_at' => '2026-06-16 10:00:00',
                'reviewed_at' => '2026-06-16 11:00:00',
                'response_payload' => json_encode([
                    'synthetic' => true,
                    'dataset_tag' => KenyaProcurementEvaluationDataCleaner::DATASET_TAG,
                ], JSON_THROW_ON_ERROR),
                'created_at' => '2026-06-16 10:00:00',
                'updated_at' => self::REFERENCE_TIME,
            ];

            $failedCriterion = collect($this->prescreeningStructure['criteria'])
                ->search(fn (array $criterion): bool => $criterion['field_key'] === 'beneficial_ownership_disclosure');
            $failedCriterion = $failedCriterion === false ? 0 : $failedCriterion;
            foreach ($this->prescreeningStructure['criteria'] as $criterionIndex => $criterion) {
                $criterionPassed = $passed || $criterionIndex !== $failedCriterion;
                $rows['prescreening_evaluations'][] = [
                    'id' => $this->uuid('prescreening-evaluation', $projectKey.'|'.$candidateIndex.'|'.$criterion['id']),
                    'submission_id' => $submissionId,
                    'prescreening_template_id' => $this->prescreeningStructure['id'],
                    'criterion_id' => $criterion['id'],
                    'evaluator_id' => $prescreenerId,
                    'evaluation_value' => $criterionPassed ? 'Yes' : 'No',
                    'is_passed' => $criterionPassed,
                    'remarks' => $criterionPassed
                        ? 'Mandatory requirement verified in the synthetic application.'
                        : 'Synthetic application did not satisfy this mandatory gate.',
                    'evaluated_at' => '2026-06-17 14:00:00',
                ];
            }
            $rows['prescreening_results'][] = [
                'id' => $this->uuid('prescreening-result', $projectKey.'|'.$candidateIndex),
                'submission_id' => $submissionId,
                'prescreening_template_id' => $this->prescreeningStructure['id'],
                'total_criteria' => count($this->prescreeningStructure['criteria']),
                'passed_criteria' => $passed ? count($this->prescreeningStructure['criteria']) : count($this->prescreeningStructure['criteria']) - 1,
                'failed_criteria' => $passed ? 0 : 1,
                'final_status' => $passed ? 'passed' : 'failed',
                'evaluated_by' => $prescreenerId,
                'evaluated_at' => '2026-06-18 14:00:00',
                'is_locked' => true,
                'rework_requested_by' => null,
                'rework_requested_at' => null,
            ];

            if ($passed) {
                $this->appendEvaluationResults(
                    $rows,
                    $projectKey,
                    $procurementId,
                    $submissionId,
                    (string) $project->portfolio_id,
                    $candidateIndex,
                    $reviewerIds,
                );
            }
        }

        $rows['procurement_contract_negotiations'][] = [
            'id' => $negotiationId,
            'procurement_id' => $procurementId,
            'submission_id' => $submissionIds[0],
            'vendor_id' => $bidders[0]['id'],
            'proposed_amount' => $bidAmounts[0],
            'agreed_amount' => $agreedAmount,
            'status' => 'agreed',
            'notes' => KenyaProcurementEvaluationDataCleaner::DATASET_TAG.' Synthetic negotiation concluded after combined technical and financial ranking.',
            'termination_reason' => null,
            'terminated_by' => null,
            'terminated_at' => null,
            'created_by' => $this->adminIds[0],
            'agreed_at' => '2026-06-27 15:00:00',
            'created_at' => '2026-06-26 09:00:00',
            'updated_at' => self::REFERENCE_TIME,
        ];

        $deliverableAmounts = $this->splitAmount($agreedAmount, [20, 35, 45]);
        $deliverableDefinitions = [
            ['title' => 'Inception, mobilization and approved delivery plan', 'start' => '2026-07-01', 'end' => '2026-07-10'],
            ['title' => 'Core project outputs and verified implementation evidence', 'start' => '2026-07-11', 'end' => '2026-07-31'],
            ['title' => 'Final acceptance, knowledge transfer and close-out', 'start' => '2026-08-01', 'end' => '2026-08-15'],
        ];
        foreach ($deliverableDefinitions as $deliverableIndex => $definition) {
            $deliverableId = $this->uuid('deliverable', $projectKey.'|'.($deliverableIndex + 1));
            $rows['procurement_deliverables'][] = [
                'id' => $deliverableId,
                'procurement_id' => $procurementId,
                'vendor_id' => $bidders[0]['id'],
                'title' => $definition['title'],
                'type' => 'deliverable',
                'description' => KenyaProcurementEvaluationDataCleaner::DATASET_TAG.' Synthetic contract deliverable linked to paid execution evidence.',
                'timeline_start' => $definition['start'],
                'timeline_end' => $definition['end'],
                'amount' => $deliverableAmounts[$deliverableIndex],
                'currency' => 'KES',
                'sequence' => $deliverableIndex + 1,
                'frequency' => 'one_time',
                'status' => 'completed',
                'vendor_approval_status' => 'approved',
                'admin_approval_status' => 'approved',
                'vendor_approved_by' => $bidders[0]['id'],
                'vendor_approved_at' => $definition['end'].' 12:00:00',
                'admin_approved_by' => $this->adminIds[0],
                'admin_approved_at' => $definition['end'].' 15:00:00',
                'completed_at' => $definition['end'].' 15:00:00',
                'cancelled_at' => null,
                'notes' => 'Synthetic completion record for demonstration and reporting.',
                'created_by' => $this->adminIds[0],
                'deleted_at' => null,
                'deleted_by' => null,
                'created_at' => '2026-06-28 15:00:00',
                'updated_at' => self::REFERENCE_TIME,
            ];
            $rows['procurement_purchase_order_deliverables'][] = [
                'purchase_order_id' => $purchaseOrder->id,
                'deliverable_id' => $deliverableId,
                'created_at' => '2026-06-28 15:00:00',
                'updated_at' => self::REFERENCE_TIME,
            ];
            $rows['procurement_invoice_deliverables'][] = [
                'invoice_id' => $purchaseOrder->invoice_id,
                'deliverable_id' => $deliverableId,
                'created_at' => '2026-08-15 15:00:00',
                'updated_at' => self::REFERENCE_TIME,
            ];
        }

        $rows['vendor_sub_activity_assignments'][] = [
            'id' => $this->uuid('vendor-sub-activity', $projectKey),
            'vendor_id' => $bidders[0]['id'],
            'program_id' => $project->program_uuid,
            'project_id' => $project->project_uuid,
            'activity_id' => $purchaseOrder->activity_id,
            'sub_activity_id' => $purchaseOrder->sub_activity_id,
            'created_at' => '2026-06-28 15:00:00',
            'updated_at' => self::REFERENCE_TIME,
        ];
        $rows['vendor_reports'][] = [
            'id' => $this->uuid('vendor-report', $projectKey),
            'user_id' => $bidders[0]['id'],
            'procurement_id' => $procurementId,
            'purchase_order_id' => $purchaseOrder->id,
            'reference_no' => 'KEN-VRPT-'.$referenceToken,
            'title' => $this->limit('Completion Report - '.$project->project_name),
            'report_type' => 'completion',
            'reporting_period_start' => '2026-07-01',
            'reporting_period_end' => '2026-08-15',
            'status' => 'accepted',
            'summary' => 'Synthetic completion report: contracted outputs were delivered, verified and accepted.',
            'challenges' => 'Demonstration-only scheduling and coordination risks were tracked and closed.',
            'next_steps' => 'Maintain the delivered outputs and monitor benefits through the Kenya project results framework.',
            'admin_feedback' => 'Synthetic report accepted against all three contract deliverables.',
            'reviewed_by' => $this->adminIds[0],
            'reviewed_at' => '2026-08-16 10:00:00',
            'created_at' => '2026-08-15 16:00:00',
            'updated_at' => self::REFERENCE_TIME,
        ];

        $winnerTechnical = 91.0;
        $winnerFinancial = 90.0;
        $runnerTechnical = 80.0;
        $runnerFinancial = 79.0;
        $auditEvents = [
            ['Published procurement', '2026-06-01 08:00:00', ['project_code' => $project->project_code]],
            ['Applications closed', '2026-06-15 17:00:00', ['application_count' => 3]],
            ['Prescreening completed', '2026-06-18 15:00:00', ['passed' => 2, 'failed' => 1]],
            ['Technical evaluation completed', '2026-06-22 16:00:00', ['winner_average' => $winnerTechnical, 'runner_average' => $runnerTechnical]],
            ['Financial evaluation completed', '2026-06-25 16:00:00', ['winner_average' => $winnerFinancial, 'runner_average' => $runnerFinancial]],
            ['Awarded procurement', '2026-06-28 14:00:00', [
                'winner_vendor_id' => $bidders[0]['id'],
                'winner_submission_id' => $submissionIds[0],
                'technical_weight' => 70,
                'financial_weight' => 30,
                'winner_weighted_score' => round(($winnerTechnical * .7) + ($winnerFinancial * .3), 2),
                'runner_weighted_score' => round(($runnerTechnical * .7) + ($runnerFinancial * .3), 2),
                'agreed_amount_kes' => $agreedAmount,
            ]],
        ];
        foreach ($auditEvents as $eventIndex => [$action, $createdAt, $metadata]) {
            $rows['procurement_audit_logs'][] = [
                'id' => $this->uuid('audit-log', $projectKey.'|'.$eventIndex),
                'user_id' => $this->adminIds[0],
                'action' => $action,
                'procurement_id' => $procurementId,
                'form_id' => $formId,
                'submission_id' => $eventIndex >= 2 ? $submissionIds[0] : null,
                'metadata' => json_encode(array_merge([
                    'synthetic' => true,
                    'dataset_tag' => KenyaProcurementEvaluationDataCleaner::DATASET_TAG,
                ], $metadata), JSON_THROW_ON_ERROR),
                'created_at' => $createdAt,
            ];
        }

        $rows['financial_links'][] = [
            'purchase_order_id' => $purchaseOrder->id,
            'invoice_id' => $purchaseOrder->invoice_id,
            'procurement_id' => $procurementId,
            'negotiation_id' => $negotiationId,
            'vendor_id' => $bidders[0]['id'],
            'governance_node_id' => $project->governance_node_id,
        ];
    }

    /**
     * @param  array<string, array<int, array<string, mixed>>>  $rows
     * @param  array<int, string>  $reviewerIds
     */
    private function appendEvaluationResults(
        array &$rows,
        string $projectKey,
        string $procurementId,
        string $submissionId,
        string $portfolioId,
        int $candidateIndex,
        array $reviewerIds,
    ): void {
        $factors = $candidateIndex === 0
            ? ['technical' => [0.92, 0.90], 'financial' => [0.91, 0.89]]
            : ['technical' => [0.81, 0.79], 'financial' => [0.80, 0.78]];

        foreach (['technical', 'financial'] as $phase) {
            $template = $this->portfolioStructures[$portfolioId]['evaluations'][$phase];
            foreach ($reviewerIds as $reviewerIndex => $reviewerId) {
                $factor = $factors[$phase][$reviewerIndex];
                $evaluationSubmissionId = $this->uuid(
                    'evaluation-submission',
                    $projectKey.'|'.$submissionId.'|'.$phase.'|'.$reviewerId
                );
                $rows['evaluation_assignments'][] = [
                    'id' => $this->uuid(
                        'evaluation-assignment',
                        $projectKey.'|'.$submissionId.'|'.$phase.'|'.$reviewerId
                    ),
                    'evaluation_id' => $template['id'],
                    'procurement_id' => $procurementId,
                    'form_submission_id' => $submissionId,
                    'user_id' => $reviewerId,
                    'assigned_by' => $this->adminIds[0],
                    'assigned_at' => $phase === 'technical' ? '2026-06-19 08:00:00' : '2026-06-23 08:00:00',
                    'status' => 'submitted',
                    'created_at' => $phase === 'technical' ? '2026-06-19 08:00:00' : '2026-06-23 08:00:00',
                    'updated_at' => self::REFERENCE_TIME,
                ];

                $overallScore = 0.0;
                foreach ($template['sections'] as $section) {
                    $sectionScore = 0.0;
                    foreach ($section['criteria'] as $criterion) {
                        $score = round($criterion['max_score'] * $factor, 2);
                        $sectionScore += $score;
                        $overallScore += $score;
                        $rows['evaluation_criteria_scores'][] = [
                            'id' => $this->uuid('criterion-score', $evaluationSubmissionId.'|'.$criterion['id']),
                            'submission_id' => $evaluationSubmissionId,
                            'evaluation_criteria_id' => $criterion['id'],
                            'score' => $score,
                            'decision' => null,
                            'comment' => 'Synthetic score supported by the seeded proposal response and evaluation profile.',
                            'created_at' => $phase === 'technical' ? '2026-06-21 12:00:00' : '2026-06-24 12:00:00',
                            'updated_at' => self::REFERENCE_TIME,
                        ];
                    }
                    $rows['evaluation_section_scores'][] = [
                        'id' => $this->uuid('section-score', $evaluationSubmissionId.'|'.$section['id']),
                        'submission_id' => $evaluationSubmissionId,
                        'evaluation_section_id' => $section['id'],
                        'section_score' => round($sectionScore, 2),
                        'strengths' => 'The synthetic proposal provides a coherent and verifiable response for this section.',
                        'weaknesses' => 'Minor assumptions require routine contract-stage monitoring.',
                        'created_at' => $phase === 'technical' ? '2026-06-21 12:00:00' : '2026-06-24 12:00:00',
                        'updated_at' => self::REFERENCE_TIME,
                    ];
                }

                $rows['evaluation_submissions'][] = [
                    'id' => $evaluationSubmissionId,
                    'evaluation_id' => $template['id'],
                    'procurement_id' => $procurementId,
                    'evaluator_id' => $reviewerId,
                    'form_submission_id' => $submissionId,
                    'overall_score' => round($overallScore, 2),
                    'comments' => 'Completed synthetic '.$phase.' evaluation. Scores are demonstration data only.',
                    'video_path' => null,
                    'video_duration' => null,
                    'submitted_at' => $phase === 'technical' ? '2026-06-22 15:00:00' : '2026-06-25 15:00:00',
                    'created_at' => $phase === 'technical' ? '2026-06-19 08:00:00' : '2026-06-23 08:00:00',
                    'updated_at' => self::REFERENCE_TIME,
                ];
            }
        }
    }

    /** @return array<string, string|float|int> */
    private function applicationValues(array $vendor, int $candidateIndex, float $bidAmount): array
    {
        return [
            'official_name' => $vendor['name'],
            'official_email' => $vendor['email'],
            'business_registration_number' => $vendor['registration'],
            'tax_compliance_reference' => $vendor['tax_reference'],
            'debarment_declaration' => 'Yes',
            'signed_bid_declaration' => 'Yes',
            'conflict_interest_declaration' => 'No conflict declared',
            'beneficial_ownership_disclosure' => $candidateIndex === 2 ? 'Incomplete' : 'Complete',
            'relevant_experience_summary' => 'Synthetic record of comparable Kenya programme delivery assignments.',
            'key_personnel_summary' => 'Synthetic multidisciplinary team with delivery, finance and quality roles.',
            'financial_capacity_declaration' => 'Synthetic declaration of sufficient liquidity and contract capacity.',
            'implementation_period_days' => 45 + ($candidateIndex * 5),
            'bid_amount_kes' => number_format($bidAmount, 2, '.', ''),
        ];
    }

    /**
     * @param  array<string, array<int, array<string, mixed>>>  $rows
     * @param  array<string, int>  $totals
     */
    private function persistLifecycleRows(array $rows, array &$totals): void
    {
        $tableOrder = [
            'procurements',
            'dynamic_forms',
            'dynamic_form_fields',
            'procurement_form_assignments',
            'form_submissions',
            'form_submission_values',
            'procurement_submission_screenings',
            'prescreening_template_procurements',
            'prescreening_assignments',
            'prescreening_evaluations',
            'prescreening_results',
            'procurement_evaluations',
            'evaluation_assignments',
            'evaluation_submissions',
            'evaluation_criteria_scores',
            'evaluation_section_scores',
            'procurement_contract_negotiations',
            'procurement_deliverables',
            'procurement_purchase_order_deliverables',
            'procurement_invoice_deliverables',
            'vendor_sub_activity_assignments',
            'vendor_reports',
            'procurement_audit_logs',
        ];

        foreach ($tableOrder as $table) {
            $tableRows = $rows[$table];
            $totals[$table] = ($totals[$table] ?? 0) + count($tableRows);
            if ($tableRows === []) {
                continue;
            }

            $uniqueBy = match ($table) {
                'procurement_purchase_order_deliverables' => ['purchase_order_id', 'deliverable_id'],
                'procurement_invoice_deliverables' => ['invoice_id', 'deliverable_id'],
                default => ['id'],
            };
            $this->upsertRows($table, $tableRows, $uniqueBy);
        }

        foreach ($rows['financial_links'] as $link) {
            DB::table('procurement_purchase_orders')
                ->where('id', $link['purchase_order_id'])
                ->update([
                    'procurement_id' => $link['procurement_id'],
                    'negotiation_id' => $link['negotiation_id'],
                    'vendor_id' => $link['vendor_id'],
                ]);
            DB::table('procurement_invoices')
                ->where('id', $link['invoice_id'])
                ->update([
                    'procurement_id' => $link['procurement_id'],
                    'vendor_id' => $link['vendor_id'],
                ]);
            DB::table('procurement_disbursements')
                ->where('purchase_order_id', $link['purchase_order_id'])
                ->update([
                    'procurement_id' => $link['procurement_id'],
                    'vendor_id' => $link['vendor_id'],
                ]);
        }
        $totals['financial_links'] = ($totals['financial_links'] ?? 0) + count($rows['financial_links']);
    }

    /**
     * @param  Collection<int, object>  $projects
     * @param  array<string, int>  $totals
     */
    private function validateSeededDataset(Collection $projects, array $totals): void
    {
        $projectCount = $projects->count();
        $portfolioCount = $projects->pluck('portfolio_id')->unique()->count();
        $errors = collect();

        $this->expect($errors, ($totals['procurements'] ?? 0) === $projectCount, 'one procurement per Kenya project');
        $this->expect($errors, ($totals['dynamic_forms'] ?? 0) === $projectCount, 'one approved application form per procurement');
        $this->expect($errors, ($totals['form_submissions'] ?? 0) === $projectCount * 3, 'three vendor applications per procurement');
        $this->expect($errors, ($totals['form_submission_values'] ?? 0) === $projectCount * 3 * count($this->catalogue['application_fields']), 'every application field populated');
        $this->expect($errors, ($totals['procurement_submission_screenings'] ?? 0) === $projectCount * 3, 'one transparent screening per application');
        $this->expect($errors, ($totals['prescreening_evaluations'] ?? 0) === $projectCount * 3 * count($this->prescreeningStructure['criteria']), 'every prescreening criterion evaluated');
        $this->expect($errors, ($totals['prescreening_results'] ?? 0) === $projectCount * 3, 'one prescreening result per application');
        $this->expect($errors, ($totals['evaluation_assignments'] ?? 0) === $projectCount * 8, 'two reviewers for both phases and both qualified bidders');
        $this->expect($errors, ($totals['evaluation_submissions'] ?? 0) === $projectCount * 8, 'every evaluation assignment submitted');
        $this->expect($errors, ($totals['evaluation_criteria_scores'] ?? 0) === $projectCount * 40, 'all technical and financial criteria scored');
        $this->expect($errors, ($totals['evaluation_section_scores'] ?? 0) === $projectCount * 16, 'all evaluation section summaries completed');
        $this->expect($errors, ($totals['procurement_contract_negotiations'] ?? 0) === $projectCount, 'one agreed negotiation per procurement');
        $this->expect($errors, ($totals['procurement_deliverables'] ?? 0) === $projectCount * 3, 'three completed contract deliverables per procurement');
        $this->expect($errors, ($totals['financial_links'] ?? 0) === $projectCount, 'one paid purchase order linked per procurement');
        $this->expect($errors, count($this->portfolioStructures) === $portfolioCount, 'portfolio-scoped resources and templates');

        $procurementScope = DB::table('procurements')
            ->where('reference_no', 'like', KenyaProcurementEvaluationDataCleaner::PROCUREMENT_PREFIX.'%');
        $this->expect($errors, (clone $procurementScope)->count() === $projectCount, 'controlled procurement count in database');
        $this->expect($errors, DB::table('users')->where('email', 'like', 'kenya.demo.vendor.%@example.invalid')->where('user_type', 'vendor')->count() === count($this->vendors), 'synthetic vendor accounts');

        $badPrescreening = DB::table('prescreening_results as result')
            ->join('form_submissions as submission', 'submission.id', '=', 'result.submission_id')
            ->join('procurements as procurement', 'procurement.id', '=', 'submission.procurement_id')
            ->where('procurement.reference_no', 'like', KenyaProcurementEvaluationDataCleaner::PROCUREMENT_PREFIX.'%')
            ->where(function ($query): void {
                $query->where('result.is_locked', '!=', true)
                    ->orWhereRaw('CAST(result.passed_criteria AS numeric) + CAST(result.failed_criteria AS numeric) <> result.total_criteria')
                    ->orWhereRaw("result.final_status <> CASE WHEN CAST(result.failed_criteria AS numeric) = 0 THEN 'passed' ELSE 'failed' END")
                    ->orWhereRaw("submission.status <> CASE WHEN CAST(result.failed_criteria AS numeric) = 0 THEN 'prescreen_passed' ELSE 'prescreen_failed' END");
            })
            ->count();
        $this->expect($errors, $badPrescreening === 0, 'prescreening totals, lock and submission statuses reconcile');

        $badProgression = DB::table('evaluation_submissions as evaluation_submission')
            ->join('form_submissions as application', 'application.id', '=', 'evaluation_submission.form_submission_id')
            ->join('procurements as procurement', 'procurement.id', '=', 'evaluation_submission.procurement_id')
            ->where('procurement.reference_no', 'like', KenyaProcurementEvaluationDataCleaner::PROCUREMENT_PREFIX.'%')
            ->where('application.status', '!=', 'prescreen_passed')
            ->count();
        $this->expect($errors, $badProgression === 0, 'only prescreen-passed applications advance');

        $templateTotals = DB::table('evaluations as evaluation')
            ->join('evaluation_sections as section', 'section.evaluation_id', '=', 'evaluation.id')
            ->join('evaluation_criteria as criterion', 'criterion.evaluation_section_id', '=', 'section.id')
            ->whereRaw("coalesce(evaluation.description, '') like ?", ['%'.KenyaProcurementEvaluationDataCleaner::DATASET_TAG.'%'])
            ->groupBy('evaluation.id')
            ->selectRaw('evaluation.id, SUM(criterion.max_score) AS total');
        $badTemplateTotals = DB::query()->fromSub($templateTotals, 'template_total')->whereRaw('ABS(total - 100) > 0.01')->count();
        $this->expect($errors, $badTemplateTotals === 0, 'every technical and financial template totals 100 points');

        $scoreTotals = DB::table('evaluation_submissions as evaluation_submission')
            ->join('procurements as procurement', 'procurement.id', '=', 'evaluation_submission.procurement_id')
            ->leftJoin('evaluation_criteria_scores as score', 'score.submission_id', '=', 'evaluation_submission.id')
            ->where('procurement.reference_no', 'like', KenyaProcurementEvaluationDataCleaner::PROCUREMENT_PREFIX.'%')
            ->groupBy('evaluation_submission.id', 'evaluation_submission.overall_score')
            ->selectRaw('evaluation_submission.id, evaluation_submission.overall_score, COALESCE(SUM(score.score), 0) AS score_total');
        $badScoreTotals = DB::query()->fromSub($scoreTotals, 'score_total')->whereRaw('ABS(overall_score - score_total) > 0.01')->count();
        $this->expect($errors, $badScoreTotals === 0, 'evaluation overall scores equal criterion totals');

        $badPortfolioScope = DB::table('evaluation_assignments as assignment')
            ->join('procurements as procurement', 'procurement.id', '=', 'assignment.procurement_id')
            ->join('evaluations as evaluation', 'evaluation.id', '=', 'assignment.evaluation_id')
            ->where('procurement.reference_no', 'like', KenyaProcurementEvaluationDataCleaner::PROCUREMENT_PREFIX.'%')
            ->whereColumn('procurement.portfolio_id', '!=', 'evaluation.portfolio_id')
            ->count();
        $this->expect($errors, $badPortfolioScope === 0, 'evaluation templates match explicit procurement portfolios');

        $badAwards = DB::table('procurements as procurement')
            ->leftJoin('procurement_contract_negotiations as negotiation', function ($join): void {
                $join->on('negotiation.procurement_id', '=', 'procurement.id')
                    ->on('negotiation.submission_id', '=', 'procurement.awarded_submission_id')
                    ->on('negotiation.vendor_id', '=', 'procurement.awarded_vendor_id')
                    ->where('negotiation.status', '=', 'agreed');
            })
            ->where('procurement.reference_no', 'like', KenyaProcurementEvaluationDataCleaner::PROCUREMENT_PREFIX.'%')
            ->where(function ($query): void {
                $query->where('procurement.status', '!=', 'awarded')
                    ->orWhereNull('procurement.awarded_at')
                    ->orWhereNull('negotiation.id');
            })
            ->count();
        $this->expect($errors, $badAwards === 0, 'awards reconcile with agreed negotiations and winning vendors');

        $linkedPurchaseOrders = DB::table('procurement_purchase_orders as purchase_order')
            ->join('procurements as procurement', 'procurement.id', '=', 'purchase_order.procurement_id')
            ->join('procurement_contract_negotiations as negotiation', 'negotiation.id', '=', 'purchase_order.negotiation_id')
            ->where('procurement.reference_no', 'like', KenyaProcurementEvaluationDataCleaner::PROCUREMENT_PREFIX.'%');
        $this->expect($errors, (clone $linkedPurchaseOrders)->count() === $projectCount, 'paid purchase orders linked to every award');
        $badFinancialLinks = (clone $linkedPurchaseOrders)
            ->where(function ($query): void {
                $query->whereColumn('purchase_order.vendor_id', '!=', 'procurement.awarded_vendor_id')
                    ->orWhereColumn('negotiation.vendor_id', '!=', 'procurement.awarded_vendor_id')
                    ->orWhere('purchase_order.currency', '!=', 'KES')
                    ->orWhereRaw('ABS(purchase_order.amount - negotiation.agreed_amount) > 0.01')
                    ->orWhereColumn('purchase_order.governance_node_id', '!=', 'procurement.governance_node_id');
            })
            ->count();
        $this->expect($errors, $badFinancialLinks === 0, 'award, PO, vendor, governance and KES amounts reconcile');

        if ($errors->isNotEmpty()) {
            throw new RuntimeException('Kenya procurement dataset validation failed: '.$errors->implode('; '));
        }
    }

    /** @return array<int, string> */
    private function reviewerPair(int $projectIndex): array
    {
        $count = count($this->adminIds);
        $first = ($projectIndex + 2) % $count;
        $second = ($first + 1) % $count;
        if ($second === $first) {
            $second = ($first + 1) % $count;
        }

        return [$this->adminIds[$first], $this->adminIds[$second]];
    }

    /** @param array<int, int> $weights @return array<int, float> */
    private function splitAmount(float $amount, array $weights): array
    {
        if (array_sum($weights) !== 100) {
            throw new RuntimeException('Deliverable weights must total 100 percent.');
        }

        $totalCents = (int) round($amount * 100);
        $remaining = $totalCents;
        $parts = [];
        foreach ($weights as $index => $weight) {
            $cents = $index === array_key_last($weights)
                ? $remaining
                : intdiv($totalCents * $weight, 100);
            $parts[] = $cents / 100;
            $remaining -= $cents;
        }

        return $parts;
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
        $hash = md5(self::UUID_NAMESPACE.'|'.$scope.'|'.$key);

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
     */
    private function upsertRows(string $table, array $rows, array $uniqueBy = ['id']): void
    {
        if ($rows === []) {
            return;
        }

        $updateColumns = array_values(array_diff(array_keys($rows[0]), $uniqueBy, ['id', 'created_at']));
        foreach (array_chunk($rows, 500) as $chunk) {
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

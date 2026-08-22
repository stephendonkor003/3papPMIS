<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes only the identifiable ATTP/Malawi demo dataset that was installed by
 * the historical default seed chain. Application accounts, roles, permissions,
 * and unrelated operational records are intentionally left in place.
 */
class LegacyAttpDemoDataCleaner
{
    public const KENYA_TAG = '[KENYA-GOV-DEMO]';

    private const LEGACY_MALAWI_NODE_CODES = [
        'GOM', 'OPC', 'MOAIWD', 'MOD', 'MOEST', 'MOFAIC', 'MOFEPD',
        'MOGCDSW', 'MOHS', 'MOHSEC', 'MOIBTT', 'MOICT', 'MOJCA',
        'MOLGRD', 'MOLHUD', 'MOLSI', 'MONREM', 'MOS', 'MOTPW', 'MOYSC',
        'ESCOM', 'MACRA', 'MPC', 'MRA', 'PPDA', 'RBM',
    ];

    private const LEGACY_MALAWI_SECTOR_IDS = [
        '019eab4e-24e6-71bf-a567-538377a6c9e8',
        '019eab4c-bb14-71e9-a213-ef1c1f04373e',
        '019eab4e-9add-7019-977c-cd3e9447faa6',
        '019ea974-49e9-7099-ac6e-83b4ff6caf99',
        '019eab4f-01d6-7280-8090-19ca6d536400',
        '019eab4f-a1ac-72be-84d2-3ae98a88fe7c',
        '019eab51-093d-70a2-b3f4-b81e6c57fc4c',
        '019eab52-1676-7223-9a28-fff59070ab7d',
        '019eab51-8d88-70e1-9ce5-a5933f9a28ff',
        '019eab4d-9e99-701f-8f99-845d281de9b3',
    ];

    private const LEGACY_COMMITTEE_IDS = [
        '285a283b-3170-490b-9a27-624d20360840',
        '9bb0c87a-5816-4abd-840a-7e1e47efb74c',
        'a644dfa4-019f-4eae-842a-37ac8a3d3cfb',
        'b7aa6937-bfa0-43a9-90b0-ee92bf716fe0',
    ];

    private const ATTP_DIMENSION_CODES = [
        'country',
        'priority_theme',
        'gender',
        'researcher_gender',
        'stakeholder_category',
        'citizen_participation',
        'institution_type',
        'government_body_type',
        'research_output_type',
        'fellowship_status',
        'support_type',
        'capacity_plan_status',
    ];

    /** @return array<string, int> */
    public function purge(): array
    {
        $before = $this->controlledCounts();
        $legacyNodeIds = $this->legacyGovernanceNodeIds();
        $scope = $this->programScope($legacyNodeIds);

        $this->purgeAttpOperationalTraces();
        $this->purgeAttpDataWarehouse();
        $this->purgeAttpQuestionnaires();
        $this->purgeLegacyMel($scope);
        $this->purgeThinkTankProcurements();
        $this->purgeLegacyImportedThinkTankData();
        $this->purgeAttpModuleTables();
        $this->purgeProgramScope($scope);
        $this->purgeAttpDiscussionContent();
        $this->purgeLegacyResources();
        $this->purgeLegacyGovernance($legacyNodeIds);

        $after = $this->controlledCounts();

        return collect($before)
            ->mapWithKeys(fn (int $count, string $key): array => [$key => max(0, $count - ($after[$key] ?? 0))])
            ->all();
    }

    /** @return array<string, int> */
    private function controlledCounts(): array
    {
        return [
            'programs' => Schema::hasTable('myb_programs')
                ? DB::table('myb_programs')->where($this->programSelector())->count()
                : 0,
            'governance_nodes' => Schema::hasTable('myb_governance_nodes')
                ? DB::table('myb_governance_nodes')->whereIn('code', self::LEGACY_MALAWI_NODE_CODES)->count()
                : 0,
            'procurements' => Schema::hasTable('procurements')
                ? DB::table('procurements')->where($this->thinkTankContentSelector('title', 'description'))->count()
                : 0,
            'think_tank_catalogue' => Schema::hasTable('think_datasets')
                ? DB::table('think_datasets')->count()
                : 0,
        ];
    }

    private function programSelector(): \Closure
    {
        return function (Builder $query): void {
            $query->where('program_id', 'PROG00001')
                ->orWhere('program_id', 'like', 'KEN-PROG-%')
                ->orWhereRaw("lower(coalesce(name, '')) like ?", ['%africa think tank%'])
                ->orWhereRaw("lower(coalesce(name, '')) like ?", ['%attp%'])
                ->orWhereRaw("coalesce(description, '') like ?", ['%'.self::KENYA_TAG.'%']);
        };
    }

    private function thinkTankContentSelector(string ...$columns): \Closure
    {
        return function (Builder $query) use ($columns): void {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                $query->{$method}(
                    "lower(coalesce({$column}, '')) similar to ?",
                    ['%(africa think tank|think tank|attp)%']
                );
            }
        };
    }

    /** @return Collection<int, string> */
    private function legacyGovernanceNodeIds(): Collection
    {
        if (! Schema::hasTable('myb_governance_nodes')) {
            return collect();
        }

        $nodeIds = DB::table('myb_governance_nodes')
            ->whereIn('code', self::LEGACY_MALAWI_NODE_CODES)
            ->orWhereRaw("lower(coalesce(name, '')) like ?", ['%malawi%'])
            ->pluck('id')
            ->unique()
            ->values();

        if (! Schema::hasTable('myb_governance_reporting_lines')) {
            return $nodeIds;
        }

        do {
            $before = $nodeIds->count();
            $children = DB::table('myb_governance_reporting_lines')
                ->whereIn('parent_node_id', $nodeIds->all())
                ->pluck('child_node_id');
            $nodeIds = $nodeIds->merge($children)->filter()->unique()->values();
        } while ($nodeIds->count() > $before);

        return $nodeIds;
    }

    /** @return array<string, Collection<int, string>> */
    private function programScope(Collection $legacyNodeIds): array
    {
        $programs = DB::table('myb_programs')->where($this->programSelector())->get(['id', 'sector_id']);
        $programIds = $programs->pluck('id')->unique()->values();
        $projectIds = DB::table('myb_projects')->whereIn('program_id', $programIds->all())->pluck('id');
        $activityIds = DB::table('myb_activities')->whereIn('project_id', $projectIds->all())->pluck('id');
        $subActivityIds = DB::table('myb_sub_activities')->whereIn('activity_id', $activityIds->all())->pluck('id');
        $fundingIds = DB::table('myb_program_fundings')
            ->whereIn('program_id', $programIds->all())
            ->orWhereRaw("lower(coalesce(program_name, '')) similar to ?", ['%(africa think tank|attp)%'])
            ->pluck('id')
            ->unique()
            ->values();

        $sectorIds = $programs->pluck('sector_id')->filter();
        if ($legacyNodeIds->isNotEmpty()) {
            $sectorIds = $sectorIds->merge(
                DB::table('myb_sectors')->whereIn('governance_node_id', $legacyNodeIds->all())->pluck('id')
            );
        }
        $sectorIds = $sectorIds->merge(
            DB::table('myb_sectors')
                ->whereRaw("coalesce(description, '') like ?", ['%'.self::KENYA_TAG.'%'])
                ->orWhereRaw("lower(coalesce(description, '')) like ?", ['%government of malawi%'])
                ->pluck('id')
        )->merge(
            DB::table('myb_sectors')->whereIn('id', self::LEGACY_MALAWI_SECTOR_IDS)->pluck('id')
        )->filter()->unique()->values();

        return compact(
            'programIds',
            'projectIds',
            'activityIds',
            'subActivityIds',
            'fundingIds',
            'sectorIds',
        );
    }

    /** @param array<string, Collection<int, string>> $scope */
    private function purgeProgramScope(array $scope): void
    {
        $programIds = $scope['programIds'];
        $projectIds = $scope['projectIds'];
        $activityIds = $scope['activityIds'];
        $subActivityIds = $scope['subActivityIds'];
        $fundingIds = $scope['fundingIds'];

        $purchaseRequestIds = DB::table('myb_purchase_requests')
            ->where(function (Builder $query) use ($fundingIds, $subActivityIds): void {
                $query->whereIn('program_funding_id', $fundingIds->all())
                    ->orWhereIn('allocation_id', $subActivityIds->all())
                    ->orWhere('reference_no', 'like', 'KEN-PR-%')
                    ->orWhereRaw("lower(coalesce(work_plan_source, '')) similar to ?", ['%(africa think tank|attp)%']);
            })
            ->pluck('id')
            ->unique()
            ->values();
        $purchaseRequestItemIds = DB::table('myb_purchase_request_items')
            ->whereIn('purchase_request_id', $purchaseRequestIds->all())
            ->pluck('id');
        $commitmentIds = DB::table('myb_budget_commitments')
            ->where(function (Builder $query) use ($fundingIds, $purchaseRequestIds, $subActivityIds): void {
                $query->whereIn('program_funding_id', $fundingIds->all())
                    ->orWhereIn('purchase_request_id', $purchaseRequestIds->all())
                    ->orWhereIn('allocation_id', $subActivityIds->all())
                    ->orWhereRaw("lower(coalesce(description, '')) similar to ?", ['%(africa think tank|attp)%']);
            })
            ->pluck('id')
            ->unique()
            ->values();
        $purchaseOrderIds = DB::table('procurement_purchase_orders')
            ->where(function (Builder $query) use ($purchaseRequestIds, $commitmentIds, $subActivityIds): void {
                $query->whereIn('purchase_request_id', $purchaseRequestIds->all())
                    ->orWhereIn('budget_commitment_id', $commitmentIds->all())
                    ->orWhereIn('sub_activity_id', $subActivityIds->all())
                    ->orWhere('reference_no', 'like', 'KEN-PO-%');
            })
            ->pluck('id')
            ->unique()
            ->values();
        $invoiceIds = DB::table('procurement_invoices')
            ->whereIn('sub_activity_id', $subActivityIds->all())
            ->orWhere('reference_no', 'like', 'KEN-INV-%')
            ->orWhereIn('id', DB::table('procurement_purchase_orders')->whereIn('id', $purchaseOrderIds->all())->pluck('invoice_id')->filter()->all())
            ->pluck('id')
            ->unique()
            ->values();

        DB::table('procurement_disbursements')
            ->whereIn('purchase_order_id', $purchaseOrderIds->all())
            ->orWhereIn('purchase_request_item_id', $purchaseRequestItemIds->all())
            ->orWhereIn('sub_activity_id', $subActivityIds->all())
            ->orWhere('reference_no', 'like', 'KEN-PAY-%')
            ->delete();

        $this->deleteWhereIn('vendor_purchase_requests', 'sub_activity_id', $subActivityIds);
        DB::table('vendor_sub_activity_assignments')
            ->whereIn('program_id', $programIds->all())
            ->orWhereIn('project_id', $projectIds->all())
            ->orWhereIn('activity_id', $activityIds->all())
            ->orWhereIn('sub_activity_id', $subActivityIds->all())
            ->delete();
        $this->deleteWhereIn('procurement_purchase_order_item_evidence', 'purchase_order_id', $purchaseOrderIds);
        $this->deleteWhereIn('procurement_purchase_order_item_evidence', 'invoice_id', $invoiceIds);
        $this->deleteWhereIn('procurement_purchase_order_deliverables', 'purchase_order_id', $purchaseOrderIds);
        $this->deleteWhereIn('procurement_invoice_deliverables', 'invoice_id', $invoiceIds);
        $this->deleteWhereIn('procurement_purchase_orders', 'id', $purchaseOrderIds);
        $this->deleteWhereIn('procurement_invoices', 'id', $invoiceIds);
        $this->deleteWhereIn('approved_work_plan_item_reviews', 'purchase_request_item_id', $purchaseRequestItemIds);
        DB::table('approved_work_plans')
            ->whereIn('budget_commitment_id', $commitmentIds->all())
            ->orWhereIn('program_funding_id', $fundingIds->all())
            ->orWhere('awp_code', 'like', 'KEN-AWP-%')
            ->orWhere('awp_code', 'like', 'AWP-2025-%')
            ->delete();
        $this->deleteWhereIn('myb_budget_commitments', 'id', $commitmentIds);
        $this->deleteWhereIn('myb_purchase_request_items', 'id', $purchaseRequestItemIds);
        $this->deleteWhereIn('myb_purchase_requests', 'id', $purchaseRequestIds);

        DB::table('myb_procurement_plans')
            ->whereIn('activity_id', $activityIds->all())
            ->orWhereIn('sub_activity_id', $subActivityIds->all())
            ->delete();
        DB::table('program_budget_allocations')
            ->whereIn('project_id', $projectIds->all())
            ->orWhereIn('activity_id', $activityIds->all())
            ->orWhereIn('sub_activity_id', $subActivityIds->all())
            ->delete();
        $this->deleteWhereIn('myb_sub_activity_allocations', 'sub_activity_id', $subActivityIds);
        $this->deleteWhereIn('myb_activity_allocations', 'activity_id', $activityIds);
        $this->deleteWhereIn('myb_project_allocations', 'project_id', $projectIds);
        $this->deleteWhereIn('myb_sub_activities', 'id', $subActivityIds);
        $this->deleteWhereIn('myb_activities', 'id', $activityIds);
        $this->deleteWhereIn('myb_projects', 'id', $projectIds);

        $this->deleteWhereIn('myb_program_funding_documents', 'program_funding_id', $fundingIds);
        foreach ([
            'myb_program_funding_member_states',
            'myb_program_funding_regional_blocks',
            'myb_program_funding_aspirations',
            'myb_program_funding_goals',
            'myb_program_funding_flagship_projects',
            'myb_partner_information_requests',
        ] as $table) {
            $this->deleteWhereIn($table, 'program_funding_id', $fundingIds);
        }
        $this->deleteWhereIn('myb_program_fundings', 'id', $fundingIds);
        $this->deleteWhereIn('myb_programs', 'id', $programIds);
        $this->deleteWhereIn('myb_sectors', 'id', $scope['sectorIds']);

        if (Schema::hasTable('myb_procurement_program_plans')) {
            DB::table('myb_procurement_program_plans')
                ->where($this->thinkTankContentSelector('name', 'description'))
                ->whereNotExists(fn (Builder $query) => $query
                    ->selectRaw('1')
                    ->from('myb_procurement_plans')
                    ->whereColumn('myb_procurement_plans.program_plan_id', 'myb_procurement_program_plans.id'))
                ->delete();
        }
    }

    /** @param array<string, Collection<int, string>> $scope */
    private function purgeLegacyMel(array $scope): void
    {
        if (Schema::hasTable('me_focal_unit_contacts')) {
            $memberIds = Schema::hasTable('attp_consortium_think_tanks')
                ? DB::table('attp_consortium_think_tanks')->pluck('id')
                : collect();
            DB::table('me_focal_unit_contacts')
                ->whereRaw("lower(coalesce(source, '')) like ?", ['%attp%'])
                ->when(
                    $memberIds->isNotEmpty(),
                    fn (Builder $query) => $query->orWhereIn('think_tank_member_id', $memberIds->all())
                )
                ->delete();
        }

        if (! Schema::hasTable('me_frameworks')) {
            return;
        }

        $frameworkIds = DB::table('me_frameworks')
            ->where('code', 'like', 'ATTP%')
            ->orWhere($this->thinkTankContentSelector('title', 'project_development_objective', 'notes'))
            ->pluck('id');
        $indicatorIds = DB::table('myb_indicators')
            ->where(function (Builder $query) use ($frameworkIds, $scope): void {
                $query->whereIn('framework_id', $frameworkIds->all())
                    ->orWhere('indicator_code', 'like', 'ATTP%')
                    ->orWhere(function (Builder $owner) use ($scope): void {
                        $owner->whereIn('indicatorable_id', $scope['programIds']->all())
                            ->orWhereIn('indicatorable_id', $scope['projectIds']->all());
                    });
            })
            ->pluck('id')
            ->unique()
            ->values();
        $formIdsFromIndicators = Schema::hasTable('me_data_entry_form_indicators')
            ? DB::table('me_data_entry_form_indicators')->whereIn('indicator_id', $indicatorIds->all())->pluck('form_id')
            : collect();
        $formIds = DB::table('me_data_entry_forms')
            ->where('code', 'like', 'ATTP%')
            ->orWhereIn('portfolio_id', $scope['sectorIds']->all())
            ->orWhereIn('project_component_id', $scope['projectIds']->all())
            ->orWhereIn('indicator_id', $indicatorIds->all())
            ->pluck('id')
            ->merge($formIdsFromIndicators)
            ->unique()
            ->values();
        $periodIds = DB::table('me_reporting_periods')
            ->where('code', 'like', 'ATTP%')
            ->orWhereIn('portfolio_id', $scope['sectorIds']->all())
            ->pluck('id')
            ->unique()
            ->values();
        $collectionIds = DB::table('me_data_collections')
            ->whereIn('form_id', $formIds->all())
            ->orWhereIn('reporting_period_id', $periodIds->all())
            ->pluck('id');
        $assignmentIds = DB::table('me_data_collection_assignments')
            ->whereIn('collection_id', $collectionIds->all())
            ->pluck('id');
        $reportIds = DB::table('me_performance_reports')
            ->whereIn('form_id', $formIds->all())
            ->orWhereIn('reporting_period_id', $periodIds->all())
            ->orWhereIn('assignment_id', $assignmentIds->all())
            ->orWhereIn('portfolio_id', $scope['sectorIds']->all())
            ->orWhereIn('project_component_id', $scope['projectIds']->all())
            ->pluck('id')
            ->merge(DB::table('me_performance_report_indicator_results')->whereIn('indicator_id', $indicatorIds->all())->pluck('report_id'))
            ->merge(DB::table('me_indicator_achievements')->whereIn('indicator_id', $indicatorIds->all())->pluck('report_id'))
            ->filter()
            ->unique()
            ->values();
        $achievementIds = DB::table('me_indicator_achievements')
            ->whereIn('report_id', $reportIds->all())
            ->orWhereIn('indicator_id', $indicatorIds->all())
            ->pluck('id');
        $folderIds = DB::table('me_repository_folders')
            ->whereIn('portfolio_id', $scope['sectorIds']->all())
            ->orWhereRaw("lower(coalesce(name, '')) similar to ?", ['%(africa think tank|think tank|attp)%'])
            ->pluck('id')
            ->unique()
            ->values();
        $repositoryItemIds = DB::table('me_knowledge_evidence_items')
            ->whereIn('portfolio_id', $scope['sectorIds']->all())
            ->orWhereIn('folder_id', $folderIds->all())
            ->pluck('id')
            ->unique()
            ->values();

        if ($achievementIds->isNotEmpty()) {
            DB::table('me_repository_document_links')
                ->whereIn('linkable_type', ['App\\Models\\MeIndicatorAchievement', 'MeIndicatorAchievement'])
                ->whereIn('linkable_id', $achievementIds->all())
                ->delete();
        }

        $this->deleteWhereIn('me_performance_reports', 'id', $reportIds);
        $this->deleteWhereIn('me_data_submissions', 'assignment_id', $assignmentIds);
        $this->deleteWhereIn('me_data_collection_assignments', 'id', $assignmentIds);
        $this->deleteWhereIn('me_data_collections', 'id', $collectionIds);
        if (Schema::hasTable('me_data_entry_form_indicators')) {
            DB::table('me_data_entry_form_indicators')
                ->whereIn('form_id', $formIds->all())
                ->orWhereIn('indicator_id', $indicatorIds->all())
                ->delete();
        }
        $this->deleteWhereIn('me_data_entry_forms', 'id', $formIds);
        $this->deleteWhereIn('me_reporting_periods', 'id', $periodIds);

        foreach (['me_indicator_reference_sheets', 'me_indicator_targets', 'me_indicator_calculation_rules'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            DB::table($table)
                ->whereIn('framework_id', $frameworkIds->all())
                ->orWhereIn('indicator_id', $indicatorIds->all())
                ->delete();
        }
        foreach (['me_indicator_disaggregation_requirements', 'me_repository_folder_indicators', 'me_performance_report_indicator_results', 'me_indicator_achievements'] as $table) {
            $this->deleteWhereIn($table, 'indicator_id', $indicatorIds);
        }
        $this->deleteWhereIn('myb_indicators', 'id', $indicatorIds);

        $this->deleteWhereIn('me_performance_thresholds', 'framework_id', $frameworkIds);

        $this->deleteWhereIn('me_data_governance_controls', 'portfolio_id', $scope['sectorIds']);
        if (Schema::hasTable('me_mission_reports')) {
            DB::table('me_mission_reports')
                ->whereIn('portfolio_id', $scope['sectorIds']->all())
                ->orWhereIn('project_component_id', $scope['projectIds']->all())
                ->delete();
        }
        if (Schema::hasTable('me_matrix_versions')) {
            DB::table('me_matrix_versions')
                ->whereIn('portfolio_id', $scope['sectorIds']->all())
                ->orWhereIn('repository_item_id', $repositoryItemIds->all())
                ->delete();
        }
        $this->deleteWhereIn('me_reporting_notification_logs', 'subject_id', $repositoryItemIds);
        $this->deleteWhereIn('me_knowledge_evidence_items', 'id', $repositoryItemIds);
        $this->deleteWhereIn('me_repository_folders', 'id', $folderIds);

        $definitionIds = DB::table('indicator_definitions')
            ->whereIn('portfolio_id', $scope['sectorIds']->all())
            ->pluck('id');
        $methodologyIds = DB::table('indicator_methodologies')
            ->whereIn('portfolio_id', $scope['sectorIds']->all())
            ->pluck('id')
            ->merge(DB::table('indicator_definitions')->whereIn('id', $definitionIds->all())->pluck('methodology_id'))
            ->filter()
            ->unique()
            ->values();
        $this->deleteWhereIn('indicator_definitions', 'id', $definitionIds);
        $this->deleteWhereIn('indicator_methodologies', 'id', $methodologyIds);

        $this->deleteUnusedPortfolioIndicatorMetadata('me_indicator_levels', 'indicator_level_id', $scope['sectorIds']);
        $this->deleteUnusedPortfolioIndicatorMetadata('me_indicator_units', 'unit_id', $scope['sectorIds']);
        $this->deleteUnusedPortfolioIndicatorMetadata('me_reporting_frequencies', 'frequency_of_reporting_id', $scope['sectorIds']);

        $portfolioEvaluationIds = DB::table('evaluations')
            ->whereIn('portfolio_id', $scope['sectorIds']->all())
            ->pluck('id');
        $this->purgeEvaluationIds($portfolioEvaluationIds);

        $this->deleteWhereIn('me_frameworks', 'id', $frameworkIds);

        $dimensionIds = DB::table('me_disaggregation_dimensions')
            ->whereIn('code', self::ATTP_DIMENSION_CODES)
            ->pluck('id');
        $protectedDimensionIds = DB::table('me_indicator_disaggregation_requirements')
            ->whereIn('dimension_id', $dimensionIds->all())
            ->pluck('dimension_id')
            ->unique();
        $deletableDimensionIds = $dimensionIds->diff($protectedDimensionIds)->values();
        $this->deleteWhereIn('me_disaggregation_options', 'dimension_id', $deletableDimensionIds);
        $this->deleteWhereIn('me_disaggregation_dimensions', 'id', $deletableDimensionIds);
    }

    private function deleteUnusedPortfolioIndicatorMetadata(
        string $table,
        string $indicatorColumn,
        Collection $portfolioIds
    ): void {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'portfolio_id')) {
            return;
        }

        $ids = DB::table($table)->whereIn('portfolio_id', $portfolioIds->all())->pluck('id');
        if ($ids->isEmpty()) {
            return;
        }

        DB::table($table)
            ->whereIn('id', $ids->all())
            ->whereNotExists(fn (Builder $query) => $query
                ->selectRaw('1')
                ->from('myb_indicators')
                ->whereColumn('myb_indicators.'.$indicatorColumn, $table.'.id'))
            ->delete();
    }

    private function purgeEvaluationIds(Collection $evaluationIds): void
    {
        $evaluationIds = $evaluationIds->filter()->unique()->values();
        if ($evaluationIds->isEmpty()) {
            return;
        }

        $submissionIds = DB::table('evaluation_submissions')
            ->whereIn('evaluation_id', $evaluationIds->all())
            ->pluck('id');
        $sectionIds = DB::table('evaluation_sections')
            ->whereIn('evaluation_id', $evaluationIds->all())
            ->pluck('id');
        $criterionIds = DB::table('evaluation_criteria')
            ->whereIn('evaluation_section_id', $sectionIds->all())
            ->pluck('id');

        DB::table('evaluation_criteria_scores')
            ->whereIn('submission_id', $submissionIds->all())
            ->orWhereIn('evaluation_criteria_id', $criterionIds->all())
            ->delete();
        DB::table('evaluation_section_scores')
            ->whereIn('submission_id', $submissionIds->all())
            ->orWhereIn('evaluation_section_id', $sectionIds->all())
            ->delete();
        $this->deleteWhereIn('evaluation_results', 'submission_id', $submissionIds);
        $this->deleteWhereIn('rework_requests', 'evaluation_id', $evaluationIds);
        $this->deleteWhereIn('evaluation_assignments', 'evaluation_id', $evaluationIds);
        $this->deleteWhereIn('procurement_evaluations', 'evaluation_id', $evaluationIds);
        $this->deleteWhereIn('evaluation_submissions', 'id', $submissionIds);
        $this->deleteWhereIn('evaluation_criteria', 'id', $criterionIds);
        $this->deleteWhereIn('evaluation_sections', 'id', $sectionIds);
        $this->deleteWhereIn('evaluations', 'id', $evaluationIds);
    }

    private function purgeLegacyImportedThinkTankData(): void
    {
        if (! Schema::hasTable('applicants')) {
            return;
        }

        $applicantIds = DB::table('applicants')
            ->where('code', 'like', 'AUC-TK-2-%')
            ->orWhere('legacy_source_table', 'applicants')
            ->pluck('id')
            ->unique()
            ->values();
        $evaluationIds = DB::table('evaluations')
            ->whereIn('applicant_id', $applicantIds->all())
            ->orWhere('type', 'legacy_application_evaluation')
            ->orWhere($this->thinkTankContentSelector('name', 'description'))
            ->pluck('id')
            ->unique()
            ->values();
        $this->purgeEvaluationIds($evaluationIds);

        $teamIds = DB::table('team_consortiums')
            ->whereIn('consortium_id', $applicantIds->all())
            ->pluck('team_id')
            ->merge(DB::table('site_visit_evaluations')->whereIn('consortium_id', $applicantIds->all())->pluck('team_id'))
            ->filter()
            ->unique()
            ->values();

        $this->deleteWhereIn('financial_evaluations', 'applicant_id', $applicantIds);
        $this->deleteWhereIn('prescreening_criteria', 'applicant_id', $applicantIds);
        $this->deleteWhereIn('assignments', 'applicant_id', $applicantIds);
        $this->deleteWhereIn('site_visit_evaluations', 'consortium_id', $applicantIds);
        $this->deleteWhereIn('team_consortiums', 'consortium_id', $applicantIds);
        $this->deleteWhereIn('team_members', 'team_id', $teamIds);
        $this->deleteWhereIn('evaluator_teams', 'id', $teamIds);
        $this->deleteWhereIn('applicant_user', 'applicant_id', $applicantIds);
        $this->deleteWhereIn('applicants', 'id', $applicantIds);
        $this->deleteWhereIn('committee_members', 'committee_id', collect(self::LEGACY_COMMITTEE_IDS));
        $this->deleteWhereIn('committees', 'id', collect(self::LEGACY_COMMITTEE_IDS));
        DB::table('categories')
            ->where('id', '1d5fdbfe-1586-491b-bb8b-b7c745241fb2')
            ->where('name', 'Establish Secretariat')
            ->whereRaw("lower(coalesce(description, '')) like ?", ['%manage the platform%'])
            ->delete();
        DB::table('rework_requests')
            ->whereNotNull('evaluation_id')
            ->whereNotExists(fn (Builder $query) => $query
                ->selectRaw('1')
                ->from('evaluations')
                ->whereColumn('evaluations.id', 'rework_requests.evaluation_id'))
            ->delete();

        if (Schema::hasTable('think_datasets')) {
            DB::table('think_datasets')->delete();
        }
    }

    private function purgeThinkTankProcurements(): void
    {
        if (! Schema::hasTable('procurements')) {
            return;
        }

        $procurementIds = DB::table('procurements')
            ->where('id', '3ed5b12a-8895-4cd3-a2fa-4e74bb147141')
            ->orWhere($this->thinkTankContentSelector('title', 'description'))
            ->pluck('id')
            ->unique()
            ->values();
        if ($procurementIds->isEmpty()) {
            return;
        }

        $formSubmissionIds = DB::table('form_submissions')->whereIn('procurement_id', $procurementIds->all())->pluck('id');
        $evaluationSubmissionIds = DB::table('evaluation_submissions')->whereIn('procurement_id', $procurementIds->all())->pluck('id');
        $evaluationIds = DB::table('evaluations')
            ->whereIn('procurement_id', $procurementIds->all())
            ->pluck('id')
            ->merge($evaluationSubmissionIds->isEmpty()
                ? collect()
                : DB::table('evaluation_submissions')->whereIn('id', $evaluationSubmissionIds->all())->pluck('evaluation_id'))
            ->merge(DB::table('evaluation_assignments')->whereIn('procurement_id', $procurementIds->all())->pluck('evaluation_id'))
            ->merge(DB::table('procurement_evaluations')->whereIn('procurement_id', $procurementIds->all())->pluck('evaluation_id'))
            ->merge(DB::table('evaluations')
                ->where('type', 'legacy_application_evaluation')
                ->orWhere($this->thinkTankContentSelector('name', 'description'))
                ->pluck('id'))
            ->filter()
            ->unique()
            ->values();
        $evaluationSectionIds = DB::table('evaluation_sections')->whereIn('evaluation_id', $evaluationIds->all())->pluck('id');
        $siteVisitIds = DB::table('site_visits')->whereIn('procurement_id', $procurementIds->all())->pluck('id');
        $siteVisitGroupIds = DB::table('site_visit_groups')->whereIn('site_visit_id', $siteVisitIds->all())->pluck('id');
        $formIds = DB::table('dynamic_forms')->whereIn('procurement_id', $procurementIds->all())->pluck('id');
        $templateIds = DB::table('prescreening_template_procurements')
            ->whereIn('procurement_id', $procurementIds->all())
            ->pluck('prescreening_template_id');
        $controlledTemplateIds = DB::table('prescreening_templates')
            ->whereIn('id', $templateIds->all())
            ->where($this->thinkTankContentSelector('name', 'description'))
            ->pluck('id');
        $prescreeningSectionIds = DB::table('prescreening_sections')
            ->whereIn('prescreening_template_id', $controlledTemplateIds->all())
            ->pluck('id');
        $negotiationIds = DB::table('procurement_contract_negotiations')
            ->whereIn('procurement_id', $procurementIds->all())
            ->pluck('id');

        foreach (['evaluation_criteria_scores', 'evaluation_section_scores', 'evaluation_results'] as $table) {
            $this->deleteWhereIn($table, 'submission_id', $evaluationSubmissionIds);
        }
        $this->deleteWhereIn('evaluation_submissions', 'id', $evaluationSubmissionIds);
        $this->deleteWhereIn('rework_requests', 'evaluation_id', $evaluationIds);
        $this->deleteWhereIn('evaluation_assignments', 'procurement_id', $procurementIds);
        $this->deleteWhereIn('evaluation_assignments', 'evaluation_id', $evaluationIds);
        $this->deleteWhereIn('evaluation_criteria', 'evaluation_section_id', $evaluationSectionIds);
        $this->deleteWhereIn('evaluation_sections', 'id', $evaluationSectionIds);
        $this->deleteWhereIn('procurement_evaluations', 'procurement_id', $procurementIds);
        $this->deleteWhereIn('evaluations', 'id', $evaluationIds);

        foreach (['site_visit_approvals', 'site_visit_assignments', 'site_visit_medias', 'site_visit_observations'] as $table) {
            $this->deleteWhereIn($table, 'site_visit_id', $siteVisitIds);
        }
        $this->deleteWhereIn('site_visit_group_members', 'group_id', $siteVisitGroupIds);
        $this->deleteWhereIn('site_visit_groups', 'id', $siteVisitGroupIds);
        $this->deleteWhereIn('site_visits', 'id', $siteVisitIds);

        foreach (['prescreening_evaluations', 'prescreening_results'] as $table) {
            $this->deleteWhereIn($table, 'submission_id', $formSubmissionIds);
            $this->deleteWhereIn($table, 'prescreening_template_id', $controlledTemplateIds);
        }
        $this->deleteWhereIn('prescreening_criteria', 'prescreening_template_id', $controlledTemplateIds);
        $this->deleteWhereIn('prescreening_criteria', 'prescreening_section_id', $prescreeningSectionIds);
        $this->deleteWhereIn('prescreening_sections', 'id', $prescreeningSectionIds);
        $this->deleteWhereIn('form_submission_values', 'submission_id', $formSubmissionIds);
        $this->deleteWhereIn('procurement_submission_screenings', 'submission_id', $formSubmissionIds);
        $this->deleteWhereIn('form_submissions', 'id', $formSubmissionIds);
        $this->deleteWhereIn('dynamic_form_fields', 'form_id', $formIds);
        $this->deleteWhereIn('dynamic_forms', 'id', $formIds);

        $poIds = DB::table('procurement_purchase_orders')->whereIn('procurement_id', $procurementIds->all())->pluck('id');
        $invoiceIds = DB::table('procurement_invoices')->whereIn('procurement_id', $procurementIds->all())->pluck('id');
        DB::table('procurement_disbursements')
            ->whereIn('procurement_id', $procurementIds->all())
            ->orWhereIn('purchase_order_id', $poIds->all())
            ->delete();
        DB::table('vendor_purchase_requests')
            ->whereIn('procurement_id', $procurementIds->all())
            ->orWhereIn('purchase_order_id', $poIds->all())
            ->delete();
        DB::table('vendor_reports')
            ->whereIn('procurement_id', $procurementIds->all())
            ->orWhereIn('purchase_order_id', $poIds->all())
            ->delete();
        $this->deleteWhereIn('procurement_purchase_order_item_evidence', 'purchase_order_id', $poIds);
        $this->deleteWhereIn('procurement_purchase_order_item_evidence', 'invoice_id', $invoiceIds);
        $this->deleteWhereIn('procurement_purchase_order_deliverables', 'purchase_order_id', $poIds);
        $this->deleteWhereIn('procurement_invoice_deliverables', 'invoice_id', $invoiceIds);
        $this->deleteWhereIn('procurement_purchase_orders', 'id', $poIds);
        $this->deleteWhereIn('procurement_invoices', 'id', $invoiceIds);
        $this->deleteWhereIn('procurement_contract_documents', 'negotiation_id', $negotiationIds);

        foreach ([
            'prescreening_assignments', 'prescreening_template_procurements', 'procurement_audit_logs',
            'procurement_contract_negotiations', 'procurement_deliverables', 'procurement_documents',
            'procurement_evaluations', 'procurement_form_assignments', 'procurement_form_maps',
            'procurement_user_permissions', 'vendor_information_requests', 'vendor_messages',
        ] as $table) {
            $this->deleteWhereIn($table, 'procurement_id', $procurementIds);
        }
        $this->deleteWhereIn('procurements', 'id', $procurementIds);

        $this->deleteWhereIn('prescreening_templates', 'id', $controlledTemplateIds);
    }

    private function purgeAttpModuleTables(): void
    {
        $tables = [
            'attp_think_tank_procurement_import_rows',
            'attp_think_tank_procurement_import_batches',
            'attp_think_tank_procurement_documents',
            'attp_think_tank_procurement_events',
            'attp_think_tank_procurement_reviews',
            'attp_think_tank_procurement_items',
            'attp_think_tank_procurement_plans',
            'attp_think_tank_research_outputs',
            'attp_report_evidence',
            'attp_expense_reports',
            'attp_risk_flags',
            'attp_activity_reports',
            'attp_disbursement_requests',
            'attp_fund_allocations',
            'attp_workplans',
            'attp_consortium_think_tanks',
            'attp_consortia',
            'attp_news_attachments',
            'attp_news_posts',
            'attp_news_subscribers',
            'attp_ai_guide_settings',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }

    private function purgeAttpDiscussionContent(): void
    {
        if (! Schema::hasTable('discussion_topics')) {
            return;
        }

        $topicIds = DB::table('discussion_topics')
            ->where($this->thinkTankContentSelector('title', 'summary', 'body'))
            ->orWhere('slug', 'like', '%attp%')
            ->pluck('id');
        $postIds = DB::table('discussion_posts')->whereIn('topic_id', $topicIds->all())->pluck('id');
        $participantIds = DB::table('discussion_participants')
            ->whereRaw("lower(coalesce(email, '')) like ?", ['%@discussion.attp.invalid'])
            ->orWhere($this->thinkTankContentSelector('display_name', 'organization', 'bio'))
            ->pluck('id');
        $themeIds = DB::table('discussion_themes')
            ->where($this->thinkTankContentSelector('name', 'description'))
            ->pluck('id');

        if (Schema::hasTable('discussion_moderation_actions')) {
            DB::table('discussion_moderation_actions')
                ->where(function (Builder $query) use ($topicIds): void {
                    $query->where('subject_type', 'topic')->whereIn('subject_id', $topicIds->all());
                })
                ->orWhere(function (Builder $query) use ($postIds): void {
                    $query->where('subject_type', 'post')->whereIn('subject_id', $postIds->all());
                })
                ->delete();
        }
        $this->deleteWhereIn('discussion_topics', 'id', $topicIds);
        $this->deleteWhereIn('discussion_participants', 'id', $participantIds);
        if ($themeIds->isNotEmpty()) {
            DB::table('discussion_themes')
                ->whereIn('id', $themeIds->all())
                ->whereNotExists(fn (Builder $query) => $query
                    ->selectRaw('1')
                    ->from('discussion_topics')
                    ->whereColumn('discussion_topics.theme_id', 'discussion_themes.id'))
                ->delete();
        }
    }

    private function purgeAttpQuestionnaires(): void
    {
        if (! Schema::hasTable('biannual_site_visit_templates')) {
            return;
        }

        $templateIds = DB::table('biannual_site_visit_templates')
            ->where('code', 'like', 'ATTP%')
            ->orWhere($this->thinkTankContentSelector('name', 'description'))
            ->pluck('id');
        $this->deleteWhereIn('biannual_site_visit_profiles', 'template_id', $templateIds);
        $this->deleteWhereIn('biannual_site_visit_templates', 'id', $templateIds);
    }

    private function purgeAttpDataWarehouse(): void
    {
        if (! Schema::hasTable('data_warehouse_records')) {
            return;
        }

        $recordIds = DB::table('data_warehouse_records')
            ->where($this->thinkTankContentSelector('title', 'description', 'source_name', 'data_owner'))
            ->pluck('id');
        $categoryIds = DB::table('data_warehouse_categories')
            ->where($this->thinkTankContentSelector('name', 'description'))
            ->pluck('id');

        $this->deleteWhereIn('data_warehouse_records', 'id', $recordIds);
        if ($categoryIds->isNotEmpty()) {
            DB::table('data_warehouse_categories')
                ->whereIn('id', $categoryIds->all())
                ->whereNotExists(fn (Builder $query) => $query
                    ->selectRaw('1')
                    ->from('data_warehouse_records')
                    ->whereColumn('data_warehouse_records.category_id', 'data_warehouse_categories.id'))
                ->delete();
        }
    }

    private function purgeAttpOperationalTraces(): void
    {
        if (Schema::hasTable('notifications')) {
            DB::table('notifications')
                ->where($this->thinkTankContentSelector('data'))
                ->delete();
        }

        if (Schema::hasTable('system_audit_logs')) {
            DB::table('system_audit_logs')
                ->where($this->thinkTankContentSelector(
                    'action',
                    'action_message',
                    'description',
                    'module',
                    'url',
                    'route_name'
                ))
                ->orWhereRaw("lower(coalesce(cast(payload as text), '')) similar to ?", ['%(africa think tank|think tank|attp)%'])
                ->delete();
        }

        if (Schema::hasTable('website_visit_activities')) {
            DB::table('website_visit_activities')
                ->where($this->thinkTankContentSelector('title', 'url', 'path', 'referrer'))
                ->orWhereRaw("lower(coalesce(cast(metadata as text), '')) similar to ?", ['%(africa think tank|think tank|attp)%'])
                ->delete();
        }

        if (Schema::hasTable('website_visits')) {
            foreach (['referrer', 'landing_url', 'current_url', 'current_path'] as $column) {
                DB::table('website_visits')
                    ->whereRaw("lower(coalesce({$column}, '')) similar to ?", ['%(africa think tank|think tank|attp)%'])
                    ->update([$column => null]);
            }
        }
    }

    private function purgeLegacyResources(): void
    {
        if (! Schema::hasTable('myb_resources')) {
            return;
        }

        $resourceIds = DB::table('myb_resources')
            ->where($this->thinkTankContentSelector('name', 'description'))
            ->orWhere('reference_code', 'like', 'AWP-2025-%')
            ->pluck('id');
        if ($resourceIds->isNotEmpty()) {
            DB::table('myb_resources')
                ->whereIn('id', $resourceIds->all())
                ->whereNotExists(fn (Builder $query) => $query
                    ->selectRaw('1')
                    ->from('myb_purchase_request_items')
                    ->whereColumn('myb_purchase_request_items.resource_id', 'myb_resources.id'))
                ->whereNotExists(fn (Builder $query) => $query
                    ->selectRaw('1')
                    ->from('myb_budget_commitments')
                    ->whereColumn('myb_budget_commitments.resource_id', 'myb_resources.id'))
                ->whereNotExists(fn (Builder $query) => $query
                    ->selectRaw('1')
                    ->from('procurements')
                    ->whereColumn('procurements.resource_id', 'myb_resources.id'))
                ->delete();
        }

        DB::table('myb_resource_categories')
            ->where($this->thinkTankContentSelector('name', 'description'))
            ->whereNotExists(fn (Builder $query) => $query
                ->selectRaw('1')
                ->from('myb_resources')
                ->whereColumn('myb_resources.resource_category_id', 'myb_resource_categories.id'))
            ->delete();
    }

    private function purgeLegacyGovernance(Collection $legacyNodeIds): void
    {
        if ($legacyNodeIds->isEmpty()) {
            return;
        }

        $this->deleteWhereIn('myb_governance_node_assignments', 'node_id', $legacyNodeIds);
        if (Schema::hasTable('myb_governance_reporting_lines')) {
            DB::table('myb_governance_reporting_lines')
                ->whereIn('child_node_id', $legacyNodeIds->all())
                ->orWhereIn('parent_node_id', $legacyNodeIds->all())
                ->delete();
        }
        $this->deleteWhereIn('myb_governance_nodes', 'id', $legacyNodeIds);
    }

    private function deleteWhereIn(string $table, string $column, Collection $ids): void
    {
        if ($ids->isEmpty() || ! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::table($table)->whereIn($column, $ids->all())->delete();
    }
}

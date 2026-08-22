<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes only records owned by KenyaProcurementEvaluationSeeder.
 *
 * Existing Kenya planning/finance rows are preserved. Purchase orders,
 * invoices and disbursements enriched by the seeder are unlinked before the
 * generated procurement records are removed.
 */
class KenyaProcurementEvaluationDataCleaner
{
    public const DATASET_TAG = '[KENYA-PROCUREMENT-EVALUATION-DEMO]';

    public const PROCUREMENT_PREFIX = 'KEN-PROC-';

    public const RESOURCE_PREFIX = 'KEN-PROC-RES-';

    /** @return array<string, int> */
    public function purge(): array
    {
        if (! Schema::hasTable('procurements')) {
            return [];
        }

        $procurementIds = DB::table('procurements')
            ->where('reference_no', 'like', self::PROCUREMENT_PREFIX.'%')
            ->orWhereRaw("coalesce(description, '') like ?", ['%'.self::DATASET_TAG.'%'])
            ->pluck('id')
            ->unique()
            ->values();

        $counts = [
            'procurements' => $procurementIds->count(),
            'applications' => 0,
            'evaluation_submissions' => 0,
        ];

        if ($procurementIds->isNotEmpty()) {
            $counts = array_merge($counts, $this->purgeProcurements($procurementIds));
        }

        $this->purgeDetachedTemplatesAndResources();

        return $counts;
    }

    /**
     * @param  Collection<int, string>  $procurementIds
     * @return array<string, int>
     */
    private function purgeProcurements(Collection $procurementIds): array
    {
        $formIds = $this->pluckWhereIn('dynamic_forms', 'procurement_id', $procurementIds, 'id');
        $submissionIds = $this->pluckWhereIn('form_submissions', 'procurement_id', $procurementIds, 'id');
        $evaluationIds = $this->pluckWhereIn('evaluations', 'procurement_id', $procurementIds, 'id')
            ->merge($this->pluckWhereIn('evaluation_assignments', 'procurement_id', $procurementIds, 'evaluation_id'))
            ->merge($this->pluckWhereIn('procurement_evaluations', 'procurement_id', $procurementIds, 'evaluation_id'))
            ->filter()
            ->unique()
            ->values();
        $evaluationSubmissionIds = $this->pluckWhereIn(
            'evaluation_submissions',
            'procurement_id',
            $procurementIds,
            'id'
        );
        $evaluationSectionIds = $this->pluckWhereIn(
            'evaluation_sections',
            'evaluation_id',
            $evaluationIds,
            'id'
        );
        $evaluationCriteriaIds = $this->pluckWhereIn(
            'evaluation_criteria',
            'evaluation_section_id',
            $evaluationSectionIds,
            'id'
        );
        $templateIds = $this->pluckWhereIn(
            'prescreening_template_procurements',
            'procurement_id',
            $procurementIds,
            'prescreening_template_id'
        );
        $negotiationIds = $this->pluckWhereIn(
            'procurement_contract_negotiations',
            'procurement_id',
            $procurementIds,
            'id'
        );
        $deliverableIds = $this->pluckWhereIn(
            'procurement_deliverables',
            'procurement_id',
            $procurementIds,
            'id'
        );
        $purchaseOrderIds = $this->pluckWhereIn(
            'procurement_purchase_orders',
            'procurement_id',
            $procurementIds,
            'id'
        );
        $invoiceIds = $this->pluckWhereIn(
            'procurement_invoices',
            'procurement_id',
            $procurementIds,
            'id'
        )->merge($this->pluckWhereIn(
            'procurement_purchase_orders',
            'procurement_id',
            $procurementIds,
            'invoice_id'
        ))->filter()->unique()->values();

        $vendorRequestIds = $this->pluckWhereIn(
            'vendor_purchase_requests',
            'procurement_id',
            $procurementIds,
            'id'
        );
        $vendorReportIds = $this->pluckWhereIn(
            'vendor_reports',
            'procurement_id',
            $procurementIds,
            'id'
        );

        $this->deleteWhereIn('vendor_documents', 'source_id', $vendorRequestIds->merge($vendorReportIds)->unique());
        $this->deleteWhereIn('vendor_purchase_request_items', 'vendor_purchase_request_id', $vendorRequestIds);
        $this->deleteWhereIn('vendor_purchase_requests', 'id', $vendorRequestIds);
        foreach (['vendor_reports', 'vendor_messages', 'vendor_information_requests'] as $table) {
            $this->deleteWhereIn($table, 'procurement_id', $procurementIds);
        }

        $subActivityIds = $this->pluckWhereIn(
            'procurement_purchase_orders',
            'procurement_id',
            $procurementIds,
            'sub_activity_id'
        );
        $vendorIds = $this->pluckWhereIn(
            'procurements',
            'id',
            $procurementIds,
            'awarded_vendor_id'
        );
        if ($subActivityIds->isNotEmpty() && $vendorIds->isNotEmpty() && Schema::hasTable('vendor_sub_activity_assignments')) {
            DB::table('vendor_sub_activity_assignments')
                ->whereIn('sub_activity_id', $subActivityIds->all())
                ->whereIn('vendor_id', $vendorIds->all())
                ->delete();
        }

        $this->deleteWhereIn('evaluation_criteria_scores', 'submission_id', $evaluationSubmissionIds);
        $this->deleteWhereIn('evaluation_section_scores', 'submission_id', $evaluationSubmissionIds);
        $this->deleteWhereIn('evaluation_results', 'submission_id', $submissionIds);
        $this->deleteWhereIn('evaluation_submissions', 'id', $evaluationSubmissionIds);
        $this->deleteWhereIn('evaluation_assignments', 'procurement_id', $procurementIds);
        $this->deleteWhereIn('procurement_evaluations', 'procurement_id', $procurementIds);
        $this->deleteWhereIn('rework_requests', 'evaluation_id', $evaluationIds);
        $this->deleteWhereIn('evaluation_criteria', 'id', $evaluationCriteriaIds);
        $this->deleteWhereIn('evaluation_sections', 'id', $evaluationSectionIds);
        $this->deleteWhereIn('evaluations', 'id', $evaluationIds);

        $this->deleteWhereIn('prescreening_evaluations', 'submission_id', $submissionIds);
        $this->deleteWhereIn('prescreening_results', 'submission_id', $submissionIds);
        $this->deleteWhereIn('prescreening_assignments', 'procurement_id', $procurementIds);
        $this->deleteWhereIn('prescreening_template_procurements', 'procurement_id', $procurementIds);

        $this->deleteWhereIn('procurement_submission_screenings', 'submission_id', $submissionIds);
        $this->deleteWhereIn('form_submission_values', 'submission_id', $submissionIds);
        $this->deleteWhereIn('form_submissions', 'id', $submissionIds);
        $this->deleteWhereIn('dynamic_form_fields', 'form_id', $formIds);
        foreach (['procurement_form_assignments', 'procurement_form_maps'] as $table) {
            $this->deleteWhereIn($table, 'procurement_id', $procurementIds);
        }
        $this->deleteWhereIn('dynamic_forms', 'id', $formIds);

        $this->deleteWhereIn('procurement_contract_documents', 'negotiation_id', $negotiationIds);
        $this->deleteWhereIn('procurement_purchase_order_deliverables', 'deliverable_id', $deliverableIds);
        $this->deleteWhereIn('procurement_invoice_deliverables', 'deliverable_id', $deliverableIds);

        if (Schema::hasTable('procurement_disbursements')) {
            DB::table('procurement_disbursements')
                ->whereIn('procurement_id', $procurementIds->all())
                ->update([
                    'procurement_id' => null,
                    'vendor_id' => null,
                    'deliverable_id' => null,
                ]);
        }
        if (Schema::hasTable('procurement_invoices')) {
            DB::table('procurement_invoices')
                ->whereIn('id', $invoiceIds->all())
                ->update(['procurement_id' => null, 'vendor_id' => null]);
        }
        if (Schema::hasTable('procurement_purchase_orders')) {
            DB::table('procurement_purchase_orders')
                ->whereIn('id', $purchaseOrderIds->all())
                ->update([
                    'procurement_id' => null,
                    'negotiation_id' => null,
                    'vendor_id' => null,
                ]);
        }

        $this->deleteWhereIn('procurement_deliverables', 'id', $deliverableIds);
        $this->deleteWhereIn('procurement_contract_negotiations', 'id', $negotiationIds);
        foreach ([
            'procurement_audit_logs',
            'procurement_documents',
            'procurement_user_permissions',
            'procurement_step_approvals',
            'procurement_step_stages',
        ] as $table) {
            $this->deleteWhereIn($table, 'procurement_id', $procurementIds);
        }

        DB::table('procurements')
            ->whereIn('id', $procurementIds->all())
            ->update([
                'awarded_submission_id' => null,
                'awarded_vendor_id' => null,
                'awarded_at' => null,
            ]);
        $this->deleteWhereIn('procurements', 'id', $procurementIds);

        // The template is shared by this generated scope and is removed only
        // after all procurement links and results have gone.
        $this->deleteWhereIn('prescreening_criteria', 'prescreening_template_id', $templateIds);
        $this->deleteWhereIn('prescreening_sections', 'prescreening_template_id', $templateIds);
        $this->deleteWhereIn('prescreening_templates', 'id', $templateIds);

        return [
            'applications' => $submissionIds->count(),
            'evaluation_submissions' => $evaluationSubmissionIds->count(),
        ];
    }

    private function purgeDetachedTemplatesAndResources(): void
    {
        if (Schema::hasTable('evaluations')) {
            $evaluationIds = DB::table('evaluations')
                ->whereRaw("coalesce(description, '') like ?", ['%'.self::DATASET_TAG.'%'])
                ->pluck('id');
            $sectionIds = $this->pluckWhereIn('evaluation_sections', 'evaluation_id', $evaluationIds, 'id');
            $criteriaIds = $this->pluckWhereIn('evaluation_criteria', 'evaluation_section_id', $sectionIds, 'id');
            $evaluationSubmissionIds = $this->pluckWhereIn(
                'evaluation_submissions',
                'evaluation_id',
                $evaluationIds,
                'id'
            );
            $this->deleteWhereIn('evaluation_criteria_scores', 'submission_id', $evaluationSubmissionIds);
            $this->deleteWhereIn('evaluation_section_scores', 'submission_id', $evaluationSubmissionIds);
            $this->deleteWhereIn('evaluation_submissions', 'id', $evaluationSubmissionIds);
            $this->deleteWhereIn('evaluation_assignments', 'evaluation_id', $evaluationIds);
            $this->deleteWhereIn('procurement_evaluations', 'evaluation_id', $evaluationIds);
            $this->deleteWhereIn('evaluation_criteria', 'id', $criteriaIds);
            $this->deleteWhereIn('evaluation_sections', 'id', $sectionIds);
            $this->deleteWhereIn('evaluations', 'id', $evaluationIds);
        }

        if (Schema::hasTable('prescreening_templates')) {
            $templateIds = DB::table('prescreening_templates')
                ->whereRaw("coalesce(description, '') like ?", ['%'.self::DATASET_TAG.'%'])
                ->pluck('id');
            $this->deleteWhereIn('prescreening_evaluations', 'prescreening_template_id', $templateIds);
            $this->deleteWhereIn('prescreening_results', 'prescreening_template_id', $templateIds);
            $this->deleteWhereIn('prescreening_template_procurements', 'prescreening_template_id', $templateIds);
            $this->deleteWhereIn('prescreening_criteria', 'prescreening_template_id', $templateIds);
            $this->deleteWhereIn('prescreening_sections', 'prescreening_template_id', $templateIds);
            $this->deleteWhereIn('prescreening_templates', 'id', $templateIds);
        }

        if (Schema::hasTable('myb_resources')) {
            DB::table('myb_resources')
                ->where('reference_code', 'like', self::RESOURCE_PREFIX.'%')
                ->orWhereRaw("coalesce(description, '') like ?", ['%'.self::DATASET_TAG.'%'])
                ->delete();
        }
        if (Schema::hasTable('myb_resource_categories')) {
            DB::table('myb_resource_categories')
                ->whereRaw("coalesce(description, '') like ?", ['%'.self::DATASET_TAG.'%'])
                ->delete();
        }
    }

    /** @return Collection<int, mixed> */
    private function pluckWhereIn(
        string $table,
        string $whereColumn,
        Collection $values,
        string $pluckColumn,
    ): Collection {
        if (
            $values->isEmpty()
            || ! Schema::hasTable($table)
            || ! Schema::hasColumn($table, $whereColumn)
            || ! Schema::hasColumn($table, $pluckColumn)
        ) {
            return collect();
        }

        return DB::table($table)
            ->whereIn($whereColumn, $values->all())
            ->pluck($pluckColumn)
            ->filter()
            ->unique()
            ->values();
    }

    private function deleteWhereIn(string $table, string $column, Collection $values): void
    {
        if (
            $values->isEmpty()
            || ! Schema::hasTable($table)
            || ! Schema::hasColumn($table, $column)
        ) {
            return;
        }

        DB::table($table)->whereIn($column, $values->all())->delete();
    }
}

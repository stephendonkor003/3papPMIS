<?php

namespace Database\Seeders;

use App\Models\GovernanceLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernanceLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['legacy_key' => 'au_assembly', 'key' => 'national_government', 'name' => 'National Government', 'sort_order' => 0, 'description' => 'The national government and executive authority of the Republic of Malawi.'],
            ['legacy_key' => 'auc_cabinet_of_the_chairperson', 'key' => 'executive_office', 'name' => 'Executive Office', 'sort_order' => 1, 'description' => 'Central executive offices that coordinate government policy, administration, and delivery.'],
            ['legacy_key' => 'cabinet_of_the_deputy_chairperson_cdcp', 'key' => 'ministry', 'name' => 'Ministry', 'sort_order' => 2, 'description' => 'Cabinet portfolios responsible for national policy, programmes, and public-service delivery.'],
            ['legacy_key' => 'office_of_the_commissioner', 'key' => 'government_department', 'name' => 'Government Department', 'sort_order' => 3, 'description' => 'Government departments responsible for specialised public administration and services.'],
            ['legacy_key' => 'director', 'key' => 'directorate_unit', 'name' => 'Directorate / Unit', 'sort_order' => 4, 'description' => 'Directorates and operational units within public institutions.'],
            ['legacy_key' => 'head_of_division', 'key' => 'agency_authority', 'name' => 'Agency / Authority', 'sort_order' => 5, 'description' => 'Statutory agencies and authorities with regulatory, oversight, or service-delivery mandates.'],
            ['legacy_key' => 'coordinator', 'key' => 'constitutional_body', 'name' => 'Constitutional Body / Commission', 'sort_order' => 6, 'description' => 'Independent constitutional bodies and public commissions.'],
            ['legacy_key' => 'senior_policy_officer', 'key' => 'parastatal_soe', 'name' => 'Parastatal / State-Owned Enterprise', 'sort_order' => 7, 'description' => 'Public corporations and state-owned enterprises delivering strategic national services.'],
            ['legacy_key' => 'policy_officer', 'key' => 'local_authority', 'name' => 'Local Authority / Council', 'sort_order' => 8, 'description' => 'City, municipal, town, and district councils responsible for local public services.'],
        ];

        DB::transaction(function () use ($levels): void {
            foreach ($levels as $definition) {
                $level = GovernanceLevel::query()
                    ->where('key', $definition['key'])
                    ->first()
                    ?? GovernanceLevel::query()
                        ->where('key', $definition['legacy_key'])
                        ->first()
                    ?? new GovernanceLevel;

                $level->forceFill([
                    'key' => $definition['key'],
                    'name' => $definition['name'],
                    'sort_order' => $definition['sort_order'],
                    'description' => $definition['description'],
                ])->save();
            }
        });
    }
}

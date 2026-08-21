<?php

namespace Database\Seeders;

use App\Models\GovernanceLevel;
use App\Models\GovernanceNode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GovernanceNodeSeeder extends Seeder
{
    public function run(): void
    {
        $nodes = [
            ['legacy_code' => 'AUA_HSG', 'level' => 'national_government', 'code' => 'GOM', 'name' => 'Government of the Republic of Malawi', 'description' => 'National government root for executive leadership, public administration, and portfolio delivery across Malawi.'],
            ['legacy_code' => 'CCP', 'level' => 'executive_office', 'code' => 'OPC', 'name' => 'Office of the President and Cabinet', 'description' => 'Advises the President, supports Cabinet, oversees the Public Service, and coordinates government policy, programmes, and delivery monitoring.'],
            ['legacy_code' => 'CDCP', 'level' => 'ministry', 'code' => 'MOS', 'name' => 'Ministry of State', 'description' => 'Supports executive coordination and delivery of cross-government priorities assigned by the Presidency and Cabinet.'],
            ['legacy_code' => 'COM_ARDE', 'level' => 'ministry', 'code' => 'MOAIWD', 'name' => 'Ministry of Agriculture, Irrigation and Water Development', 'description' => 'Leads policy and programmes for agriculture, food systems, irrigation, and water development.'],
            ['legacy_code' => 'COM_ESTI', 'level' => 'ministry', 'code' => 'MOEST', 'name' => 'Ministry of Education, Science and Technology', 'description' => 'Leads national education, science, technology, skills, and human-capital development policy.'],
            ['legacy_code' => 'COM_ETTIM', 'level' => 'ministry', 'code' => 'MOIBTT', 'name' => 'Ministry of Industrialisation, Business, Trade and Tourism', 'description' => 'Leads industrialisation, private-sector development, trade, investment, business growth, and tourism policy.'],
            ['legacy_code' => 'COM_HHS', 'level' => 'ministry', 'code' => 'MOHS', 'name' => 'Ministry of Health and Sanitation', 'description' => 'Leads public health, health-service delivery, disease prevention, and sanitation programmes.'],
            ['legacy_code' => 'COM_IED', 'level' => 'ministry', 'code' => 'MONREM', 'name' => 'Ministry of Natural Resources, Energy and Mining', 'description' => 'Leads policy for natural resources, environmental stewardship, energy, and mineral development.'],
            ['legacy_code' => 'COM_PAPS', 'level' => 'ministry', 'code' => 'MOHSEC', 'name' => 'Ministry of Homeland Security', 'description' => 'Leads internal security, immigration, civil protection, and public-safety policy.'],
            ['legacy_code' => 'DIR_GEN', 'level' => 'ministry', 'code' => 'MOD', 'name' => 'Ministry of Defence', 'description' => 'Leads national defence policy and civilian oversight of Malawi defence institutions.'],
            ['legacy_code' => 'DIR_ARD', 'level' => 'ministry', 'code' => 'MOLGRD', 'name' => 'Ministry of Local Government and Rural Development', 'description' => 'Leads decentralisation, local-government administration, and rural-development policy.'],
            ['legacy_code' => 'DIR_EDIT', 'level' => 'agency_authority', 'code' => 'MRA', 'name' => 'Malawi Revenue Authority', 'description' => 'Administers and collects domestic taxes and customs revenue on behalf of the Government of Malawi.'],
            ['legacy_code' => 'DIR_ENERGY', 'level' => 'parastatal_soe', 'code' => 'ESCOM', 'name' => 'Electricity Supply Corporation of Malawi (ESCOM) Limited', 'description' => 'State-owned electricity utility responsible for power transmission, distribution, and supply services in Malawi.'],
            ['legacy_code' => 'DIR_ESD', 'level' => 'ministry', 'code' => 'MOLSI', 'name' => 'Ministry of Labour Skills and Innovation', 'description' => 'Leads labour administration, employment, occupational standards, skills development, and innovation policy.'],
            ['legacy_code' => 'DIR_HHA', 'level' => 'ministry', 'code' => 'MOGCDSW', 'name' => 'Ministry of Gender, Children, Disability and Social Welfare', 'description' => 'Leads gender equality, child protection, disability inclusion, and social-welfare programmes.'],
            ['legacy_code' => 'DIR_IMET', 'level' => 'ministry', 'code' => 'MOLHUD', 'name' => 'Ministry of Lands, Housing and Urban Development', 'description' => 'Leads land administration, housing policy, physical planning, and sustainable urban development.'],
            ['legacy_code' => 'DIR_INFRA_TPT', 'level' => 'ministry', 'code' => 'MOTPW', 'name' => 'Ministry of Transport and Public Works', 'description' => 'Leads transport policy, public infrastructure, roads, buildings, and public-works delivery.'],
            ['legacy_code' => 'DIR_MISD', 'level' => 'ministry', 'code' => 'MOICT', 'name' => 'Ministry of Information and Communications Technology', 'description' => 'Leads government communications, digitalisation, information services, and national ICT policy.'],
            ['legacy_code' => 'DIR_OSSD', 'level' => 'agency_authority', 'code' => 'PPDA', 'name' => 'Public Procurement and Disposal of Assets Authority', 'description' => 'Independent statutory authority regulating, monitoring, and overseeing public procurement and disposal of public assets in Malawi.'],
            ['legacy_code' => 'DIR_PAG', 'level' => 'ministry', 'code' => 'MOJCA', 'name' => 'Ministry of Justice and Constitutional Affairs', 'description' => 'Leads justice-sector policy, constitutional affairs, legal services, and law reform coordination.'],
            ['legacy_code' => 'DIR_PAS', 'level' => 'ministry', 'code' => 'MOFAIC', 'name' => 'Ministry of Foreign Affairs and International Cooperation', 'description' => 'Leads diplomacy, international relations, treaty engagement, and international cooperation.'],
            ['legacy_code' => 'DIR_PMRM', 'level' => 'ministry', 'code' => 'MOFEPD', 'name' => 'Ministry of Finance, Economic Planning and Decentralisation', 'description' => 'Leads public finance, budgeting, economic planning, fiscal policy, and decentralisation financing.'],
            ['legacy_code' => 'DIR_SDCS', 'level' => 'ministry', 'code' => 'MOYSC', 'name' => 'Ministry of Youth, Sports and Culture', 'description' => 'Leads youth development, sport, recreation, arts, culture, and heritage policy.'],
            ['legacy_code' => 'DIR_SEBE', 'level' => 'parastatal_soe', 'code' => 'MPC', 'name' => 'Malawi Posts Corporation', 'description' => 'Public postal corporation providing mail, courier, logistics, and related communication services.'],
            ['legacy_code' => 'DIR_SPD', 'level' => 'agency_authority', 'code' => 'RBM', 'name' => 'Reserve Bank of Malawi', 'description' => 'Malawi central bank responsible for monetary policy, financial stability, currency, and payment-system oversight.'],
            ['legacy_code' => 'DIR_STI', 'level' => 'agency_authority', 'code' => 'MACRA', 'name' => 'Malawi Communications Regulatory Authority', 'description' => 'Regulates electronic communications, broadcasting, postal services, and related communications markets in Malawi.'],
        ];

        DB::transaction(function () use ($nodes): void {
            $levelIds = GovernanceLevel::query()->pluck('id', 'key');

            foreach ($nodes as $definition) {
                $levelId = $levelIds->get($definition['level']);

                if (! $levelId) {
                    throw new RuntimeException("Missing governance level [{$definition['level']}]. Run GovernanceLevelSeeder first.");
                }

                $node = GovernanceNode::query()
                    ->where('code', $definition['code'])
                    ->first()
                    ?? GovernanceNode::query()
                        ->where('code', $definition['legacy_code'])
                        ->first()
                    ?? new GovernanceNode;

                $node->forceFill([
                    'level_id' => $levelId,
                    'name' => $definition['name'],
                    'code' => $definition['code'],
                    'description' => $definition['description'],
                    'status' => 'active',
                    'effective_start' => '2025-10-30',
                    'effective_end' => null,
                    'deleted_at' => null,
                ])->save();
            }
        });
    }
}

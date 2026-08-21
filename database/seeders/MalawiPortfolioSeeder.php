<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MalawiPortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $portfolios = [
            [
                'id' => '019eab4e-24e6-71bf-a567-538377a6c9e8',
                'legacy_name' => 'Agriculture and Rural Development (ARDE)',
                'name' => 'Agriculture, Irrigation and Water Development Portfolio',
                'description' => 'Coordinates Government of Malawi investments in agricultural productivity, food security, irrigation, water development, and resilient rural livelihoods.',
            ],
            [
                'id' => '019eab4c-bb14-71e9-a213-ef1c1f04373e',
                'legacy_name' => 'Economic Development, Tourism, Trade, Industry, and Mining (ETTIM)',
                'name' => 'Industrialisation, Business, Trade and Tourism Portfolio',
                'description' => 'Coordinates industrialisation, enterprise development, trade, investment, tourism, competitiveness, and private-sector growth in Malawi.',
            ],
            [
                'id' => '019eab4e-9add-7019-977c-cd3e9447faa6',
                'legacy_name' => 'Education, Science, Technology, and Innovation (ESTI)',
                'name' => 'Education, Science and Technology Portfolio',
                'description' => 'Coordinates equitable education, skills development, science, technology, innovation, and digital learning investments across Malawi.',
            ],
            [
                'id' => '019ea974-49e9-7099-ac6e-83b4ff6caf99',
                'legacy_name' => 'Governance',
                'name' => 'Public Governance and Institutional Reform Portfolio',
                'description' => 'Coordinates public-sector governance, institutional reform, accountability, justice, and citizen-centred service delivery in Malawi.',
            ],
            [
                'id' => '019eab4f-01d6-7280-8090-19ca6d536400',
                'legacy_name' => 'Health and Humanitarian Affairs (HHE)',
                'name' => 'Health and Sanitation Portfolio',
                'description' => 'Coordinates health systems, sanitation, public health, disease prevention, emergency preparedness, and equitable health-service delivery.',
            ],
            [
                'id' => '019eab4f-a1ac-72be-84d2-3ae98a88fe7c',
                'legacy_name' => 'Infrastructure and Energy (IE)',
                'name' => 'Natural Resources, Energy and Mining Portfolio',
                'description' => 'Coordinates natural-resource stewardship, energy access, mining development, environmental management, and climate-resilient investment.',
            ],
            [
                'id' => '019eab51-093d-70a2-b3f4-b81e6c57fc4c',
                'legacy_name' => 'Office of Strategic Planning and Delivery (OSPD)',
                'name' => 'National Planning and Government Delivery Portfolio',
                'description' => 'Coordinates national planning, Cabinet priorities, cross-government performance, implementation monitoring, and delivery assurance.',
            ],
            [
                'id' => '019eab52-1676-7223-9a28-fff59070ab7d',
                'legacy_name' => 'Operational Support Services Directorate (OSSD)',
                'name' => 'Public Service Operations and Coordination Portfolio',
                'description' => 'Coordinates public-service operations, administrative systems, logistics, procurement support, and cross-government implementation.',
            ],
            [
                'id' => '019eab51-8d88-70e1-9ce5-a5933f9a28ff',
                'legacy_name' => 'Partnership Management and Resource Mobilization (PMRM)',
                'name' => 'Strategic Partnerships and Resource Mobilisation Portfolio',
                'description' => 'Coordinates development cooperation, strategic partnerships, resource mobilisation, investment alignment, and programme financing for Malawi.',
            ],
            [
                'id' => '019eab4d-9e99-701f-8f99-845d281de9b3',
                'legacy_name' => 'Sustainable Environment and Blue Economy (SEBE)',
                'name' => 'Climate-Smart Agriculture and Water Resilience Portfolio',
                'description' => 'Coordinates climate-smart agriculture, watershed protection, water security, ecosystem resilience, and sustainable rural development.',
            ],
        ];

        DB::transaction(function () use ($portfolios): void {
            foreach ($portfolios as $definition) {
                $portfolio = Sector::query()->find($definition['id'])
                    ?? Sector::query()->where('name', $definition['name'])->first()
                    ?? Sector::query()->where('name', $definition['legacy_name'])->first();

                if (! $portfolio) {
                    throw new RuntimeException("Portfolio [{$definition['legacy_name']}] could not be found safely.");
                }

                $portfolio->forceFill([
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                ])->save();
            }
        });
    }
}

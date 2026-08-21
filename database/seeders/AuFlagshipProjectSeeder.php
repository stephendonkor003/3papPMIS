<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuFlagshipProject;

class AuFlagshipProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds all 12 AU Flagship Projects from Agenda 2063.
     */
    public function run(): void
    {
        $flagshipProjects = [
            [
                'number' => 1,
                'name' => 'Integrated High-Speed Train Network',
                'description' => 'Connect all African capitals and commercial centres through an African High-Speed Train Network to facilitate movement of goods, services and passengers.',
            ],
            [
                'number' => 2,
                'name' => 'African Commodity Strategy',
                'description' => 'Enable African countries to add value, extract higher rents from their commodities, integrate into global value chains, and promote vertical and horizontal diversification.',
            ],
            [
                'number' => 3,
                'name' => 'African Continental Free Trade Area (AfCFTA)',
                'description' => 'Accelerate intra-African trade and boost Africa\'s trading position in the global market by strengthening Africa\'s common voice and policy space in global trade negotiations.',
            ],
            [
                'number' => 4,
                'name' => 'African Passport and Free Movement of People',
                'description' => 'Remove restrictions on Africans ability to travel, work and live within their own continent through the issuance of an African Passport.',
            ],
            [
                'number' => 5,
                'name' => 'Silencing the Guns by 2020',
                'description' => 'End all wars, civil conflicts, gender-based violence, violent conflicts and prevent genocide in the continent.',
            ],
            [
                'number' => 6,
                'name' => 'Grand Inga Dam Project',
                'description' => 'Develop the Inga Dam to generate about 43,200 MW of power to support current and future industrialization of Africa.',
            ],
            [
                'number' => 7,
                'name' => 'Single African Air Transport Market (SAATM)',
                'description' => 'Create a single unified air transport market in Africa to advance the liberalization of civil aviation in Africa.',
            ],
            [
                'number' => 8,
                'name' => 'African Outer Space Strategy',
                'description' => 'Develop and implement an African Outer Space Strategy towards strengthening Africa\'s use of outer space to bolster development.',
            ],
            [
                'number' => 9,
                'name' => 'African Virtual and E-University',
                'description' => 'Develop the Pan African Virtual and E-University to increase access to tertiary and continuing education in Africa by reaching large numbers of students.',
            ],
            [
                'number' => 10,
                'name' => 'Cyber Security',
                'description' => 'Put in place robust cyber security measures to combat cybercrime and protect Africans in cyberspace.',
            ],
            [
                'number' => 11,
                'name' => 'Great African Museum',
                'description' => 'Create a Trans African Museum of History of Africa focusing on the history of the African people and their diaspora.',
            ],
            [
                'number' => 12,
                'name' => 'Encyclopedia Africana',
                'description' => 'Compile and publish Encyclopedia Africana, a compendium of African history, culture and peoples.',
            ],
        ];

        foreach ($flagshipProjects as $project) {
            AuFlagshipProject::updateOrCreate(
                ['number' => $project['number']],
                [
                    'name' => $project['name'],
                    'description' => $project['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}

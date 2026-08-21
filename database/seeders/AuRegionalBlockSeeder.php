<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuRegionalBlock;

class AuRegionalBlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds all 8 AU-recognized Regional Economic Communities (RECs).
     */
    public function run(): void
    {
        $regionalBlocks = [
            [
                'name' => 'Arab Maghreb Union',
                'abbreviation' => 'UMA',
                'description' => 'Regional bloc comprising Algeria, Libya, Mauritania, Morocco, and Tunisia, aimed at economic and cultural integration of the Maghreb region.',
            ],
            [
                'name' => 'Common Market for Eastern and Southern Africa',
                'abbreviation' => 'COMESA',
                'description' => 'Free trade area stretching from Libya to Eswatini with 21 member states, focused on regional economic integration and development.',
            ],
            [
                'name' => 'Community of Sahel-Saharan States',
                'abbreviation' => 'CEN-SAD',
                'description' => 'Regional bloc of 29 African states aimed at creating an economic union and promoting free movement of people, goods, and capital.',
            ],
            [
                'name' => 'East African Community',
                'abbreviation' => 'EAC',
                'description' => 'Intergovernmental organization comprising Burundi, DR Congo, Kenya, Rwanda, South Sudan, Tanzania, and Uganda, with a customs union and common market.',
            ],
            [
                'name' => 'Economic Community of Central African States',
                'abbreviation' => 'ECCAS',
                'description' => 'Economic community of Central African states promoting regional economic cooperation and establishing a Central African common market.',
            ],
            [
                'name' => 'Economic Community of West African States',
                'abbreviation' => 'ECOWAS',
                'description' => 'Regional political and economic union of 15 countries in West Africa, promoting economic integration and collective self-sufficiency.',
            ],
            [
                'name' => 'Intergovernmental Authority on Development',
                'abbreviation' => 'IGAD',
                'description' => 'Regional development organization in Eastern Africa focusing on food security, environmental protection, peace and security, and economic cooperation.',
            ],
            [
                'name' => 'Southern African Development Community',
                'abbreviation' => 'SADC',
                'description' => 'Regional economic community of 16 member states in Southern Africa, focused on socioeconomic and political integration and poverty eradication.',
            ],
        ];

        foreach ($regionalBlocks as $index => $block) {
            AuRegionalBlock::updateOrCreate(
                ['abbreviation' => $block['abbreviation']],
                [
                    'name' => $block['name'],
                    'description' => $block['description'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}

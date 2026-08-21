<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuAspiration;
use App\Models\AuGoal;

class AuAgenda2063Seeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds all 7 Aspirations and 20 Goals from Agenda 2063.
     */
    public function run(): void
    {
        $aspirations = [
            [
                'number' => 1,
                'title' => 'A prosperous Africa based on inclusive growth and sustainable development',
                'description' => 'A transformed and prosperous continent through inclusive growth and sustainable development.',
                'goals' => [
                    ['number' => 1, 'title' => 'A high standard of living, quality of life and well-being for all citizens'],
                    ['number' => 2, 'title' => 'Well educated citizens and skills revolution underpinned by science, technology and innovation'],
                    ['number' => 3, 'title' => 'Healthy and well-nourished citizens'],
                    ['number' => 4, 'title' => 'Transformed economies'],
                    ['number' => 5, 'title' => 'Modern agriculture for increased productivity and production'],
                    ['number' => 6, 'title' => 'Blue/ocean economy for accelerated economic growth'],
                    ['number' => 7, 'title' => 'Environmentally sustainable and climate resilient economies and communities'],
                ],
            ],
            [
                'number' => 2,
                'title' => 'An integrated continent, politically united based on the ideals of Pan-Africanism and the vision of Africa\'s Renaissance',
                'description' => 'Political unity based on Pan-Africanism and Africa\'s Renaissance.',
                'goals' => [
                    ['number' => 8, 'title' => 'United Africa (Federal or Confederate)'],
                    ['number' => 9, 'title' => 'Continental financial and monetary institutions are established and functional'],
                    ['number' => 10, 'title' => 'World-class infrastructure criss-crosses Africa'],
                ],
            ],
            [
                'number' => 3,
                'title' => 'An Africa of good governance, democracy, respect for human rights, justice and the rule of law',
                'description' => 'Democratic values, practices, universal principles of human rights, justice and the rule of law entrenched.',
                'goals' => [
                    ['number' => 11, 'title' => 'Democratic values, practices, universal principles of human rights, justice and the rule of law entrenched'],
                    ['number' => 12, 'title' => 'Capable institutions and transformative leadership in place'],
                ],
            ],
            [
                'number' => 4,
                'title' => 'A peaceful and secure Africa',
                'description' => 'Peace, security and stability are preserved through mechanisms for peaceful prevention and resolution of conflicts.',
                'goals' => [
                    ['number' => 13, 'title' => 'Peace, security and stability are preserved'],
                    ['number' => 14, 'title' => 'A stable and peaceful Africa'],
                    ['number' => 15, 'title' => 'A fully functional and operational African Peace and Security Architecture'],
                ],
            ],
            [
                'number' => 5,
                'title' => 'An Africa with a strong cultural identity, common heritage, values and ethics',
                'description' => 'African cultural renaissance through promotion of African culture and heritage.',
                'goals' => [
                    ['number' => 16, 'title' => 'African cultural renaissance is pre-eminent'],
                ],
            ],
            [
                'number' => 6,
                'title' => 'An Africa whose development is people-driven, relying on the potential of African people, especially its women and youth',
                'description' => 'Full gender equality, youth and child empowerment.',
                'goals' => [
                    ['number' => 17, 'title' => 'Full gender equality in all spheres of life'],
                    ['number' => 18, 'title' => 'Engaged and empowered youth and children'],
                ],
            ],
            [
                'number' => 7,
                'title' => 'Africa as a strong, united and influential global player and partner',
                'description' => 'Africa is a major partner in global affairs and peaceful co-existence.',
                'goals' => [
                    ['number' => 19, 'title' => 'Africa as a major partner in global affairs and peaceful co-existence'],
                    ['number' => 20, 'title' => 'Africa takes full responsibility for financing her development'],
                ],
            ],
        ];

        foreach ($aspirations as $aspirationData) {
            $aspiration = AuAspiration::updateOrCreate(
                ['number' => $aspirationData['number']],
                [
                    'title' => $aspirationData['title'],
                    'description' => $aspirationData['description'],
                    'is_active' => true,
                ]
            );

            foreach ($aspirationData['goals'] as $goalData) {
                AuGoal::updateOrCreate(
                    ['number' => $goalData['number']],
                    [
                        'aspiration_id' => $aspiration->id,
                        'title' => $goalData['title'],
                        'description' => null,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}

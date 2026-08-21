<?php

namespace Database\Seeders;

use App\Models\GovernanceNode;
use App\Models\GovernanceNodeAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class GovernanceAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id', 'email');
        $nodes = GovernanceNode::pluck('id', 'name');

        $assignments = [
            [
                'email' => 'system.admin@example.invalid',
                'node' => 'Office of the President and Cabinet',
                'role_title' => 'Cabinet Administration Lead',
                'is_primary' => true,
            ],
            [
                'email' => 'finance.manager@example.invalid',
                'node' => 'Ministry of Finance, Economic Planning and Decentralisation',
                'role_title' => 'Finance Manager',
                'is_primary' => true,
            ],
            [
                'email' => 'finance.officer@example.invalid',
                'node' => 'Ministry of Finance, Economic Planning and Decentralisation',
                'role_title' => 'Finance Officer',
                'is_primary' => false,
            ],
            [
                'email' => 'budget.officer@example.invalid',
                'node' => 'Ministry of Finance, Economic Planning and Decentralisation',
                'role_title' => 'Budget Officer',
                'is_primary' => true,
            ],
            [
                'email' => 'hr.manager@example.invalid',
                'node' => 'Office of the President and Cabinet',
                'role_title' => 'HR Manager',
                'is_primary' => true,
            ],
            [
                'email' => 'prescreener@example.invalid',
                'node' => 'Public Procurement and Disposal of Assets Authority',
                'role_title' => 'Procurement Prescreening Officer',
                'is_primary' => true,
            ],
            [
                'email' => 'evaluator@example.invalid',
                'node' => 'Public Procurement and Disposal of Assets Authority',
                'role_title' => 'Procurement Evaluation Officer',
                'is_primary' => true,
            ],
        ];

        foreach ($assignments as $assignment) {
            $userId = $users[$assignment['email']] ?? null;
            $nodeId = $nodes[$assignment['node']] ?? null;

            if (! $userId || ! $nodeId) {
                continue;
            }

            GovernanceNodeAssignment::updateOrCreate(
                [
                    'user_id' => $userId,
                    'node_id' => $nodeId,
                    'role_title' => $assignment['role_title'],
                ],
                [
                    'is_primary' => $assignment['is_primary'],
                ]
            );
        }
    }
}

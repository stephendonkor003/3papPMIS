<?php

namespace Database\Seeders;

use App\Models\GovernanceNode;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatter;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'name');
        $nodes = GovernanceNode::pluck('id', 'name');

        $users = [
            [
                'name' => 'System Admin',
                'email' => 'system.admin@example.invalid',
                'role' => 'System Admin',
                'user_type' => 'admin',
                'node' => 'African Union Commission',
            ],
            [
                'name' => 'Prescreening Evaluator',
                'email' => 'prescreener@example.invalid',
                'role' => 'Prescreening Evaluator',
                'user_type' => 'staff',
                'node' => 'Directorate of Governance',
            ],
            [
                'name' => 'Evaluation Evaluator',
                'email' => 'evaluator@example.invalid',
                'role' => 'Evaluation Evaluator',
                'user_type' => 'staff',
                'node' => 'Directorate of Elections',
            ],
            [
                'name' => 'HR Manager',
                'email' => 'hr.manager@example.invalid',
                'role' => 'HR Manager',
                'user_type' => 'staff',
                'node' => 'African Union Commission',
            ],
            [
                'name' => 'HR Officer',
                'email' => 'hr.officer@example.invalid',
                'role' => 'HR Officer',
                'user_type' => 'staff',
                'node' => 'African Union Commission',
            ],
            [
                'name' => 'Finance Manager',
                'email' => 'finance.manager@example.invalid',
                'role' => 'Finance Manager',
                'user_type' => 'staff',
                'node' => 'Department of Trade',
            ],
            [
                'name' => 'Finance Officer',
                'email' => 'finance.officer@example.invalid',
                'role' => 'Finance Officer',
                'user_type' => 'staff',
                'node' => 'Department of Trade',
            ],
            [
                'name' => 'Budget Officer',
                'email' => 'budget.officer@example.invalid',
                'role' => 'Budget Officer',
                'user_type' => 'staff',
                'node' => 'Department of Health',
            ],
            [
                'name' => 'Auditor',
                'email' => 'auditor@example.invalid',
                'role' => 'Auditor',
                'user_type' => 'staff',
                'node' => 'African Union Commission',
            ],
        ];

        foreach ($users as $user) {
            $createdUser = User::firstOrNew(['email' => $user['email']]);
            $isNew = ! $createdUser->exists;

            if ($isNew) {
                $password = $this->randomCreationPassword();
                $createdUser->fill([
                    'name' => $user['name'],
                    'password' => Hash::make($password),
                    'user_type' => $user['user_type'],
                    'must_change_password' => true,
                    'password_changed_at' => null,
                    'otp_verified_at' => null,
                    'role_id' => $roles[$user['role']] ?? null,
                    'governance_node_id' => $nodes[$user['node']] ?? null,
                    'is_disabled' => false,
                    'is_blacklisted' => false,
                ])->save();

                $escapedPassword = OutputFormatter::escape($password);
                $this->command?->warn(
                    "Created sample user {$user['email']}. Temporary password (shown once): {$escapedPassword}"
                );
                $this->command?->warn(
                    'A password change is required at first login; later seeder runs will preserve the current password.'
                );
            }

            if ($user['role'] === 'System Admin') {
                $createdUser->permissions()->sync(Permission::pluck('id'));
            }

            if ($user['role'] === 'Evaluation Evaluator') {
                $createdUser->permissions()->syncWithoutDetaching(
                    Permission::whereIn('name', [
                        'evaluations.evaluate',
                    ])->pluck('id')
                );
            }
        }
    }

    private function randomCreationPassword(): string
    {
        if (! isset($this->command)) {
            throw new RuntimeException(
                'Sample user provisioning requires an interactive seeder command so each random temporary password can be shown once.'
            );
        }

        return Str::password(24);
    }
}

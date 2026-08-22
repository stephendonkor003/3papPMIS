<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class MasterAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'System Admin'],
            ['description' => 'Full system administrator']
        );
        $permissionIds = Permission::pluck('id')->all();
        $passwordHash = $this->configuredPasswordHash();
        $synchronizePassword = (bool) config('super-admins.synchronize_password_on_seed', true);
        $forcePasswordChange = (bool) config('super-admins.force_password_change', true);

        $admins = collect($this->configuredAccounts())->map(function (array $account) use (
            $role,
            $passwordHash,
            $synchronizePassword,
            $forcePasswordChange
        ): User {
            $admin = User::firstOrNew(['email' => $account['email']]);
            $isNew = ! $admin->exists;

            $admin->fill([
                'name' => $isNew || blank($admin->name) ? $account['name'] : $admin->name,
                'user_type' => 'admin',
                'role_id' => $role->id,
            ]);

            if ($isNew || $synchronizePassword) {
                $admin->forceFill([
                    'password' => $passwordHash,
                    'must_change_password' => $forcePasswordChange,
                    'password_changed_at' => $forcePasswordChange ? null : now(),
                    'otp_verified_at' => null,
                ]);
            }

            if ($isNew) {
                $admin->forceFill([
                    'email_verified_at' => now(),
                    'is_disabled' => false,
                    'disabled_at' => null,
                    'disabled_until' => null,
                    'disabled_reason' => null,
                    'is_blacklisted' => false,
                    'blacklisted_at' => null,
                    'blacklisted_reason' => null,
                ]);
            } elseif (! $admin->email_verified_at) {
                $admin->forceFill(['email_verified_at' => now()]);
            }

            $admin->save();

            $action = $isNew ? 'Created' : ($synchronizePassword ? 'Synchronized' : 'Updated access for');
            $this->command?->info("{$action} deployment super administrator {$account['email']}.");

            return $admin;
        });

        $role->permissions()->sync($permissionIds);
        $admins->each(fn (User $admin) => $admin->permissions()->sync($permissionIds));

        if ($synchronizePassword) {
            $this->command?->warn(
                'Bootstrap password synchronization is enabled. Set MASTER_ADMIN_SYNC_PASSWORD_ON_SEED=false after initial deployment to preserve later password rotations.'
            );
        }

        if ($forcePasswordChange) {
            $this->command?->warn('A password change is required at the next login for each deployment super administrator.');
        }
    }

    /** @return array<int, array{name: string, email: string}> */
    private function configuredAccounts(): array
    {
        $configuredEmails = trim((string) config('super-admins.email_override', ''));

        if ($configuredEmails !== '') {
            $emails = array_values(array_unique(array_filter(
                array_map('trim', explode(',', $configuredEmails)),
                static fn (string $email): bool => $email !== ''
            )));

            $accounts = array_map(fn (string $email, int $index): array => [
                'name' => 'Deployment Master Administrator '.($index + 1),
                'email' => $email,
            ], $emails, array_keys($emails));
        } else {
            $accounts = config('super-admins.accounts', []);
        }

        if (! is_array($accounts) || $accounts === []) {
            throw new RuntimeException(
                'No deployment super administrators are configured. Run php artisan optimize:clear after pulling new configuration, then seed again.'
            );
        }

        $validated = [];
        $seen = [];

        foreach ($accounts as $index => $account) {
            $email = strtolower(trim((string) ($account['email'] ?? '')));
            $name = trim((string) ($account['name'] ?? '')) ?: 'Deployment Master Administrator '.($index + 1);

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException("Invalid deployment super administrator email at index {$index}.");
            }

            if (isset($seen[$email])) {
                continue;
            }

            $seen[$email] = true;
            $validated[] = ['name' => $name, 'email' => $email];
        }

        return $validated;
    }

    private function configuredPasswordHash(): string
    {
        $configuredPassword = config('super-admins.bootstrap_password');

        if (is_string($configuredPassword) && $configuredPassword !== '') {
            if (strlen($configuredPassword) < 16) {
                throw new RuntimeException('MASTER_ADMIN_BOOTSTRAP_PASSWORD must contain at least 16 characters.');
            }

            return Hash::make($configuredPassword);
        }

        $configuredHash = trim((string) config('super-admins.bootstrap_password_hash', ''));

        if (($passwordInfo = password_get_info($configuredHash))['algoName'] !== 'bcrypt') {
            throw new RuntimeException('The deployment super administrator password hash must use bcrypt.');
        }

        return $configuredHash;
    }
}

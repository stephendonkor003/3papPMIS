<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatter;

class SecondarySuperAdminSeeder extends Seeder
{
    public const EMAIL = 'secondary.system.admin@example.invalid';

    public const NAME = 'Secondary System Administrator';

    private const EMAIL_ENV = 'SECONDARY_SUPER_ADMIN_EMAIL';

    private const NAME_ENV = 'SECONDARY_SUPER_ADMIN_NAME';

    private const PASSWORD_ENV = 'SECONDARY_SUPER_ADMIN_BOOTSTRAP_PASSWORD';

    public function run(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'System Admin'],
            ['description' => 'Full system administrator']
        );

        $email = $this->configuredEmail();
        $name = trim((string) env(self::NAME_ENV, self::NAME)) ?: self::NAME;
        $admin = User::firstOrNew(['email' => $email]);
        $isNew = ! $admin->exists;

        $admin->fill([
            'name' => $name,
            'user_type' => 'admin',
            'role_id' => $role->id,
        ]);

        if ($isNew) {
            [$password, $wasGenerated] = $this->creationPassword();
            $admin->forceFill([
                'password' => Hash::make($password),
                'must_change_password' => true,
                'password_changed_at' => null,
                'otp_verified_at' => null,
                'is_disabled' => false,
                'is_blacklisted' => false,
            ]);
        }

        $admin->save();

        $isNew
            ? $this->reportNewAccount($email, $password, $wasGenerated)
            : $this->command?->line("Existing secondary administrator {$email} kept its password, password-change status, and account restrictions.");

        $permissionIds = Permission::pluck('id')->all();

        $role->permissions()->sync($permissionIds);
        $admin->permissions()->sync($permissionIds);
    }

    private function configuredEmail(): string
    {
        $email = trim((string) env(self::EMAIL_ENV, self::EMAIL));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(self::EMAIL_ENV.' must contain a valid email address.');
        }

        return $email;
    }

    /** @return array{string, bool} */
    private function creationPassword(): array
    {
        $configuredPassword = env(self::PASSWORD_ENV);

        if ($configuredPassword !== null && $configuredPassword !== '') {
            $configuredPassword = (string) $configuredPassword;

            if (strlen($configuredPassword) < 16) {
                throw new RuntimeException(self::PASSWORD_ENV.' must contain at least 16 characters.');
            }

            return [$configuredPassword, false];
        }

        if (! isset($this->command)) {
            throw new RuntimeException(
                'Set '.self::PASSWORD_ENV.' when provisioning without an interactive seeder command.'
            );
        }

        return [Str::password(24), true];
    }

    private function reportNewAccount(string $email, string $password, bool $wasGenerated): void
    {
        if ($wasGenerated) {
            $escapedPassword = OutputFormatter::escape($password);
            $this->command?->warn("Created secondary administrator {$email}. Temporary password (shown once): {$escapedPassword}");
        } else {
            $this->command?->info("Created secondary administrator {$email} using ".self::PASSWORD_ENV.'.');
        }

        $this->command?->warn('A password change is required at first login; later seeder runs will preserve the current password.');
    }
}

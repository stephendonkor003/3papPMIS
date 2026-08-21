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

class MasterAdminSeeder extends Seeder
{
    public const EMAIL = 'master.admin@example.invalid';

    public const ALTERNATE_EMAIL = 'alternate.master.admin@example.invalid';

    private const EMAIL_ENV = 'MASTER_ADMIN_EMAIL';

    private const ALTERNATE_EMAIL_ENV = 'MASTER_ADMIN_ALTERNATE_EMAIL';

    private const PASSWORD_ENV = 'MASTER_ADMIN_BOOTSTRAP_PASSWORD';

    private const ALTERNATE_PASSWORD_ENV = 'MASTER_ADMIN_ALTERNATE_BOOTSTRAP_PASSWORD';

    public function run(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'System Admin'],
            ['description' => 'Full system administrator']
        );

        $admins = collect([
            [$this->configuredEmail(self::EMAIL_ENV, self::EMAIL), self::PASSWORD_ENV],
            [$this->configuredEmail(self::ALTERNATE_EMAIL_ENV, self::ALTERNATE_EMAIL), self::ALTERNATE_PASSWORD_ENV],
        ])->map(function (array $account) use ($role): User {
            [$email, $passwordEnvironment] = $account;
            $admin = User::firstOrNew(['email' => $email]);
            $isNew = ! $admin->exists;

            $admin->fill([
                'name' => 'Master Admin',
                'user_type' => 'admin',
                'role_id' => $role->id,
            ]);

            if ($isNew) {
                [$password, $wasGenerated] = $this->creationPassword($passwordEnvironment);
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
                ? $this->reportNewAccount($email, $passwordEnvironment, $password, $wasGenerated)
                : $this->command?->line("Existing bootstrap account {$email} kept its password, password-change status, and account restrictions.");

            return $admin;
        });

        $role->permissions()->sync(Permission::pluck('id')->all());
        $admins->each(fn (User $admin) => $admin->permissions()->sync(Permission::pluck('id')->all()));
    }

    private function configuredEmail(string $environmentVariable, string $default): string
    {
        $email = trim((string) env($environmentVariable, $default));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("{$environmentVariable} must contain a valid email address.");
        }

        return $email;
    }

    /** @return array{string, bool} */
    private function creationPassword(string $environmentVariable): array
    {
        $configuredPassword = env($environmentVariable);

        if ($configuredPassword !== null && $configuredPassword !== '') {
            $configuredPassword = (string) $configuredPassword;

            if (strlen($configuredPassword) < 16) {
                throw new RuntimeException("{$environmentVariable} must contain at least 16 characters.");
            }

            return [$configuredPassword, false];
        }

        if (! isset($this->command)) {
            throw new RuntimeException(
                "Set {$environmentVariable} when provisioning without an interactive seeder command."
            );
        }

        return [Str::password(24), true];
    }

    private function reportNewAccount(
        string $email,
        string $environmentVariable,
        string $password,
        bool $wasGenerated
    ): void {
        if ($wasGenerated) {
            $escapedPassword = OutputFormatter::escape($password);
            $this->command?->warn("Created bootstrap account {$email}. Temporary password (shown once): {$escapedPassword}");
        } else {
            $this->command?->info("Created bootstrap account {$email} using {$environmentVariable}.");
        }

        $this->command?->warn('A password change is required at first login; later seeder runs will preserve the current password.');
    }
}

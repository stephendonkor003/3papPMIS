<?php

namespace Database\Seeders;

use App\Models\Funder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatter;

class WorldBankPartnerAccessSeeder extends Seeder
{
    public const EMAIL = 'world-bank.partner@example.invalid';

    private const EMAIL_ENV = 'WORLD_BANK_PARTNER_EMAIL';

    private const PASSWORD_ENV = 'WORLD_BANK_PARTNER_BOOTSTRAP_PASSWORD';

    public function run(): void
    {
        $role = Role::where('name', 'Funding Partner')->first();
        $email = $this->configuredEmail();

        $user = User::firstOrNew(['email' => $email]);
        $isNew = ! $user->exists;

        $user->fill([
            'name' => 'World Bank Partner',
            'user_type' => 'funding_partner',
            'role_id' => $role?->id,
        ]);

        if ($isNew) {
            [$password, $wasGenerated] = $this->creationPassword();
            $user->forceFill([
                'password' => Hash::make($password),
                'must_change_password' => true,
                'password_changed_at' => null,
                'otp_verified_at' => null,
                'is_disabled' => false,
                'is_blacklisted' => false,
            ]);
        }

        $user->save();

        $isNew
            ? $this->reportNewAccount($password, $wasGenerated)
            : $this->command?->line('Existing World Bank partner account kept its password, password-change status, and account restrictions.');

        $funder = Funder::where('name', 'World Bank')
            ->orWhere('name', 'World Bank Group')
            ->orWhere('contact_email', $email)
            ->first();

        if (! $funder) {
            $funder = new Funder([
                'name' => 'World Bank',
                'type' => 'donor',
                'currency' => 'USD',
            ]);
        }

        $funder->fill([
            'name' => 'World Bank',
            'contact_person' => 'World Bank Partner',
            'contact_email' => $email,
            'has_portal_access' => true,
            'user_id' => $user->id,
        ]);

        $funder->save();
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

    private function reportNewAccount(string $password, bool $wasGenerated): void
    {
        if ($wasGenerated) {
            $escapedPassword = OutputFormatter::escape($password);
            $this->command?->warn('Created World Bank partner account. Temporary password (shown once): '.$escapedPassword);
        } else {
            $this->command?->info('Created World Bank partner account using '.self::PASSWORD_ENV.'.');
        }

        $this->command?->warn('A password change is required at first login; later seeder runs will preserve the current password.');
    }
}

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

class FundingPartnerSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'Funding Partner')->first();

        if (! $role) {
            $this->command?->warn('Funding Partner role is missing; run PartnerPortalPermissionsSeeder first.');

            return;
        }

        $partners = [
            [
                'name' => 'World Bank Group',
                'email' => $this->worldBankPartnerEmail(),
                'contact' => 'World Bank Partner Contact',
            ],
            [
                'name' => 'African Development Bank (AfDB)',
                'email' => 'afdb.partner@example.invalid',
                'contact' => 'African Development Bank Partner Contact',
            ],
            [
                'name' => 'African Export-Import Bank (Afreximbank)',
                'email' => 'afreximbank.partner@example.invalid',
                'contact' => 'Afreximbank Partner Contact',
            ],
            [
                'name' => 'Islamic Development Bank',
                'email' => 'isdb.partner@example.invalid',
                'contact' => 'Islamic Development Bank Partner Contact',
            ],
            [
                'name' => 'African Union Development Agency (AUDA-NEPAD)',
                'email' => 'auda-nepad.partner@example.invalid',
                'contact' => 'AUDA-NEPAD Partner Contact',
            ],
            [
                'name' => 'European Investment Bank (EIB)',
                'email' => 'eib.partner@example.invalid',
                'contact' => 'European Investment Bank Partner Contact',
            ],
            [
                'name' => 'Export-Import Bank of India',
                'email' => 'exim-india.partner@example.invalid',
                'contact' => 'Export-Import Bank of India Partner Contact',
            ],
            [
                'name' => 'African Export-Import Bank (Roaring Lion Fund)',
                'email' => 'roaring-lion-fund.partner@example.invalid',
                'contact' => 'Roaring Lion Fund Partner Contact',
            ],
        ];

        foreach ($partners as $partner) {
            $user = User::firstOrNew(['email' => $partner['email']]);
            $isNew = ! $user->exists;

            $user->fill([
                'name' => $partner['contact'],
                'user_type' => 'funding_partner',
                'role_id' => $role->id,
            ]);

            if ($isNew) {
                $password = $this->randomCreationPassword();
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

            if ($isNew) {
                $escapedPassword = OutputFormatter::escape($password);
                $this->command?->warn(
                    "Created funding partner {$partner['email']}. Temporary password (shown once): {$escapedPassword}"
                );
                $this->command?->warn(
                    'A password change is required at first login; later seeder runs will preserve the current password.'
                );
            } else {
                $this->command?->line(
                    "Existing funding partner {$partner['email']} kept its password, password-change status, and account restrictions."
                );
            }

            Funder::updateOrCreate(
                ['contact_email' => $partner['email']],
                [
                    'name' => $partner['name'],
                    'type' => 'donor',
                    'currency' => 'USD',
                    'has_portal_access' => true,
                    'user_id' => $user->id,
                    'contact_person' => $partner['contact'],
                    'contact_email' => $partner['email'],
                    'contact_phone' => null,
                    'notes' => 'Seeded funding partner with portal access.',
                ]
            );
        }
    }

    private function randomCreationPassword(): string
    {
        if (! isset($this->command)) {
            throw new RuntimeException(
                'Funding partner provisioning requires an interactive seeder command so each random temporary password can be shown once.'
            );
        }

        return Str::password(24);
    }

    private function worldBankPartnerEmail(): string
    {
        $configuredEmail = trim((string) env('WORLD_BANK_PARTNER_EMAIL'));
        $email = $configuredEmail !== '' ? $configuredEmail : WorldBankPartnerAccessSeeder::EMAIL;

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('WORLD_BANK_PARTNER_EMAIL must contain a valid email address.');
        }

        return $email;
    }
}

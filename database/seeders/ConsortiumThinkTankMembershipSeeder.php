<?php

namespace Database\Seeders;

use App\Models\Consortium;
use App\Models\ConsortiumThinkTank;
use App\Models\Funder;
use App\Models\ProgramFunding;
use App\Models\Role;
use App\Models\User;
use App\Models\VendorCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatter;

class ConsortiumThinkTankMembershipSeeder extends Seeder
{
    private const PROVISION_ACCESS_ENV = 'CONSORTIUM_BOOTSTRAP_ACCESS_ACCOUNTS';

    public function run(): void
    {
        $provisionAccessAccounts = $this->shouldProvisionAccessAccounts();

        VendorCategory::firstOrCreate(
            ['name' => 'Think Tank Transfer'],
            [
                'description' => 'Think tank vendor accounts used for consortium fund transfer purchase orders and disbursements.',
                'is_active' => true,
            ]
        );

        $thinkTankRole = Role::where('name', 'Think Tank User')->first();
        $worldBank = Funder::where('name', 'World Bank')
            ->orWhere('name', 'World Bank Group')
            ->orWhere('contact_email', WorldBankPartnerAccessSeeder::EMAIL)
            ->first();
        $worldBankFunding = $worldBank
            ? ProgramFunding::where('funder_id', $worldBank->id)
                ->where('status', 'approved')
                ->orderByDesc('approved_amount')
                ->first()
            : null;

        $consortia = [
            [
                'code' => 'RAISED-AFRICA',
                'name' => 'RAISED Africa',
                'members' => [
                    ['name' => 'Economic Research Forum', 'country' => 'Egypt'],
                    ['name' => 'Partnership for Economic Policy (PEP)', 'country' => 'Kenya'],
                    ['name' => 'Resource and Environmental Policy Research Centre (REPRC), Environment for Development (EfD) Nigeria', 'country' => 'Nigeria'],
                ],
            ],
            [
                'code' => 'CACEPS',
                'name' => 'CACEPS Consortium',
                'members' => [
                    ['name' => 'African Population and Health Research Center (APHRC)', 'country' => 'Kenya'],
                    ['name' => 'Centro de Integridade Publica', 'country' => 'Mozambique'],
                    ['name' => 'Centre for Population and Environmental Development (CPED)', 'country' => 'Nigeria'],
                    ['name' => 'The Egyptian Center for Economic Studies (ECES)', 'country' => 'Egypt'],
                    ['name' => 'Initiative Prospective Agricole et Rurale', 'country' => 'Senegal'],
                ],
            ],
            [
                'code' => 'BRIDGE-AFRICA',
                'name' => 'Bridge Africa Consortium',
                'members' => [
                    ['name' => 'African Center for Economic Transformation (ACET)', 'country' => 'Ghana'],
                    ['name' => 'African Institute for Development Policy (AFIDEP)', 'country' => 'Kenya'],
                    ['name' => 'Denis and Lenora Foretia Foundation (Nkafu Policy Institute)', 'country' => 'Cameroon'],
                    ['name' => 'Policy Center for the New South (PCNS)', 'country' => 'Morocco'],
                    ['name' => 'South Africa Institute of International Affairs (SAIIA)', 'country' => 'South Africa'],
                ],
            ],
        ];

        foreach ($consortia as $consortiumData) {
            $consortium = Consortium::updateOrCreate(
                ['code' => $consortiumData['code']],
                [
                    'name' => $consortiumData['name'],
                    'currency' => 'USD',
                    'status' => 'active',
                    'funder_id' => $worldBank?->id,
                    'program_funding_id' => $worldBankFunding?->id,
                    'approved_budget' => $worldBankFunding?->approved_amount ?? 0,
                    'notes' => 'Seeded ATTP think tank consortium membership.',
                ]
            );

            foreach ($consortiumData['members'] as $memberData) {
                $portalUser = $this->resolveAccessAccount(
                    $this->portalEmailFor($memberData['name']),
                    [
                        'name' => $memberData['name'],
                        'user_type' => 'think_tank',
                        'role_id' => $thinkTankRole?->id,
                    ],
                    $provisionAccessAccounts
                );

                $vendor = $this->resolveAccessAccount(
                    $this->vendorEmailFor($memberData['name']),
                    [
                        'name' => $memberData['name'],
                        'user_type' => 'vendor',
                        'vendor_category' => 'Think Tank Transfer',
                    ],
                    $provisionAccessAccounts
                );

                $membershipAttributes = [
                    'country' => $memberData['country'],
                    'email' => $this->emailFor($memberData['name']),
                    'role' => 'member',
                    'status' => 'active',
                    'joined_at' => now()->toDateString(),
                ];

                if ($portalUser) {
                    $membershipAttributes['portal_user_id'] = $portalUser->id;
                }

                if ($vendor) {
                    $membershipAttributes['vendor_user_id'] = $vendor->id;
                }

                $membership = ConsortiumThinkTank::updateOrCreate(
                    [
                        'consortium_id' => $consortium->id,
                        'name' => $memberData['name'],
                    ],
                    $membershipAttributes
                );

                if ($portalUser) {
                    $portalUser->update([
                        'think_tank_member_id' => $membership->id,
                        'think_tank_access_level' => User::THINK_TANK_ACCESS_ADMIN,
                    ]);
                }
            }
        }

        if (! $provisionAccessAccounts) {
            $this->command?->info(
                'Consortium memberships were seeded without creating login accounts. Set '
                .self::PROVISION_ACCESS_ENV
                .'=true and run this seeder explicitly when access accounts are required.'
            );
        }
    }

    /** @param array<string, mixed> $attributes */
    private function resolveAccessAccount(
        string $email,
        array $attributes,
        bool $provisionAccessAccounts
    ): ?User {
        if (! $provisionAccessAccounts) {
            return null;
        }

        $user = User::firstOrNew(['email' => $email]);
        $isNew = ! $user->exists;
        $user->fill($attributes);

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
            $this->command?->warn("Created {$email}. Temporary password (shown once): {$escapedPassword}");
            $this->command?->warn(
                'A password change is required at first login; later seeder runs will preserve the current password.'
            );
        }

        return $user;
    }

    private function randomCreationPassword(): string
    {
        if (! isset($this->command)) {
            throw new RuntimeException(
                'Consortium access account provisioning requires an interactive seeder command so each random temporary password can be shown once.'
            );
        }

        return Str::password(24);
    }

    private function shouldProvisionAccessAccounts(): bool
    {
        $configuredValue = env(self::PROVISION_ACCESS_ENV);

        if ($configuredValue === null) {
            return false;
        }

        $enabled = filter_var($configuredValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($enabled === null) {
            throw new RuntimeException(self::PROVISION_ACCESS_ENV.' must be true or false.');
        }

        return $enabled;
    }

    private function emailFor(string $name): string
    {
        return Str::slug(Str::limit($name, 48, ''), '.').'@thinktank.attp.local';
    }

    private function portalEmailFor(string $name): string
    {
        return 'portal.'.Str::slug(Str::limit($name, 42, ''), '.').'@thinktank.attp.local';
    }

    private function vendorEmailFor(string $name): string
    {
        return 'vendor.'.Str::slug(Str::limit($name, 42, ''), '.').'@thinktank.attp.local';
    }
}

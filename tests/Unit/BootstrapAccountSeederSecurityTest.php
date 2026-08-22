<?php

it('runs deployment super-admin provisioning last while keeping unsafe imports out of ordinary seeding', function () {
    $defaultSeeder = file_get_contents(
        dirname(__DIR__, 2).'/database/seeders/DatabaseSeeder.php'
    );

    expect($defaultSeeder)
        ->not->toContain('LegacySqlDumpSeeder::class')
        ->not->toContain('OldDataSqlImportSeeder::class')
        ->toContain('MasterAdminSeeder::class')
        ->not->toContain('SecondarySuperAdminSeeder::class')
        ->not->toContain('WorldBankPartnerAccessSeeder::class')
        ->not->toContain('ConsortiumThinkTankMembershipSeeder::class')
        ->toContain('KenyaGovernmentSeeder::class');

    expect(strpos($defaultSeeder, 'MasterAdminSeeder::class'))
        ->toBeGreaterThan(strpos($defaultSeeder, 'SystemAdminAccessSeeder::class'))
        ->toBeLessThan(strpos($defaultSeeder, 'KenyaGovernmentSeeder::class'));
});

it('provisions optional direct bootstrap accounts without committed or reusable passwords', function () {
    $root = dirname(__DIR__, 2).'/database/seeders/';
    $seeders = [
        'SecondarySuperAdminSeeder.php' => 'SECONDARY_SUPER_ADMIN_BOOTSTRAP_PASSWORD',
        'WorldBankPartnerAccessSeeder.php' => 'WORLD_BANK_PARTNER_BOOTSTRAP_PASSWORD',
    ];

    foreach ($seeders as $file => $passwordEnvironment) {
        $source = file_get_contents($root.$file);

        expect($source)
            ->toContain('User::firstOrNew(')
            ->toContain('if ($isNew)')
            ->toContain("'must_change_password' => true")
            ->toContain("'password_changed_at' => null")
            ->toContain('Str::password(24)')
            ->toContain($passwordEnvironment)
            ->toContain("\$configuredPassword !== null && \$configuredPassword !== ''")
            ->toContain('later seeder runs will preserve the current password')
            ->not->toContain('User::updateOrCreate(')
            ->not->toContain('public const PASSWORD')
            ->not->toMatch('/Hash::make\(\s*[\'\"]/');
    }
});

it('provisions the approved deployment super admins without publishing the plaintext password', function () {
    $root = dirname(__DIR__, 2);
    $source = file_get_contents($root.'/database/seeders/MasterAdminSeeder.php');
    $configSource = file_get_contents($root.'/config/super-admins.php');
    $config = require $root.'/config/super-admins.php';
    $emails = array_column($config['accounts'], 'email');

    expect($emails)
        ->toBe([
            'stephendonkor003@gmail.com',
            'nzadon123@proton.me',
            'tchirwa@gmail.com',
            'info@3pap.africa',
        ])
        ->and(array_unique($emails))->toHaveCount(4)
        ->and(password_get_info($config['bootstrap_password_hash'])['algoName'])->toBe('bcrypt')
        ->and($configSource)
        ->toContain("'bootstrap_password' => env('MASTER_ADMIN_BOOTSTRAP_PASSWORD')")
        ->toContain("'synchronize_password_on_seed' =>")
        ->toContain("'force_password_change' =>")
        ->not->toMatch("/'bootstrap_password'\\s*=>\\s*['\"][^'\"]+['\"]/i")
        ->and($source)
        ->toContain("config('super-admins.email_override'")
        ->toContain("config('super-admins.bootstrap_password'")
        ->toContain("'must_change_password' => \$forcePasswordChange")
        ->toContain("'is_disabled' => false")
        ->toContain("'is_blacklisted' => false")
        ->toContain('if ($isNew)')
        ->toContain('User::firstOrNew(')
        ->not->toContain('User::updateOrCreate(')
        ->not->toMatch('/Hash::make\(\s*[\'\"]/');

    $newAccountBlock = substr(
        $source,
        strpos($source, 'if ($isNew) {'),
        strpos($source, '} elseif (! $admin->email_verified_at)', strpos($source, 'if ($isNew) {'))
            - strpos($source, 'if ($isNew) {')
    );

    expect($newAccountBlock)
        ->toContain("'is_disabled' => false")
        ->toContain("'disabled_at' => null")
        ->toContain("'disabled_reason' => null")
        ->toContain("'is_blacklisted' => false")
        ->toContain("'blacklisted_at' => null")
        ->toContain("'blacklisted_reason' => null");
});

it('uses configurable example identities for optional direct bootstrap accounts', function () {
    $root = dirname(__DIR__, 2).'/database/seeders/';
    $secondary = file_get_contents($root.'SecondarySuperAdminSeeder.php');
    $partner = file_get_contents($root.'WorldBankPartnerAccessSeeder.php');

    expect($secondary)
        ->toContain("public const EMAIL = 'secondary.system.admin@example.invalid'")
        ->toContain("public const NAME = 'Secondary System Administrator'")
        ->toContain('SECONDARY_SUPER_ADMIN_EMAIL')
        ->toContain('SECONDARY_SUPER_ADMIN_NAME')
        ->and($partner)
        ->toContain("public const EMAIL = 'world-bank.partner@example.invalid'")
        ->toContain('WORLD_BANK_PARTNER_EMAIL');

    foreach ([$secondary, $partner] as $source) {
        expect($source)->not->toMatch('/@[a-z0-9.-]+\.(?:com|org)\b/i');
    }

    $seederFileNameHashes = array_map(
        fn (string $file): string => hash('sha256', strtolower(basename($file))),
        glob($root.'*.php')
    );

    expect($seederFileNameHashes)
        ->not->toContain('6c2d2d5bdcfe06fbd9f3cf0403193f057c40a83d9380a4d8661fb2860f89afb2')
        ->and(file_exists($root.'SecondarySuperAdminSeeder.php'))->toBeTrue();
});

it('keeps reusable password literals and user password upserts out of every PHP seeder', function () {
    $seederFiles = glob(dirname(__DIR__, 2).'/database/seeders/*.php');

    expect($seederFiles)->not->toBeFalse()->not->toBeEmpty();

    foreach ($seederFiles as $seederFile) {
        $source = file_get_contents($seederFile);

        expect($source)
            ->not->toMatch('/Hash::make\(\s*[\'\"]/')
            ->not->toMatch('/[\'\"]password[\'\"]\s*=>\s*[\'\"]/')
            ->not->toMatch('/\$2[ay]\$/')
            ->not->toMatch('/User::(?:query\(\)->)?updateOrCreate\(/');
    }
});

it('uses creation-only random credentials in every data or fixture user seeder', function () {
    $root = dirname(__DIR__, 2).'/database/seeders/';
    $creationSeeders = [
        'FundingPartnerSeeder.php',
        'UserSeeder.php',
        'LegacyThinkTankProcurementLifecycleSeeder.php',
        'OldDataSqlImportSeeder.php',
    ];

    foreach ($creationSeeders as $file) {
        $source = file_get_contents($root.$file);

        expect($source)
            ->toContain('Str::password(24)')
            ->toMatch("/(?:'must_change_password' => true|\\['must_change_password'\\] = true)/")
            ->toMatch("/(?:'password_changed_at' => null|\\['password_changed_at'\\] = null)/")
            ->toContain('Temporary password (shown once)')
            ->toContain('will preserve the current password')
            ->not->toMatch('/Hash::make\(\s*[\'\"]/')
            ->not->toMatch('/\$2[ay]\$/');
    }

    expect(file_get_contents($root.'FundingPartnerSeeder.php'))
        ->toContain('User::firstOrNew(')
        ->toContain('if ($isNew)')
        ->and(file_get_contents($root.'UserSeeder.php'))
        ->toContain('User::firstOrNew(')
        ->toContain('if ($isNew)')
        ->and(file_get_contents($root.'LegacyThinkTankProcurementLifecycleSeeder.php'))
        ->toContain('if ($existingId)')
        ->toContain("DB::table('users')->insert(")
        ->and(file_get_contents($root.'OldDataSqlImportSeeder.php'))
        ->toContain('upsertUserWithoutReusingLegacyCredentials')
        ->toContain('if ($existingId)');
});

it('seeds consortium membership data without silently creating login accounts', function () {
    $source = file_get_contents(
        dirname(__DIR__, 2).'/database/seeders/ConsortiumThinkTankMembershipSeeder.php'
    );

    expect($source)
        ->toContain("private const PROVISION_ACCESS_ENV = 'CONSORTIUM_BOOTSTRAP_ACCESS_ACCOUNTS'")
        ->toContain('if (! $provisionAccessAccounts)')
        ->toContain('if ($isNew)')
        ->toContain("'must_change_password' => true")
        ->toContain("'password_changed_at' => null")
        ->toContain('Str::password(24)')
        ->toContain('Temporary password (shown once)')
        ->toContain('without creating login accounts')
        ->not->toContain('User::updateOrCreate(')
        ->not->toMatch('/Hash::make\(\s*[\'\"]/');
});

it('keeps personal bootstrap, survey, and focal-contact identities out of PHP fixtures', function () {
    $root = dirname(__DIR__, 2).'/database/seeders/';
    $seederFiles = glob($root.'*.php');
    // Exact retired identity phrases are hashed so this regression guard does not recommit the PII it rejects.
    $retiredIdentityPhraseHashes = array_fill_keys([
        'b62ebfbbf18e3147b97979b60f3927ae7aaace7d5b1927ac0fcbc21799af930d',
        '7a1f016082aa8f7375222dda4363d90f03c3be463ea0dfb921002057b70f4660',
        'b429ff5726372dedf32de2a03f7c7668519fc939c1801089ed83ee9108c9817d',
        '82c3fc7535471882c7dd4214f0d035020990a948acabe06e34669ff4cb1b4261',
        'dcbebb720760265ab8867890a4d018aab73d833f3552ef069b0e3d4cb4cb46c6',
        '3e51c6dabafc5f581ec08dcdce296af1a2576743530624c9e18b1aa88d235e20',
        '9cb89dc01f9c36df9d9ed07d70ee545a757873e7bbb500cedd35d711fca2a12b',
        '725de9e16e888094920dad9b3dd438590e52ca2fbd9cecc499c0becf3e562e63',
        '025a03f95fc16a7784aec339ed068b66f21f61ba51ea4dac396838d23ba05c1c',
        'df349a56d129de8f048591023e9e5096038fd16d7fe4d3c0028ca629aacdcb8b',
        '125a767be9fa3da2aa5b3dad6631debdb86f80ff380356b3f30a5af53b3b1509',
        '3c24b39fe2f31143759be98cfc624619113c3e036ebeae6f3379f3a30fd9f6bc',
        '53ae677e70d889ad42061b297bfe213f2a71f1a169878d872a3a85e7cb7cdd1d',
        '4c5a152e6d4a9f45c5c15085e425df9ba3b85d461607a778bda11734a3781986',
        'f6ea4504878cb50d9dc9164ab7ec1232618797759b30e7c822a5592147e2ebe9',
        '3f31b261edf489747d1730baa875326b213d04b6cbc12f561b0a3b31d607945d',
        '265c7a030f4be77a2324325ef9a2529344186d147d0524d081112c93171dc6cd',
        'a892a2cf363dbee112aded5de8b61f20e9127193552004cca0f480f0cb0c82c0',
        '7c349864e8fc92351247da06ba26c69f656a08201a5887331f702bcdd3f3c46a',
        'b15836b54b962ac79380bccf306a91b0f7829f5e4b8403ad76eb66bb02db9f8e',
        '2804f6ac0e4152226c1d1bf78b2c04a36cebbb721e055ee66cc1755880796c7a',
        '07e22330180cf249cbdf9a16657da9c30664e7021a46b10f414739952dc77d41',
        '4eba97eaf5028c88ef69389e8d1ffcec7420cd173bf6480012a5d87c25cb108c',
        '0a78418fe1dd9ac69e73a6385b20bb6c900ba6acc0ab42bb3189373964b7770d',
        'fe625c8df1bbe052ab0701e5879b74ae39044c5b66eea6e22b6fe5167a246978',
        '7352cf46e4a649df0f02c7eb23d774f18b5ea8171f7e649a357ce4120b45ce51',
        '407d01c30db60b0cba3f73ef031727b859d42382b1e16fd5c2c63ca17b0d5eee',
        '5ca451f78e422e0f9dadbff97b47d63a6e3ae2fb64513f93b80c7b6f4cac93bb',
        '4dc2e7a25169bfc1fe1ca00c69992f02e42d7a2cc6b3834f4519bb0a5540c847',
        'e02b4eb4ee4bc6a8bd6aeeddf36ed234bc48831af742c70c32f455dd3bd7dff3',
        '9c16d366376f4bb28d7751424b398fe635631a1ee81a84ab8118c9f7e7146cab',
        '5858e7bf651c7666de3fc93e07f78096a2a2a447a9499c76332c4a692f61f708',
        '7af95c86e4daf00d1ca83c629897b4e571ab7d778d9564796b5cf2009ba80e3d',
        '7258c32d6d0a83a9dabe75d15a76704ede7a7de746fff51de5b21a1a4a9fc976',
        'e687fcedb42fd322b00c3fa94237de77b4d39e9b3caa55241e23e16162cbbb13',
        '60389ce7a8ba0076ac830542fb475cead31af16cb7d9ac86558319f1c18152bc',
        'da53e2b4138b14c435044a4991aecd990e700939a4413858dc4699b1d6bc1070',
        'd123a97ede828538f7987f80339cde025384e62680bba5c392c4497cd620aeea',
        '1fa557f4aa5fbe8a88a47c360f317533d67a33b49cc94532315f5b314fb464f2',
        'b027eb6444685385286649affef5b2a1be2e58e88c634c4bd31d1c14ed4e7d6b',
        'a303ce140bf5f5719f5f4b7e30d0e493751c2102394b0b80ba7fdf0fff25fda0',
        'c5b2bf13b8b72cd4c83204cb132e6a3eae4b07e2903b907c14563e010462bc28',
        'd4dd5a48377fbe627b65b2fe212f0b44128e3ce58ec0bc85eca858a4e90a201a',
        '43337b160112f1ea9603a2b03d8bcbe3bddd6916ba0201604a40dc096836eead',
        'f85e445652204ef4bf1beaa4d2df56aa3be837d10e2ddfbcc2dd4a318038ee10',
        '74c105c09184a57649dfdb91cee26214c24d344433a58b4a82a395912a5fb9fb',
        '83f33b389cc975ca20c2525205af5c16c3863cc6b412b549976357af027ae1c4',
        '05b2c4f41ec7e1293aa0d53be29a7ce6a946cfc5ba26b70632be8ce714bcb86a',
    ], true);
    $migrationFile = dirname(__DIR__, 2).'/database/migrations/2026_08_02_000001_create_unified_attp_me_tracking.php';
    $manualGeneratorFile = dirname(__DIR__, 2).'/scripts/generate_attp_mel_manual.php';
    $privacySensitivePhpFiles = array_merge($seederFiles, [$migrationFile, $manualGeneratorFile]);

    expect($seederFiles)->not->toBeFalse()->not->toBeEmpty()
        ->and(file_exists($root.'LegacyUserSeeder.php'))->toBeFalse()
        ->and(file_exists($migrationFile))->toBeTrue()
        ->and(file_exists($manualGeneratorFile))->toBeTrue();

    foreach ($privacySensitivePhpFiles as $privacySensitivePhpFile) {
        $source = file_get_contents($privacySensitivePhpFile);
        preg_match_all('/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i', $source, $emailMatches);

        foreach ($emailMatches[0] as $email) {
            expect(str_ends_with(strtolower($email), '.invalid'))
                ->toBeTrue(basename($privacySensitivePhpFile).' contains a deliverable email literal.');
        }

        $words = preg_split('/[^a-z]+/i', strtolower($source), -1, PREG_SPLIT_NO_EMPTY);
        $retiredIdentityFound = false;

        for ($phraseLength = 1; $phraseLength <= 4 && ! $retiredIdentityFound; $phraseLength++) {
            for ($offset = 0; $offset <= count($words) - $phraseLength; $offset++) {
                $phrase = implode(' ', array_slice($words, $offset, $phraseLength));

                if (isset($retiredIdentityPhraseHashes[hash('sha256', $phrase)])) {
                    $retiredIdentityFound = true;
                    break;
                }
            }
        }

        expect($retiredIdentityFound)
            ->toBeFalse(basename($privacySensitivePhpFile).' contains a retired personal identity.');
    }

    $surveySource = file_get_contents($root.'MandEDataUpload2026Seeder.php');
    preg_match_all("/'respondent_name'\\s*=>\\s*'([^']+)'/", $surveySource, $respondentNames);
    preg_match_all("/'respondent_email'\\s*=>\\s*'([^']+)'/", $surveySource, $respondentEmails);
    preg_match_all("/'ip_address'\\s*=>\\s*'([^']+)'/", $surveySource, $respondentIpAddresses);

    expect($respondentNames[1])->toHaveCount(14)
        ->and($respondentEmails[1])->toHaveCount(14)
        ->and($respondentIpAddresses[1])->toHaveCount(14)
        ->and($surveySource)
        ->not->toMatch("/'respondent_phone'\\s*=>\\s*['\"]/")
        ->not->toMatch("/'public_token'\\s*=>\\s*['\"]/")
        ->toContain("'public_token' => Str::random(64)")
        ->toContain("array_diff(\$updateColumns, ['public_token'])")
        ->not->toContain('Mozilla/5.0');

    foreach ($respondentNames[1] as $respondentName) {
        expect($respondentName)->toMatch('/^Survey Respondent \d{3}$/');
    }

    foreach ($respondentEmails[1] as $respondentEmail) {
        expect($respondentEmail)->toMatch('/^survey\.respondent\.\d{3}@example\.invalid$/');
    }

    foreach ($respondentIpAddresses[1] as $index => $respondentIpAddress) {
        expect($respondentIpAddress)->toBe('192.0.2.'.($index + 1));
    }

    $fundingPartnerSource = file_get_contents($root.'FundingPartnerSeeder.php');
    preg_match_all("/'contact'\\s*=>\\s*'([^']+)'/", $fundingPartnerSource, $fundingContacts);

    expect($fundingContacts[1])->toHaveCount(8)
        ->and($fundingPartnerSource)->not->toMatch("/'phone'\\s*=>\\s*['\"]/");

    foreach ($fundingContacts[1] as $fundingContact) {
        expect($fundingContact)->toEndWith('Partner Contact');
    }

    $extractFocalContacts = static function (string $file): array {
        preg_match_all(
            "/^\\s*\\['(?:RAISE AFRICA|CACEPS|BRIDGE)',\\s*'([^']+)',\\s*'([^']+)',\\s*'([^']+)'/m",
            file_get_contents($file),
            $matches,
            PREG_SET_ORDER
        );

        return array_map(
            static fn (array $match): array => [$match[1], $match[2], $match[3]],
            $matches
        );
    };

    $migrationFocalContacts = $extractFocalContacts($migrationFile);
    $manualFocalContacts = $extractFocalContacts($manualGeneratorFile);

    expect($migrationFocalContacts)->toHaveCount(18)
        ->and($manualFocalContacts)->toBe($migrationFocalContacts)
        ->and(array_unique(array_column($migrationFocalContacts, 0)))->toHaveCount(13);

    foreach ($migrationFocalContacts as [$label, $name, $email]) {
        expect($label)->not->toBeEmpty()
            ->and($name)->toMatch('/^[A-Za-z0-9-]+(?: [A-Za-z0-9-]+)* Focal Contact [1-3]$/')
            ->and($email)->toMatch('/^[a-z0-9-]+\.focal\.[1-3]@example\.invalid$/');
    }
});

it('uses role-based creator lookups instead of personal email fallbacks', function () {
    $root = dirname(__DIR__, 2).'/database/seeders/';
    $creatorSeeders = [
        'AttpBudgetStructureSeeder.php',
        'AttpWorkPlan2025Seeder.php',
        'AttpWorkshopPostSurveySeeder.php',
        'MEConfigurationSeeder.php',
        'ProcurementStructureSeeder.php',
        'ProgramPlanSheetSeeder.php',
        'ResourceDataSeeder.php',
        'ThinkDatasetSeeder.php',
    ];

    foreach ($creatorSeeders as $file) {
        expect(file_get_contents($root.$file))
            ->toContain("User::where('user_type', 'admin')->orderBy('id')->value('id')")
            ->not->toMatch("/User::where\\(['\"]email['\"]/i");
    }
});

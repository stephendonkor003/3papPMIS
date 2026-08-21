<?php

it('ships a runnable environment template without a missing frontend toolchain', function () {
    $root = dirname(__DIR__, 2);
    $composer = json_decode(file_get_contents($root.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $guestLayout = file_get_contents($root.'/resources/views/layouts/guest.blade.php');

    expect(is_file($root.'/.env.example'))->toBeTrue()
        ->and(file_get_contents($root.'/.env.example'))
        ->toContain('APP_NAME="3PAP PMIS"')
        ->toContain('DB_CONNECTION=pgsql')
        ->and(implode("\n", (array) ($composer['scripts']['dev'] ?? [])))
        ->not->toContain('npm run')
        ->not->toContain('npx ')
        ->and($guestLayout)
        ->not->toContain('@vite(')
        ->not->toContain('resources/css/app.css')
        ->not->toContain('resources/js/app.js');
});

it('keeps confidential operational data out of the public repository', function () {
    $gitignore = file_get_contents(dirname(__DIR__, 2).'/.gitignore');

    expect($gitignore)
        ->toContain('/resources/Submissions/')
        ->toContain('/docs/ATTP_MEL_AUDIT.md')
        ->toContain('/docs/ATTP_MEL_IMPLEMENTATION.md')
        ->toContain('/docs/ATTP_MEL_Platform_Complete_User_Manual.docx')
        ->toContain('/docs/SERVER_DATABASE_FINANCE_AND_REPORTING_AUDIT_2026-08-02.md')
        ->toContain('/docs/external-procurement-submissions-import.md')
        ->toContain('/docs/user-manuals/')
        ->toContain('/storage/app/backups/')
        ->toContain('/database/seeders/*.sql')
        ->toContain('*.dump');
});

<?php

test('procurement screening only accepts local redirect targets', function () {
    $source = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Procurement/ProcurementSubmissionController.php');

    expect($source)
        ->toContain("Str::startsWith(\$redirectTo, '/')")
        ->toContain("! Str::startsWith(\$redirectTo, '//')")
        ->toContain("preg_match('/[\\x00-\\x1F\\x7F]/', \$redirectTo)")
        ->not->toContain("redirect()->to((string) \$request->input('redirect_to'))");
});

test('database maintenance instructions do not contain a real password', function () {
    $source = file_get_contents(dirname(__DIR__, 2).'/clear_budget_resource_workplan_commands.txt');

    expect($source)
        ->toContain("PGPASSWORD='REPLACE_WITH_DATABASE_PASSWORD'")
        ->not->toMatch("/PGPASSWORD='(?!REPLACE_WITH_DATABASE_PASSWORD')[^']+'/i");
});

test('self service registration cannot be posted directly', function () {
    $root = dirname(__DIR__, 2);
    $routes = file_get_contents($root.'/routes/auth.php');
    $controller = file_get_contents($root.'/app/Http/Controllers/Auth/RegisteredUserController.php');

    expect($routes)->not->toContain("Route::post('register'")
        ->and($controller)
        ->not->toContain('User::create(')
        ->not->toContain('Auth::login(');
});

test('all account password flows share the strong default policy', function () {
    $root = dirname(__DIR__, 2);
    $provider = file_get_contents($root.'/app/Providers/AppServiceProvider.php');
    $security = file_get_contents($root.'/app/Http/Controllers/Auth/SecurityController.php');
    $partner = file_get_contents($root.'/app/Http/Controllers/Partner/PartnerProfileController.php');

    expect($provider)
        ->toContain('Password::min(8)')
        ->toContain('->mixedCase()')
        ->toContain('->numbers()')
        ->toContain('->symbols()')
        ->toContain('->uncompromised()')
        ->and($security)->toContain('Password::defaults()')
        ->and($partner)->toContain('Password::defaults()');
});

test('password changes consistently update security lifecycle fields', function () {
    $root = dirname(__DIR__, 2).'/app/Http/Controllers/';

    foreach ([
        'Auth/PasswordController.php',
        'Auth/NewPasswordController.php',
        'Partner/PartnerProfileController.php',
    ] as $file) {
        $source = file_get_contents($root.$file);

        expect($source)
            ->toContain('must_change_password')
            ->toContain('password_changed_at')
            ->toContain('otp_verified_at')
            ->toContain('remember_token');
    }

    expect(file_get_contents($root.'Auth/SecurityController.php'))
        ->toContain("'remember_token' => Str::random(60)");
});

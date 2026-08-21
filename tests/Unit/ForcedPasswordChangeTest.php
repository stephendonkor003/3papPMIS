<?php

use App\Models\User;

it('honours a forced password change for administrator accounts', function () {
    $admin = new User([
        'user_type' => 'admin',
        'must_change_password' => true,
    ]);

    expect($admin->isSuperAdmin())->toBeTrue()
        ->and($admin->mustChangePassword())->toBeTrue();

    $admin->must_change_password = false;

    expect($admin->mustChangePassword())->toBeFalse();
});

it('checks forced changes before administrator bypasses in login and middleware', function () {
    $root = dirname(__DIR__, 2);
    $controller = file_get_contents($root.'/app/Http/Controllers/Auth/AuthenticatedSessionController.php');
    $middleware = file_get_contents($root.'/app/Http/Middleware/EnsurePasswordNotExpired.php');

    expect(strpos($controller, '$user->mustChangePassword()'))
        ->toBeLessThan(strpos($controller, '$user->isSuperAdmin()'))
        ->and(strpos($middleware, '$user->mustChangePassword()'))
        ->toBeLessThan(strpos($middleware, '$user->isSuperAdmin()'));
});

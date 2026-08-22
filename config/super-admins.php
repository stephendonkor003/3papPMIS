<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Deployment Super Administrators
    |--------------------------------------------------------------------------
    |
    | These identities are part of the application's deployment seed contract.
    | The shared bootstrap password is stored only as a one-way bcrypt hash so
    | the plaintext credential is not published in this public repository.
    |
    */
    'accounts' => [
        ['name' => 'Master Administrator 1', 'email' => 'stephendonkor003@gmail.com'],
        ['name' => 'Master Administrator 2', 'email' => 'nzadon123@proton.me'],
        ['name' => 'Master Administrator 3', 'email' => 'tchirwa@gmail.com'],
        ['name' => '3PAP Master Administrator', 'email' => 'info@3pap.africa'],
    ],

    // Optional comma-separated replacement list for a specific deployment.
    'email_override' => env('MASTER_ADMIN_EMAILS'),

    // Optional plaintext/hash overrides should be supplied by the server secret store.
    'bootstrap_password' => env('MASTER_ADMIN_BOOTSTRAP_PASSWORD'),
    'bootstrap_password_hash' => env('MASTER_ADMIN_BOOTSTRAP_PASSWORD_HASH')
        ?: '$2y$12$o.cxnu2c08hEsqj/lFoicu.hmm8fydTHfKJt1INpDdspfBvJA2Hwi',

    // Initial deployment reconciliation can restore the documented bootstrap
    // access. Disable synchronization after bootstrap to preserve rotations.
    'synchronize_password_on_seed' => (bool) env('MASTER_ADMIN_SYNC_PASSWORD_ON_SEED', true),
    'force_password_change' => (bool) env('MASTER_ADMIN_FORCE_PASSWORD_CHANGE', true),
];

<?php

use App\Mail\ApplicantSubmissionReceived;
use App\Mail\JobApplicationReceived;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$messages = [
    'career application' => (new JobApplicationReceived(
        'Demo Applicant',
        'applicant@example.test'
    ))->render(),
    'public application' => (new ApplicantSubmissionReceived(
        (object) ['name' => 'Demo Applicant'],
        '3PAP-APP-123456',
        'Temporary8!'
    ))->render(),
    'login verification' => view('emails.security.login-otp', [
        'user' => (object) ['name' => 'Demo User'],
        'otpCode' => '123456',
    ])->render(),
    'vendor application' => view('emails.vendor.application-received', [
        'vendor' => (object) ['name' => 'Demo Vendor', 'email' => 'vendor@example.test'],
        'procurement' => (object) ['title' => 'Demo Procurement', 'reference_no' => 'PMIS-001'],
        'submission' => (object) ['procurement_submission_code' => 'SUB-001'],
        'temporaryPassword' => 'Temporary8!',
        'portalUrl' => 'http://127.0.0.1/login',
    ])->render(),
];

foreach ($messages as $name => $html) {
    if (! str_contains($html, '3PAP')) {
        fwrite(STDERR, "The {$name} email is missing 3PAP branding.\n");
        exit(1);
    }

    if (preg_match('/African\s+Union|African\s+Think\s+Tank|\bAUC\b|\bATTP\b/i', $html)) {
        fwrite(STDERR, "The {$name} email contains legacy branding.\n");
        exit(1);
    }
}

if (config('mail.from.address') !== 'info@3pap.com' || config('mail.from.name') !== '3PAP PMIS') {
    fwrite(STDERR, 'The global mail sender is not configured for 3PAP PMIS.'.PHP_EOL);
    exit(1);
}

echo "PUBLIC_BRANDING_MAIL_OK\n";

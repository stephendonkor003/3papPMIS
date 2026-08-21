<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Fluent;

function viewMailReachabilityRoot(): string
{
    return dirname(__DIR__, 2);
}

function viewMailReachabilitySource(string $relativePath): string
{
    return file_get_contents(viewMailReachabilityRoot().'/'.$relativePath);
}

it('provides every registered route view and posts each form to its controller route', function () {
    $root = viewMailReachabilityRoot();
    $views = [
        'resources/views/auth/confirm-password.blade.php',
        'resources/views/auth/verify-email.blade.php',
        'resources/views/finance/partner-requests/index.blade.php',
        'resources/views/finance/partner-requests/show.blade.php',
        'resources/views/system/roles/create.blade.php',
        'resources/views/hr/employees/index.blade.php',
        'resources/views/emails/hr/employee-welcome.blade.php',
    ];

    foreach ($views as $view) {
        expect(is_file($root.'/'.$view))->toBeTrue("Missing view: {$view}");
    }

    expect(viewMailReachabilitySource('resources/views/auth/confirm-password.blade.php'))
        ->toContain("url('/confirm-password')")
        ->toContain('@csrf')
        ->and(viewMailReachabilitySource('resources/views/auth/verify-email.blade.php'))
        ->toContain("route('verification.send')")
        ->toContain('@csrf')
        ->and(viewMailReachabilitySource('resources/views/finance/partner-requests/show.blade.php'))
        ->toContain("route('finance.partner-requests.respond'")
        ->toContain("@can('partner.requests.respond')")
        ->toContain('@csrf')
        ->and(viewMailReachabilitySource('resources/views/system/roles/create.blade.php'))
        ->toContain("route('system.roles.store')")
        ->toContain('@csrf')
        ->and(viewMailReachabilitySource('resources/views/emails/indicator_reminder.blade.php'))
        ->toContain("route('budget.me.indicators.index')");
});

it('ships a Blade template for every literal mailable view reference', function () {
    $root = viewMailReachabilityRoot();
    $missing = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root.'/app/Mail', FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        preg_match_all(
            "/(?:view|markdown)\\(\\s*['\"]([^'\"]+)['\"]/",
            file_get_contents($file->getPathname()),
            $matches
        );

        foreach ($matches[1] as $view) {
            $blade = $root.'/resources/views/'.str_replace('.', '/', $view).'.blade.php';
            if (! is_file($blade)) {
                $missing[] = $view.' referenced by '.$file->getFilename();
            }
        }
    }

    $directMailIterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root.'/app', FilesystemIterator::SKIP_DOTS)
    );

    foreach ($directMailIterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        preg_match_all(
            "/Mail::send\\(\\s*['\"]([^'\"]+)['\"]/",
            file_get_contents($file->getPathname()),
            $matches
        );

        foreach ($matches[1] as $view) {
            $blade = $root.'/resources/views/'.str_replace('.', '/', $view).'.blade.php';
            if (! is_file($blade)) {
                $missing[] = $view.' referenced by '.$file->getFilename();
            }
        }
    }

    expect($missing)->toBe([]);
});

it('renders every added notification template with representative escaped data', function () {
    $root = viewMailReachabilityRoot();
    $app = require $root.'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();

    $data = fn (array $attributes): Fluent => new Fluent($attributes);
    $now = Carbon::parse('2026-08-21 10:00:00');
    $user = $data(['name' => 'Example User', 'email' => 'user@example.test']);
    $procurement = $data([
        'title' => 'Research Services',
        'reference_no' => 'PROC-001',
        'application_end_date' => $now,
    ]);
    $formSubmission = $data([
        'procurement_submission_code' => 'SUB-001',
        'procurement' => $procurement,
        'prescreeningResult' => $data(['final_status' => 'passed']),
    ]);
    $evaluationSubmission = $data([
        'procurement' => $procurement,
        'applicant' => $formSubmission,
        'evaluator' => $user,
        'overall_score' => 86.5,
    ]);
    $partnerRequest = $data([
        'subject' => 'Quarterly report',
        'status' => 'completed',
        'response' => 'The requested report is now available.',
        'requester' => $user,
    ]);
    $communication = $data([
        'subject' => 'Implementation update',
        'channel' => 'portal',
        'communication_date' => $now,
        'status' => 'responded',
        'message' => 'Please review our implementation update.',
        'response_text' => 'The update has been reviewed.',
    ]);
    $question = $data([
        'subject' => 'Reporting schedule',
        'priority' => 'normal',
        'asked_on' => $now,
        'question_text' => 'When is the next report due?',
        'answer_text' => 'The next report is due at quarter end.',
    ]);
    $treaty = $data(['title' => 'Cooperation Treaty', 'reference_code' => 'TR-001']);
    $treatyStatus = $data([
        'signed_service_code' => 'SIGN-0001',
        'ratified_service_code' => 'RAT-0001',
        'signed_service_code_verified_at' => $now,
        'ratified_service_code_verified_at' => $now,
    ]);
    $vendorRequest = $data([
        'user' => $user,
        'subject' => 'Clarification',
        'request_topic' => 'Documents',
        'procurement' => $procurement,
        'status' => 'closed',
        'response' => 'Your clarification has been answered.',
    ]);

    $cases = [
        ['emails.security.password-changed', ['user' => $user], 'Password Changed'],
        ['emails.consortium_report_mail', ['consortium' => $data(['name' => 'Example Consortium', 'code' => 'CON-01'])], 'Consortium Evaluation Report'],
        ['emails.evaluations.assigned', ['evaluator' => $user, 'evaluation' => $data(['name' => 'Technical Review']), 'procurement' => $procurement, 'submission' => $formSubmission], 'New Evaluation Assignment'],
        ['emails.evaluations.completed', ['submission' => $evaluationSubmission, 'overallMax' => 100], 'Evaluation Submitted'],
        ['emails.indicator_reminder', ['indicator' => $data(['name' => 'Policy adoption', 'indicator_code' => 'IND-01', 'responsible_party' => 'Secretariat']), 'user' => $user, 'frequency' => 'Quarterly'], 'Indicator Reporting Reminder'],
        ['emails.hr.employee-welcome', ['name' => 'Example Employee', 'email' => 'employee@example.test', 'password' => 'temporary-value', 'userType' => 'Employee'], 'Welcome to 3PAP PMIS'],
        ['emails.prescreening.assigned', ['assignee' => $user, 'procurement' => $procurement], 'New Prescreening Assignment'],
        ['emails.prescreening.completed', ['submission' => $formSubmission, 'template' => $data(['name' => 'Eligibility Review'])], 'Prescreening Completed'],
        ['emails.partners.welcome', ['funder' => $data(['name' => 'Example Funder', 'contact_person' => 'Partner Contact']), 'user' => $user, 'plainPassword' => 'temporary-value', 'loginUrl' => route('partner.dashboard')], 'Welcome to the Funding Partner Portal'],
        ['emails.partners.request-response', ['request' => $partnerRequest], 'Response to Your Information Request'],
        ['emails.member-state.communication-submitted', ['communication' => $communication, 'memberStateName' => 'Example State', 'reviewUrl' => route('system.communications.index')], 'New Member-State Communication'],
        ['emails.member-state.communication-response', ['communication' => $communication, 'memberStateName' => 'Example State', 'portalUrl' => route('member-state.communications.index')], 'Response to Your Communication'],
        ['emails.member-state.question-submitted', ['question' => $question, 'memberStateName' => 'Example State', 'questionsDeskUrl' => route('system.questions.index')], 'New Member-State Question'],
        ['emails.member-state.question-response', ['question' => $question, 'memberStateName' => 'Example State', 'questionsPortalUrl' => route('member-state.questions.index')], 'Response to Your Question'],
        ['emails.treaties.code-verification-update', ['treaty' => $treaty, 'status' => $treatyStatus, 'codeType' => 'signed', 'codeLabel' => 'Signed', 'memberStateName' => 'Example State', 'actorName' => 'Legal Officer', 'reviewUrl' => route('settings.au.treaties.show', '00000000-0000-0000-0000-000000000001'), 'memberPortalUrl' => route('member-state.treaties.index')], 'Signed Treaty Code Verified'],
        ['emails.treaties.proof-service-codes', ['treaty' => $treaty, 'status' => $treatyStatus, 'actor' => $user, 'memberStateName' => 'Example State', 'isResend' => false, 'reviewUrl' => route('settings.au.treaties.show', '00000000-0000-0000-0000-000000000001')], 'Treaty Proof-of-Service Codes'],
        ['emails.vendor.account-created', ['vendor' => $user, 'plainPassword' => 'temporary-value', 'portalUrl' => route('login')], 'Your Vendor Portal Account'],
        ['emails.vendor.contract-terminated', ['procurement' => $procurement, 'vendor' => $user, 'reason' => 'Contract requirements were not met.', 'portalUrl' => route('login')], 'Contract Termination Notice'],
        ['emails.vendor.disbursement-receipt', ['disbursement' => $data(['vendor' => $user, 'reference_no' => 'RCPT-001', 'currency' => 'USD', 'amount' => 1250, 'payment_method' => 'bank_transfer', 'paid_at' => $now]), 'portalUrl' => route('login')], 'Payment Receipt'],
        ['emails.vendor.procurement-awarded', ['procurement' => $procurement, 'negotiation' => $data(['agreed_amount' => 10000, 'agreed_at' => $now]), 'vendor' => $user, 'portalUrl' => route('login')], 'Procurement Awarded'],
        ['emails.vendor.procurement-invite', ['procurement' => $procurement, 'vendor' => $user, 'message' => 'Please review this opportunity.', 'link' => route('public.procurement.show', 'research-services')], 'New Procurement Opportunity'],
        ['emails.vendor.request-response', ['type' => 'message', 'request' => $vendorRequest, 'subjectLine' => 'Response to Your Clarification Request'], 'Response to Your Clarification Request'],
    ];

    try {
        foreach ($cases as [$view, $payload, $expected]) {
            $html = view($view, $payload)->render();

            expect($html)
                ->toContain('3PAP PMIS')
                ->toContain($expected)
                ->not->toContain('<script');
        }
    } finally {
        restore_error_handler();
        restore_exception_handler();
    }
});

it('consolidates password-change aliases without exposing the changed password', function () {
    $root = viewMailReachabilityRoot();
    $routes = viewMailReachabilitySource('routes/web.php');
    $controller = viewMailReachabilitySource('app/Http/Controllers/Auth/SecurityController.php');
    $mail = viewMailReachabilitySource('app/Mail/Security/PasswordChangedMail.php');

    expect($routes)
        ->toContain("Route::get('/change-password', [SecurityController::class, 'showPasswordChangeForm'])")
        ->toContain("Route::post('/change-password', [SecurityController::class, 'submitPasswordChange'])")
        ->toContain("->name('password.change.form')")
        ->toContain("->name('password.change.update')")
        ->not->toContain('ChangePasswordController')
        ->and(is_file($root.'/app/Http/Controllers/ChangePasswordController.php'))->toBeFalse()
        ->and(is_file($root.'/app/Mail/PasswordChangedNotification.php'))->toBeFalse()
        ->and(is_file($root.'/resources/views/auth/change-password.blade.php'))->toBeFalse()
        ->and($controller)
        ->toContain('new PasswordChangedMail($user)')
        ->not->toContain('new PasswordChangedMail($user,')
        ->and($mail)
        ->toContain("view: 'emails.security.password-changed'")
        ->not->toContain('newPassword')
        ->not->toContain("'password' =>");
});

it('uses canonical reachable views and explicitly retires unreachable legacy actions', function () {
    $routes = viewMailReachabilitySource('routes/web.php');
    $formSubmission = viewMailReachabilitySource('app/Http/Controllers/Procurement/FormSubmissionController.php');
    $project = viewMailReachabilitySource('app/Http/Controllers/ProjectController.php');
    $publicCheck = viewMailReachabilitySource('app/Http/Controllers/PublicCheckController.php');
    $bids = viewMailReachabilitySource('app/Http/Controllers/BidController.php');
    $financial = viewMailReachabilitySource('app/Http/Controllers/FinancialController.php');

    expect($routes)
        ->toContain("Route::get('employees', [HrController::class, 'employees'])")
        ->toContain("->middleware('permission:hr.employees.view')")
        ->toContain("->name('employees.index')")
        ->not->toContain("[FormSubmissionController::class, 'show']")
        ->and($formSubmission)
        ->toContain("route('procurement.submissions.show', \$submission)")
        ->not->toContain("view('procurement.submissions.show'")
        ->and($project)
        ->toContain("view('projects.analytics'")
        ->toContain("'totalAllocations'")
        ->not->toContain("view('budget.projects.analytics'")
        ->and($publicCheck)
        ->toContain('abort(410')
        ->not->toContain("view('public.check.")
        ->and($bids)
        ->toContain("route('public.procurement.index')")
        ->not->toContain("view('bids.")
        ->and($financial)
        ->toContain('The legacy financial evaluation editor has been retired')
        ->not->toContain("view('financial.edit'");
});

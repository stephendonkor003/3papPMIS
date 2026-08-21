<?php

use App\Models\AttpAiGuideSetting;
use App\Models\Role;
use App\Models\User;

function frontendRegressionFile(string $relativePath): string
{
    return file_get_contents(dirname(__DIR__, 2).'/'.$relativePath);
}

it('renders procurement lookup data without HTML interpretation', function () {
    $view = frontendRegressionFile('resources/views/procurement/create.blade.php');

    expect($view)
        ->toContain('detail.textContent = String(value)')
        ->toContain('planResults.replaceChildren()')
        ->toContain('documentTemplate.content.cloneNode(true)')
        ->not->toContain('innerHTML')
        ->not->toContain('insertAdjacentHTML');
});

it('renders site visit members with DOM properties and bound event listeners', function () {
    $view = frontendRegressionFile('resources/views/site-visits/create.blade.php');

    expect($view)
        ->toContain("memberName.textContent = name || ''")
        ->toContain("memberEmail.textContent = email || ''")
        ->toContain("removeButton.addEventListener('click'")
        ->toContain('groupBody.replaceChildren(emptyRow)')
        ->not->toContain('innerHTML')
        ->not->toContain('onclick="removeMember(');
});

it('renders public vacancy descriptions and local file names as text', function () {
    $careers = frontendRegressionFile('resources/views/public/careers/index.blade.php');
    $communications = frontendRegressionFile('resources/views/member-state/communications/index.blade.php');

    expect($careers)
        ->toContain("modalDescription').textContent")
        ->not->toContain("modalDescription').innerHTML")
        ->and($communications)
        ->toContain("document.createTextNode(' ' + file.name)")
        ->not->toContain("chip.innerHTML = '<i class=\"feather-file\"></i> ' + file.name");
});

it('keeps server error messages and formula variable labels out of HTML sinks', function () {
    $evaluations = frontendRegressionFile('resources/views/evaluations/index.blade.php');
    $definitions = frontendRegressionFile('resources/views/me/definitions/form.blade.php');
    $funders = frontendRegressionFile('resources/views/finance/funders/index.blade.php');

    expect($evaluations)
        ->toContain("alert.textContent = error.message")
        ->not->toContain('${error.message')
        ->and($definitions)
        ->toContain("label.textContent = String(v.label ?? '')")
        ->not->toContain('${v.label}</span>')
        ->and($funders)
        ->toContain("alert.textContent = error.message")
        ->not->toContain('${error.message');
});

it('escapes purchase request API data before building deletion previews', function () {
    foreach ([
        'resources/views/finance/purchase-requests/index.blade.php',
        'resources/views/finance/purchase-requests/show.blade.php',
    ] as $path) {
        $view = frontendRegressionFile($path);

        expect($view)
            ->toContain('escapeHtml(s.reference_no)')
            ->toContain('escapeHtml(data.block_reason)')
            ->toContain('escapeHtml(item.reference_no)')
            ->toContain('escapeHtml(item.vendor)')
            ->toContain('extras.map(escapeHtml)');
    }
});

it('submits and normalizes false AI Guide settings', function () {
    $view = frontendRegressionFile('resources/views/system/attp-ai-guide/settings.blade.php');
    $controller = frontendRegressionFile('app/Http/Controllers/AttpAiGuideController.php');

    expect($view)
        ->toContain('<input type="hidden" name="enabled" value="0">')
        ->toContain('<input type="hidden" name="show_to_authenticated_only" value="0">')
        ->toContain('<input type="hidden" name="show_to_guests" value="0">')
        ->toContain("fields.toggleAttribute('inert', !enabled)")
        ->not->toContain('field.disabled = !enabled');

    expect($controller)
        ->toContain("\$validated['enabled'] = \$request->boolean('enabled');")
        ->toContain("\$validated['show_to_authenticated_only'] = \$request->boolean('show_to_authenticated_only');")
        ->toContain("\$validated['show_to_guests'] = \$request->boolean('show_to_guests');")
        ->toContain("\$validated['targeted_user_roles'] ?? []")
        ->toContain("'targeted_user_roles.*' => 'uuid|exists:roles,id'")
        ->not->toContain("array_map('intval'")
        ->toContain('regex:/\\A[A-Za-z0-9_-]+\\z/');
});

it('limits AI Guide visibility through the native user role relationship', function () {
    $targetedRoleId = '77fb2a20-bfc1-4baa-aaf7-70f76041b8ce';
    $otherRoleId = '6a41f32f-ef76-44c5-b658-c621b51eff6a';
    $settings = new AttpAiGuideSetting([
        'enabled' => true,
        'show_to_authenticated_only' => true,
        'targeted_user_roles' => [$targetedRoleId],
    ]);

    $targetedUser = new User;
    $targetedRole = new Role;
    $targetedRole->setAttribute('id', $targetedRoleId);
    $targetedUser->setRelation('role', $targetedRole);

    $otherUser = new User;
    $otherRole = new Role;
    $otherRole->setAttribute('id', $otherRoleId);
    $otherUser->setRelation('role', $otherRole);

    $userWithoutRole = new User;
    $userWithoutRole->setRelation('role', null);

    expect($settings->isAvailableForUser($targetedUser))->toBeTrue()
        ->and($settings->isAvailableForUser($otherUser))->toBeFalse()
        ->and($settings->isAvailableForUser($userWithoutRole))->toBeFalse();
});

it('loads each admin lifecycle once and uses stored Tawk identifiers safely', function () {
    $layout = frontendRegressionFile('resources/views/layouts/app.blade.php');
    $component = frontendRegressionFile('resources/views/components/tawk-widget.blade.php');

    preg_match('/@if \(\$useLeanAdminScripts\).*?@else(?<scripts>.*?)@endif/s', $layout, $matches);
    $standardScripts = $matches['scripts'] ?? '';

    expect(substr_count($standardScripts, "asset('admin/assets/vendors/js/vendors.min.js')"))->toBe(1)
        ->and(substr_count($standardScripts, "asset('admin/assets/js/common-init.min.js')"))->toBe(1)
        ->and(substr_count($standardScripts, "asset('admin/assets/vendors/js/dataTables.min.js')"))->toBe(1)
        ->and(substr_count($standardScripts, "asset('admin/assets/vendors/js/select2.min.js')"))->toBe(1);

    expect($layout)
        ->toContain(':property-id="$aiGuideSettings->tawk_property_id"')
        ->toContain(':widget-id="$aiGuideSettings->tawk_widget_id"')
        ->not->toContain('https://code.jquery.com/jquery-3.6.0.min.js')
        ->not->toContain('https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js')
        ->not->toContain('69204852eba156195f5dae48/1jaj1trqo');

    expect($component)
        ->toContain("preg_match('/\\A[A-Za-z0-9_-]+\\z/'")
        ->toContain("'https://embed.tawk.to/' . rawurlencode(\$propertyId)")
        ->toContain('Illuminate\\Support\\Js::from($embedUrl)')
        ->toContain('document.getElementById(scriptId)');
});

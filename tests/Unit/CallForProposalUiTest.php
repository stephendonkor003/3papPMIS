<?php

it('renders closed opportunities through the shared 3PAP PMIS public experience', function () {
    $root = dirname(__DIR__, 2);
    $view = file_get_contents($root.'/resources/views/applicants/create.blade.php');
    $layout = file_get_contents($root.'/resources/views/layouts/public.blade.php');
    $header = file_get_contents($root.'/resources/views/partials/public-marketing-header.blade.php');

    expect($view)
        ->toContain("@extends('layouts.public')")
        ->toContain('Opportunities | 3PAP PMIS')
        ->toContain('Online submissions are not available')
        ->toContain('There are no open calls at this time')
        ->toContain('What happens when an opportunity opens')
        ->not->toContain('<form')
        ->not->toContain("route('applicants.store')")
        ->not->toContain('name="focus_areas[]"')
        ->not->toContain('name="covered_countries[]"')
        ->not->toContain('ATTP')
        ->not->toContain('African Union')
        ->not->toContain('code.jquery.com')
        ->not->toContain('select2')
        ->not->toContain('cdn.jsdelivr.net')
        ->not->toContain('â€™')
        ->not->toContain('ðŸ');

    expect($layout)
        ->toContain("@include('partials.public-marketing-header')")
        ->toContain('3PAP PMIS');

    expect($header)
        ->toContain("request()->routeIs('public.grievances.*') ? 'active' : ''")
        ->toContain("__('public.brand_subtitle')");
});

it('does not load the think tank directory while submissions are closed', function () {
    $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/ApplicantController.php');

    expect($controller)
        ->toContain('$submissionsOpen = self::CALL_FOR_PROPOSAL_SUBMISSIONS_OPEN;')
        ->toContain('$thinkTanks = $submissionsOpen')
        ->toContain(': collect();');
});

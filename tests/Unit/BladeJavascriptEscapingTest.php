<?php

use Illuminate\Support\Js;

it('encodes database-backed chart and table values for script contexts', function () {
    $payload = '</script><script>alert("xss")</script>';
    $encoded = (string) Js::from([$payload]);

    expect($encoded)
        ->not->toContain('</script>')
        ->toContain('\\u003C');

    $root = dirname(__DIR__, 2).'/resources/views/';
    $views = [
        file_get_contents($root.'components/data-table.blade.php'),
        file_get_contents($root.'projects/analytics.blade.php'),
        file_get_contents($root.'reports/index.blade.php'),
        file_get_contents($root.'finance/governance/index.blade.php'),
    ];

    foreach ($views as $view) {
        expect($view)
            ->toContain('Illuminate\\Support\\Js::from')
            ->not->toContain('{!! json_encode(');
    }

    expect(substr_count($views[2], 'https://cdn.jsdelivr.net/npm/chart.js'))->toBe(1);

    expect($views[3])
        ->toContain('escapeHtml(n.name)')
        ->toContain('escapeHtml(n.level)')
        ->toContain('escapeHtml(d.type)');
});

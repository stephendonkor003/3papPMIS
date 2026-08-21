<?php

use App\Support\NewsHtmlSanitizer;

it('removes executable markup and unsafe URL schemes from news HTML', function () {
    $sanitized = (new NewsHtmlSanitizer())->sanitize(<<<'HTML'
        <p onclick="alert(1)">Safe text</p>
        <a href=javascript:alert(2)>Unsafe link</a>
        <img src="x" onerror="alert(3)">
        <svg><script>alert(4)</script></svg>
        <script>alert(5)</script>
        HTML);

    expect($sanitized)
        ->toContain('<p>Safe text</p>')
        ->toContain('Unsafe link')
        ->not->toContain('onclick')
        ->not->toContain('javascript:')
        ->not->toContain('onerror')
        ->not->toContain('<script')
        ->not->toContain('<svg');
});

it('preserves safe editorial formatting and hardens links', function () {
    $sanitized = (new NewsHtmlSanitizer())->sanitize(
        '<h2>Heading</h2><p><strong>Important</strong> update.</p><a href="/news">Read more</a>'
    );

    expect($sanitized)
        ->toContain('<h2>Heading</h2>')
        ->toContain('<strong>Important</strong>')
        ->toContain('href="/news"')
        ->toContain('rel="noopener noreferrer"');
});

it('accepts article bodies above the component default length', function () {
    $body = '<p>'.str_repeat('news ', 6_000).'</p>';

    expect((new NewsHtmlSanitizer())->sanitize($body))->toContain(str_repeat('news ', 5_000));
});

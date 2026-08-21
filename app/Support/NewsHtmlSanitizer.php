<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class NewsHtmlSanitizer
{
    private HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $config = (new HtmlSanitizerConfig())
            ->allowSafeElements()
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowRelativeLinks()
            ->allowMediaSchemes(['https'])
            ->allowRelativeMedias()
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->withMaxInputLength(2_000_000);

        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function sanitize(?string $html): string
    {
        return trim($this->sanitizer->sanitize((string) $html));
    }
}

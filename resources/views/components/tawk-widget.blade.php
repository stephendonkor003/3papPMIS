@props([
    'propertyId' => null,
    'widgetId' => null,
])

@php
    $propertyId = trim((string) $propertyId);
    $widgetId = trim((string) $widgetId);
    $isSafePathSegment = static fn (string $value): bool =>
        $value !== '' && preg_match('/\A[A-Za-z0-9_-]+\z/', $value) === 1;
    $embedUrl = $isSafePathSegment($propertyId) && $isSafePathSegment($widgetId)
        ? 'https://embed.tawk.to/' . rawurlencode($propertyId) . '/' . rawurlencode($widgetId)
        : null;
@endphp

@if ($embedUrl)
    <script type="text/javascript">
        (() => {
            const scriptId = 'attp-ai-guide-tawk-script';
            if (document.getElementById(scriptId)) {
                return;
            }

            window.Tawk_API = window.Tawk_API || {};
            window.Tawk_LoadStart = new Date();

            const tawkScript = document.createElement('script');
            tawkScript.id = scriptId;
            tawkScript.async = true;
            tawkScript.src = {{ Illuminate\Support\Js::from($embedUrl) }};
            tawkScript.charset = 'UTF-8';
            tawkScript.setAttribute('crossorigin', '*');
            document.head.appendChild(tawkScript);
        })();
    </script>
@endif

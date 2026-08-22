<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    /**
     * Switch the application language.
     */
    public function switch(Request $request, string $locale): JsonResponse|RedirectResponse
    {
        // Validate locale
        if (! in_array($locale, $this->availableLocales(), true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('public.language.invalid'),
                ], 400);
            }

            return redirect()->to($this->safeReturnUrl($request))
                ->with('error', __('public.language.invalid'));
        }

        // Set the application locale
        App::setLocale($locale);

        // Store in session
        $request->session()->put('locale', $locale);

        // Set cookie for 30 days with explicit security attributes.
        $this->queuePreferredLocaleCookie($locale);

        // Return appropriate response
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'locale' => $locale,
                'message' => __('public.language.changed', [
                    'language' => $this->getLanguageName($locale),
                ]),
            ]);
        }

        return redirect()->to($this->safeReturnUrl($request, $locale))
            ->with('success', __('public.language.changed', [
                'language' => $this->getLanguageName($locale),
            ]));
    }

    /**
     * Get the full language name from locale code.
     */
    protected function getLanguageName(string $locale): string
    {
        return (string) config("localization.supported.{$locale}.name", $locale);
    }

    protected function queuePreferredLocaleCookie(string $locale): void
    {
        Cookie::queue(cookie()->make(
            'preferred_locale',
            $locale,
            43200,
            config('session.path', '/'),
            config('session.domain'),
            (bool) config('session.secure', false),
            true,
            false,
            config('session.same_site', 'lax')
        ));
    }

    /**
     * Return only to the current application origin and keep an existing
     * language query parameter synchronized with the newly selected locale.
     */
    protected function safeReturnUrl(Request $request, ?string $locale = null): string
    {
        $fallback = route('landing.index');
        $referer = $request->headers->get('referer');

        if (! is_string($referer) || $referer === '') {
            return $fallback;
        }

        $parts = parse_url($referer);

        if (! is_array($parts)
            || ! isset($parts['scheme'], $parts['host'])
            || strtolower($parts['scheme']) !== strtolower($request->getScheme())
            || strtolower($parts['host']) !== strtolower($request->getHost())
            || $this->normalizedPort($parts['scheme'], $parts['port'] ?? null) !== $request->getPort()) {
            return $fallback;
        }

        $path = $parts['path'] ?? '/';

        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return $fallback;
        }

        parse_str($parts['query'] ?? '', $query);

        if ($locale === null) {
            unset($query['lang']);
        } else {
            $query['lang'] = $locale;
        }

        $queryString = http_build_query($query);

        return $path.($queryString === '' ? '' : '?'.$queryString);
    }

    protected function normalizedPort(string $scheme, ?int $port): int
    {
        return $port ?? (strtolower($scheme) === 'https' ? 443 : 80);
    }

    /**
     * Get the current locale.
     */
    public function current(): JsonResponse
    {
        return response()->json([
            'locale' => App::getLocale(),
            'name' => $this->getLanguageName(App::getLocale()),
        ]);
    }

    /**
     * Get all available locales.
     */
    public function available(): JsonResponse
    {
        $locales = [];

        foreach ($this->availableLocales() as $locale) {
            $locales[] = [
                'code' => $locale,
                'name' => $this->getLanguageName($locale),
            ];
        }

        return response()->json([
            'locales' => $locales,
        ]);
    }

    /**
     * @return list<string>
     */
    protected function availableLocales(): array
    {
        return array_keys((array) config('localization.supported', []));
    }
}

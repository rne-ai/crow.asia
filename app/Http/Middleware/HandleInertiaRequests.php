<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        // 1. Resolve Active Locale with validation against active supported locales
        $routeLocale = $request->route('locale');
        $queryLocale = $request->query('locale');
        $cookieLocale = $request->cookie('crow:locale');
        $candidateLocale = $routeLocale ?: ($queryLocale ?: ($cookieLocale ?: 'id'));

        static $activeLocaleCodes = null;
        if ($activeLocaleCodes === null) {
            try {
                $activeLocaleCodes = \App\Models\SupportedLocale::active()->pluck('code')->all();
            } catch (\Throwable) {
                $activeLocaleCodes = ['id', 'en'];
            }
            if (empty($activeLocaleCodes)) {
                $activeLocaleCodes = ['id', 'en'];
            }
        }

        $locale = in_array($candidateLocale, $activeLocaleCodes, true) ? $candidateLocale : 'id';

        // 2. Resolve Currency: 'id' -> IDR, all other Pan-Asian/international locales -> USD
        $currency = ($locale === 'id') ? 'IDR' : 'USD';

        // 3. Load Translations with static memoization
        static $cachedTranslations = [];
        if (!isset($cachedTranslations[$locale])) {
            $idPath = base_path('lang/id.json');
            $baseTranslations = file_exists($idPath) ? (json_decode(file_get_contents($idPath), true) ?: []) : [];

            $translations = $baseTranslations;
            if ($locale !== 'id') {
                $localePath = base_path("lang/{$locale}.json");
                if (file_exists($localePath)) {
                    $localeTranslations = json_decode(file_get_contents($localePath), true) ?: [];
                    $translations = array_replace_recursive($baseTranslations, $localeTranslations);
                }
            }
            $cachedTranslations[$locale] = $translations;
        }

        $translations = $cachedTranslations[$locale];

        return [
            ...parent::share($request),
            'locale' => $locale,
            'currency' => $currency,
            'translations' => $translations,
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ] : null,
            ],
        ];
    }
}

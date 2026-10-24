<?php

namespace App\Http\Controllers;

use App\Enums\ContentRegister;
use App\Models\Book;
use App\Models\PricingTier;
use App\Models\SupportedLocale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function index(Request $request): Response
    {
        $supportedLocales = SupportedLocale::active()->get()->map(fn ($l) => [
            'code' => $l->code,
            'name' => $l->name,
            'nativeName' => $l->native_name,
            'flagEmoji' => $l->flag_emoji,
            'isLlmTranslated' => $l->is_llm_translated,
        ])->all();

        $activeCodes = array_column($supportedLocales, 'code');
        $locale = $request->query('locale', $request->cookie('crow:locale', 'id'));
        if (!in_array($locale, $activeCodes, true)) {
            $locale = 'id';
        }

        if ($locale === 'id') {
            $regParam = $request->query('reg', $request->cookie('crow:id-register', 'casual'));
            $register = ($regParam === 'standard') ? ContentRegister::STANDARD : ContentRegister::CASUAL;
        } else {
            $register = ContentRegister::STANDARD;
        }

        $books = Book::active()->get()->map(fn ($b) => [
            'id' => $b->id,
            'volumeNumber' => $b->volume_number,
            'volumeCode' => $b->volume_code,
            'title' => $b->title($locale, $register),
            'subtitle' => $b->subtitle($locale, $register),
            'accentHex' => $b->accent_hex,
            'priceSingleIdr' => $b->price_single_idr,
            'priceSingleUsd' => (float) $b->price_single_usd,
            'targetRoles' => $b->target_roles,
            'keyFrameworks' => $b->key_frameworks,
        ])->all();

        $pricingTiers = PricingTier::active()->get()->map(fn ($t) => [
            'id' => $t->id,
            'name' => $t->name($locale),
            'description' => $t->description($locale),
            'features' => $t->features,
            'badges' => $t->badges,
            'priceIdr' => $t->price_idr,
            'strikePriceIdr' => $t->strike_price_idr,
            'priceUsd' => (float) $t->price_usd,
            'includedBookIds' => $t->included_book_ids,
            'isBestValue' => $t->is_best_value,
        ])->all();

        /** @var \App\Models\User|null $user */
        $user = $request->user();

        return Inertia::render('Landing', [
            'locale' => $locale,
            'register' => $register->value,
            'supportedLocales' => $supportedLocales,
            'books' => $books,
            'pricingTiers' => $pricingTiers,
            'user' => $user ? [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ] : null,
        ]);
    }
}

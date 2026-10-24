<?php

use App\Models\Book;
use App\Models\PricingTier;
use App\Models\SupportedLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    SupportedLocale::firstOrCreate(
        ['code' => 'en'],
        ['name' => 'English', 'native_name' => 'English', 'flag_emoji' => '🌐', 'has_dual_register' => false, 'is_active' => true]
    );

    SupportedLocale::firstOrCreate(
        ['code' => 'id'],
        ['name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'flag_emoji' => '🇮🇩', 'has_dual_register' => true, 'is_active' => true]
    );

    Book::firstOrCreate(
        ['id' => 'book-1'],
        [
            'volume_number' => 1,
            'volume_code' => 'VOL. 01',
            'price_single_idr' => 149000,
            'price_single_usd' => 9.99,
            'accent_hex' => '#1D4ED8',
            'titles' => ['id:casual' => 'Mahir Memimpin Tim', 'en:standard' => 'Mastering Team Leadership'],
            'subtitles' => ['id:casual' => 'Panduan Taktis', 'en:standard' => 'Tactical Guide'],
            'target_roles' => ['Lead'],
            'key_frameworks' => ['3K'],
            'pdf_r2_keys' => ['a5' => 'pdf/v1.pdf'],
            'is_active' => true,
        ]
    );

    PricingTier::firstOrCreate(
        ['id' => 'tier-book-1'],
        [
            'names' => ['id' => 'VOL. 01: Mahir Memimpin Tim', 'en' => 'VOL. 01: Mastering Team Leadership'],
            'descriptions' => ['id' => 'Bikin Tim Mandiri', 'en' => 'Transition to High-Impact Lead'],
            'features' => [
                'id' => ['15 Bab Lengkap', 'Mode Animasi 16:9'],
                'en' => ['15 Complete Chapters', '16:9 Animation Canvas'],
            ],
            'price_idr' => 149000,
            'price_usd' => 9.99,
            'included_book_ids' => ['book-1'],
            'is_best_value' => false,
            'is_active' => true,
        ]
    );
});

test('landing page renders english copy and USD pricing when locale is en', function () {
    $response = $this->get('/?locale=en');
    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Landing')
        ->where('locale', 'en')
        ->where('currency', 'USD')
        ->where('pricingTiers.0.features.0', '15 Complete Chapters')
        ->where('pricingTiers.0.name', 'VOL. 01: Mastering Team Leadership')
    );
});

test('landing page renders indonesian copy and IDR pricing when locale is id', function () {
    $response = $this->get('/?locale=id');
    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Landing')
        ->where('locale', 'id')
        ->where('currency', 'IDR')
        ->where('pricingTiers.0.features.0', '15 Bab Lengkap')
        ->where('pricingTiers.0.name', 'VOL. 01: Mahir Memimpin Tim')
    );
});

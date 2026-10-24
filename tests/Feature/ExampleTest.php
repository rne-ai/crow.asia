<?php

use App\Models\Book;
use App\Models\PricingTier;
use App\Models\SupportedLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
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
            'titles' => ['id:casual' => 'Mahir Memimpin Tim'],
            'subtitles' => ['id:casual' => 'Panduan Taktis'],
            'target_roles' => ['Lead'],
            'key_frameworks' => ['3K'],
            'pdf_r2_keys' => ['a5' => 'pdf/v1.pdf'],
            'is_active' => true,
        ]
    );

    PricingTier::firstOrCreate(
        ['id' => 'tier-book-1'],
        [
            'names' => ['id' => 'VOL. 01'],
            'descriptions' => ['id' => 'Deskripsi'],
            'features' => ['Fitur 1'],
            'price_idr' => 149000,
            'price_usd' => 9.99,
            'included_book_ids' => ['book-1'],
            'is_best_value' => false,
            'is_active' => true,
        ]
    );
});

test('landing page returns successful inertia response with required props', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Landing')
        ->has('books')
        ->has('pricingTiers')
        ->where('locale', 'id')
    );
});

<?php

use App\Models\PricingTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
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

test('checkout page provides localized summary and currency symbol', function () {
    $response = $this->get('/checkout/tier-book-1?locale=en');
    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Checkout')
        ->where('locale', 'en')
        ->where('currency', 'USD')
        ->where('translations.checkout.title', 'Complete Playbook Access')
    );
});

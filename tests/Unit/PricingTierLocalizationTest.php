<?php

use App\Models\PricingTier;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(\Tests\TestCase::class, RefreshDatabase::class);

test('pricing tier returns localized features array based on locale', function () {
    $tier = PricingTier::create([
        'id' => 'test-tier-locale',
        'names' => ['id' => 'Paket Uji', 'en' => 'Test Pack'],
        'descriptions' => ['id' => 'Deskripsi Uji', 'en' => 'Test Description'],
        'features' => [
            'id' => ['15 Bab Lengkap VOL. 01', 'Mode Animasi 16:9', 'Master PDF A5'],
            'en' => ['15 Complete Chapters VOL. 01', '16:9 Animation Canvas', 'A5 Master PDF'],
        ],
        'price_idr' => 149000,
        'price_usd' => 9.99,
        'included_book_ids' => ['book-1'],
        'sort_order' => 1,
    ]);

    expect($tier->features('id'))->toBeArray()->toHaveCount(3);
    expect($tier->features('id')[0])->toBe('15 Bab Lengkap VOL. 01');

    expect($tier->features('en'))->toBeArray()->toHaveCount(3);
    expect($tier->features('en')[0])->toContain('Chapters');

    // Fallback when locale is not defined
    expect($tier->features('ja'))->toBeArray()->toHaveCount(3);
    expect($tier->features('ja')[0])->toBe('15 Bab Lengkap VOL. 01');
});

test('pricing tier handles legacy flat array features gracefully', function () {
    $tier = PricingTier::create([
        'id' => 'test-tier-legacy',
        'names' => ['id' => 'Paket Lama', 'en' => 'Legacy Pack'],
        'descriptions' => ['id' => 'Deskripsi', 'en' => 'Description'],
        'features' => ['Fitur 1', 'Fitur 2'],
        'price_idr' => 149000,
        'price_usd' => 9.99,
        'included_book_ids' => ['book-1'],
        'sort_order' => 2,
    ]);

    expect($tier->features('id'))->toBe(['Fitur 1', 'Fitur 2']);
    expect($tier->features('en'))->toBe(['Fitur 1', 'Fitur 2']);
});

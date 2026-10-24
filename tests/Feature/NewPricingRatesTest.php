<?php

use App\Models\Book;
use App\Models\PricingTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    Artisan::call('crow:seed-manuscripts');
});

test('pricing tiers and books reflect new price points from visual redesign', function () {
    $vol1 = PricingTier::find('tier-book-1');
    $vol2 = PricingTier::find('tier-book-2');
    $vol3 = PricingTier::find('tier-book-3');
    $bundle = PricingTier::find('bundle-all-3');

    expect($vol1)->not->toBeNull();
    expect($vol1->price_idr)->toBe(49000);
    expect((float) $vol1->price_usd)->toBe(3.99);

    expect($vol2)->not->toBeNull();
    expect($vol2->price_idr)->toBe(49000);
    expect((float) $vol2->price_usd)->toBe(3.99);

    expect($vol3)->not->toBeNull();
    expect($vol3->price_idr)->toBe(99000);
    expect((float) $vol3->price_usd)->toBe(6.99);

    expect($bundle)->not->toBeNull();
    expect($bundle->price_idr)->toBe(149000);
    expect($bundle->strike_price_idr)->toBe(197000);
    expect((float) $bundle->price_usd)->toBe(9.99);

    $b1 = Book::find('book-1');
    $b2 = Book::find('book-2');
    $b3 = Book::find('book-3');

    expect($b1->price_single_idr)->toBe(49000);
    expect($b2->price_single_idr)->toBe(49000);
    expect($b3->price_single_idr)->toBe(99000);
});

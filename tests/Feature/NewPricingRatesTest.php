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
    expect($vol1->price_idr)->toBe(59000);
    expect((float) $vol1->price_usd)->toBe(4.99);

    expect($vol2)->not->toBeNull();
    expect($vol2->price_idr)->toBe(79000);
    expect((float) $vol2->price_usd)->toBe(6.99);

    expect($vol3)->not->toBeNull();
    expect($vol3->price_idr)->toBe(99000);
    expect((float) $vol3->price_usd)->toBe(8.99);

    expect($bundle)->not->toBeNull();
    expect($bundle->price_idr)->toBe(149000);
    expect($bundle->strike_price_idr)->toBe(237000);
    expect((float) $bundle->price_usd)->toBe(11.99);
    expect((float) $bundle->strike_price_usd)->toBe(20.97);

    $book1 = Book::find('book-1');
    $book2 = Book::find('book-2');
    $book3 = Book::find('book-3');

    expect($book1->price_single_idr)->toBe(59000);
    expect((float) $book1->price_single_usd)->toBe(4.99);
    expect($book2->price_single_idr)->toBe(79000);
    expect((float) $book2->price_single_usd)->toBe(6.99);
    expect($book3->price_single_idr)->toBe(99000);
    expect((float) $book3->price_single_usd)->toBe(8.99);
});

<?php

use App\Enums\PaymentStatus;
use App\Models\Book;
use App\Models\Order;
use App\Models\PricingTier;
use App\Models\User;
use App\Models\UserEntitlement;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['services.mayar.webhook_token' => 'test_secret_token_xyz']);

    // Seed Books
    Book::firstOrCreate(['id' => 'book-1'], ['volume_number' => 1, 'volume_code' => 'VOL. 01', 'price_single_idr' => 149000, 'titles' => ['id:casual' => 'Mahir'], 'subtitles' => ['id:casual' => 'Sub'], 'target_roles' => [], 'key_frameworks' => [], 'pdf_r2_keys' => [], 'is_active' => true]);
    Book::firstOrCreate(['id' => 'book-2'], ['volume_number' => 2, 'volume_code' => 'VOL. 02', 'price_single_idr' => 149000, 'titles' => ['id:casual' => 'Anti-Burnout'], 'subtitles' => ['id:casual' => 'Sub'], 'target_roles' => [], 'key_frameworks' => [], 'pdf_r2_keys' => [], 'is_active' => true]);
    Book::firstOrCreate(['id' => 'book-3'], ['volume_number' => 3, 'volume_code' => 'VOL. 03', 'price_single_idr' => 149000, 'titles' => ['id:casual' => 'Orkestrasi'], 'subtitles' => ['id:casual' => 'Sub'], 'target_roles' => [], 'key_frameworks' => [], 'pdf_r2_keys' => [], 'is_active' => true]);

    // Seed Bundle Tier
    $this->bundleTier = PricingTier::firstOrCreate(
        ['id' => 'bundle-all-3'],
        [
            'names' => ['id' => 'Trilogi Lengkap'],
            'descriptions' => ['id' => 'Semua Buku'],
            'features' => ['3 Buku'],
            'price_idr' => 299000,
            'price_usd' => 19.99,
            'included_book_ids' => ['book-1', 'book-2', 'book-3'],
            'is_best_value' => true,
            'is_active' => true,
        ]
    );

    // Create Pending Order
    $this->order = Order::create([
        'order_number' => 'CROW-TEST-0001',
        'customer_email' => 'guest.buyer@example.com',
        'pricing_tier_id' => $this->bundleTier->id,
        'amount_idr' => 299000,
        'mayar_invoice_id' => 'inv_test_123',
        'payment_status' => PaymentStatus::PENDING,
    ]);
});

test('rejects webhook with invalid callback token header', function () {
    $response = $this->withHeaders([
        'X-Callback-Token' => 'wrong_token',
    ])->postJson('/api/webhooks/mayar', [
        'event' => 'payment.received',
        'data' => [
            'orderNumber' => 'CROW-TEST-0001',
            'status' => 'PAID',
        ],
    ]);

    $response->assertStatus(401);
});

test('processes paid webhook for bundle tier and grants all 3 book entitlements', function () {
    $response = $this->withHeaders([
        'X-Callback-Token' => 'test_secret_token_xyz',
    ])->postJson('/api/webhooks/mayar', [
        'event' => 'payment.received',
        'data' => [
            'orderNumber' => 'CROW-TEST-0001',
            'invoiceId' => 'inv_test_123',
            'transactionId' => 'trx_mayar_999',
            'status' => 'PAID',
        ],
    ]);

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'order_number' => 'CROW-TEST-0001',
            'granted_books' => ['book-1', 'book-2', 'book-3'],
        ]);

    $this->order->refresh();
    expect($this->order->isPaid())->toBeTrue()
        ->and($this->order->paid_at)->not->toBeNull();

    // Verify user was auto-claimed
    $user = User::where('email', 'guest.buyer@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->hasAccessToBook('book-1'))->toBeTrue()
        ->and($user->hasAccessToBook('book-2'))->toBeTrue()
        ->and($user->hasAccessToBook('book-3'))->toBeTrue();

    expect(UserEntitlement::where('user_id', $user->id)->count())->toBe(3);
});

test('webhook is idempotent and does not duplicate entitlements on repeat calls', function () {
    $payload = [
        'event' => 'payment.received',
        'data' => [
            'orderNumber' => 'CROW-TEST-0001',
            'invoiceId' => 'inv_test_123',
            'transactionId' => 'trx_mayar_999',
            'status' => 'PAID',
        ],
    ];

    // First call
    $response1 = $this->withHeaders(['X-Callback-Token' => 'test_secret_token_xyz'])
        ->postJson('/api/webhooks/mayar', $payload);
    $response1->assertOk();

    // Second call
    $response2 = $this->withHeaders(['X-Callback-Token' => 'test_secret_token_xyz'])
        ->postJson('/api/webhooks/mayar', $payload);

    $response2->assertOk()
        ->assertJson([
            'status' => 'PAID',
            'idempotent' => true,
        ]);

    $user = User::where('email', 'guest.buyer@example.com')->first();
    expect(UserEntitlement::where('user_id', $user->id)->count())->toBe(3);
});

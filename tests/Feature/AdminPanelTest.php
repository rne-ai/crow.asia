<?php

use App\Models\Book;
use App\Models\PricingTier;
use App\Models\SupportedLocale;
use App\Models\User;
use App\Models\UserEntitlement;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    SupportedLocale::firstOrCreate(
        ['code' => 'id'],
        ['name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'flag_emoji' => '🇮🇩', 'has_dual_register' => true, 'is_active' => true]
    );

    $this->book = Book::firstOrCreate(
        ['id' => 'book-1'],
        [
            'volume_number' => 1,
            'volume_code' => 'VOL. 01',
            'price_single_idr' => 149000,
            'price_single_usd' => 9.99,
            'accent_hex' => '#1D4ED8',
            'titles' => ['id:casual' => 'Mahir'],
            'subtitles' => ['id:casual' => 'Sub'],
            'target_roles' => [],
            'key_frameworks' => [],
            'pdf_r2_keys' => [],
            'is_active' => true,
        ]
    );
});

test('reader role is forbidden from accessing filament admin panel', function () {
    $reader = User::create([
        'name' => 'Regular Reader',
        'email' => 'reader@test.com',
        'password' => 'password',
        'role' => 'reader',
    ]);

    $response = $this->actingAs($reader)->get('/admin');

    $response->assertStatus(403);
});

test('admin role can access filament admin panel dashboard', function () {
    $admin = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@test.com',
        'password' => 'password',
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertOk();
});

test('admin can access all filament resources pages', function () {
    $admin = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@test.com',
        'password' => 'password',
        'role' => 'admin',
    ]);

    $this->actingAs($admin)->get('/admin/books')->assertOk();
    $this->actingAs($admin)->get('/admin/chapters')->assertOk();
    $this->actingAs($admin)->get('/admin/pricing-tiers')->assertOk();
    $this->actingAs($admin)->get('/admin/orders')->assertOk();
    $this->actingAs($admin)->get('/admin/users')->assertOk();
});

test('admin can perform 1-click grant access to any user', function () {
    $admin = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@test.com',
        'password' => 'password',
        'role' => 'admin',
    ]);

    $targetUser = User::create([
        'name' => 'Target Student',
        'email' => 'student@test.com',
        'password' => 'password',
        'role' => 'reader',
    ]);

    expect($targetUser->hasAccessToBook('book-1'))->toBeFalse();

    UserEntitlement::create([
        'user_id' => $targetUser->id,
        'book_id' => 'book-1',
        'is_active' => true,
        'granted_at' => now(),
    ]);

    expect($targetUser->fresh()->hasAccessToBook('book-1'))->toBeTrue();
});

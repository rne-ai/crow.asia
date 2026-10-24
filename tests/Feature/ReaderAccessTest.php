<?php

use App\Enums\ContentRegister;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterTranslation;
use App\Models\ChecklistProgress;
use App\Models\SupportedLocale;
use App\Models\User;
use App\Models\UserEntitlement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed Supported Locales
    SupportedLocale::firstOrCreate(
        ['code' => 'id'],
        ['name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'flag_emoji' => '🇮🇩', 'has_dual_register' => true, 'is_active' => true]
    );
    SupportedLocale::firstOrCreate(
        ['code' => 'en'],
        ['name' => 'English', 'native_name' => 'English', 'flag_emoji' => '🌐', 'has_dual_register' => false, 'is_active' => true]
    );

    // Seed Book 1
    $this->book = Book::firstOrCreate(
        ['id' => 'book-1'],
        [
            'volume_number' => 1,
            'volume_code' => 'VOL. 01',
            'price_single_idr' => 149000,
            'price_single_usd' => 9.99,
            'accent_hex' => '#1D4ED8',
            'titles' => ['id:casual' => 'Mahir Memimpin Tim', 'id:standard' => 'Mahir Memimpin Tim (Standar)', 'en:standard' => 'Mastering Team Leadership'],
            'subtitles' => ['id:casual' => 'Panduan Taktis', 'id:standard' => 'Panduan Taktis', 'en:standard' => 'Tactical Guide'],
            'target_roles' => ['Lead'],
            'key_frameworks' => ['3K'],
            'pdf_r2_keys' => ['a5' => 'pdf/v1.pdf'],
            'is_active' => true,
        ]
    );

    // Create Free Sample Chapter (ch-0)
    $this->freeChapter = Chapter::firstOrCreate(
        ['book_id' => 'book-1', 'slug' => 'ch-0'],
        ['chapter_index' => 0, 'is_free_sample' => true, 'remotion_composition_id' => 'ChapterInteractiveStory16x9', 'remotion_duration_frames' => 540, 'remotion_fps' => 30]
    );

    ChapterTranslation::firstOrCreate(
        ['chapter_id' => $this->freeChapter->id, 'locale' => 'id', 'register' => 'casual'],
        [
            'title' => 'Pendahuluan',
            'raw_markdown' => '## Pendahuluan',
            'compiled_html' => '<p>Konten penuh pendahuluan...</p>',
            'safe_preview_html' => '<p>Konten preview...</p>',
            'has_scripts' => true,
            'extracted_checklists' => [],
            'remotion_scene_props' => ['steps' => []],
        ]
    );

    ChapterTranslation::firstOrCreate(
        ['chapter_id' => $this->freeChapter->id, 'locale' => 'en', 'register' => 'standard'],
        [
            'title' => 'Introduction',
            'raw_markdown' => '## Introduction',
            'compiled_html' => '<p>Full introduction content...</p>',
            'safe_preview_html' => '<p>Preview content...</p>',
            'has_scripts' => true,
            'extracted_checklists' => [],
            'remotion_scene_props' => ['steps' => []],
        ]
    );

    // Create Paid Chapter (ch-2)
    $this->paidChapter = Chapter::firstOrCreate(
        ['book_id' => 'book-1', 'slug' => 'ch-2'],
        ['chapter_index' => 2, 'is_free_sample' => false, 'remotion_composition_id' => 'ChapterInteractiveStory16x9', 'remotion_duration_frames' => 540, 'remotion_fps' => 30]
    );

    ChapterTranslation::firstOrCreate(
        ['chapter_id' => $this->paidChapter->id, 'locale' => 'id', 'register' => 'casual'],
        [
            'title' => 'Bab 2: Kerangka Kerja',
            'raw_markdown' => '## Bab 2',
            'compiled_html' => '<p>Konten rahasia berbayar penuh Bab 2...</p>',
            'safe_preview_html' => '<p>Cuplikan 3 paragraf Bab 2...</p>',
            'has_scripts' => true,
            'extracted_checklists' => [['itemIndex' => 0, 'labelHtml' => 'Checklist 1', 'isCompleted' => false]],
            'remotion_scene_props' => ['steps' => [['stepIndex' => 0], ['stepIndex' => 1], ['stepIndex' => 2], ['stepIndex' => 3]]],
        ]
    );
});

test('guest can access free sample chapter with full html and hasAccess true', function () {
    $response = $this->get('/read/book-1/id/ch-0');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Reader/ChapterView')
        ->where('currentChapter.slug', 'ch-0')
        ->where('currentChapter.isFreeSample', true)
        ->where('currentChapter.hasAccess', true)
        ->where('currentChapter.displayHtml', '<p>Konten penuh pendahuluan...</p>')
        ->where('showRegisterSwitcher', true)
    );
});

test('guest accessing paid chapter gets hasAccess false and masked preview html', function () {
    $response = $this->get('/read/book-1/id/ch-2');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Reader/ChapterView')
        ->where('currentChapter.slug', 'ch-2')
        ->where('currentChapter.isFreeSample', false)
        ->where('currentChapter.hasAccess', false)
        ->where('currentChapter.displayHtml', '<p>Cuplikan 3 paragraf Bab 2...</p>')
    );
});

test('entitled user gets hasAccess true and full html on paid chapter', function () {
    $user = User::create([
        'name' => 'Buyer',
        'email' => 'buyer@test.com',
        'password' => 'password',
        'role' => 'reader',
    ]);

    UserEntitlement::create([
        'user_id' => $user->id,
        'book_id' => 'book-1',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get('/read/book-1/id/ch-2');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Reader/ChapterView')
        ->where('currentChapter.hasAccess', true)
        ->where('currentChapter.displayHtml', '<p>Konten rahasia berbayar penuh Bab 2...</p>')
    );
});

test('admin user has access to all chapters automatically', function () {
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@test.com',
        'password' => 'password',
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->get('/read/book-1/id/ch-2');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('currentChapter.hasAccess', true)
    );
});

test('non-id locale forces register to standard and hides register switcher', function () {
    $response = $this->get('/read/book-1/en/ch-0?reg=casual');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('locale', 'en')
        ->where('activeRegister', 'standard')
        ->where('showRegisterSwitcher', false)
    );
});

test('checklist toggle api persists progress for authenticated user', function () {
    $user = User::create([
        'name' => 'Test Reader',
        'email' => 'reader@test.com',
        'password' => 'password',
        'role' => 'reader',
    ]);

    $response = $this->actingAs($user)->postJson('/api/checklists/toggle', [
        'book_id' => 'book-1',
        'chapter_slug' => 'ch-2',
        'item_index' => 0,
        'is_completed' => true,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'is_completed' => true,
            'authenticated' => true,
        ]);

    expect(ChecklistProgress::where('user_id', $user->id)->first()->is_completed)->toBeTrue();
});

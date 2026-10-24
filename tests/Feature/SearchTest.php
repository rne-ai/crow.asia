<?php

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterTranslation;
use App\Models\SupportedLocale;
use App\Models\User;
use App\Models\UserEntitlement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    SupportedLocale::firstOrCreate(
        ['code' => 'id'],
        ['name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'flag_emoji' => '🇮🇩', 'has_dual_register' => true, 'is_active' => true]
    );
    SupportedLocale::firstOrCreate(
        ['code' => 'en'],
        ['name' => 'English', 'native_name' => 'English', 'flag_emoji' => '🌐', 'has_dual_register' => false, 'is_active' => true]
    );

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

    // Free Chapter (ch-0)
    $ch0 = Chapter::firstOrCreate(
        ['book_id' => 'book-1', 'slug' => 'ch-0'],
        ['chapter_index' => 0, 'is_free_sample' => true, 'remotion_composition_id' => 'ChapterInteractiveStory16x9', 'remotion_duration_frames' => 540, 'remotion_fps' => 30]
    );

    $t0 = ChapterTranslation::firstOrCreate(
        ['chapter_id' => $ch0->id, 'locale' => 'id', 'register' => 'casual'],
        [
            'title' => 'Pendahuluan: Transisi Kepemimpinan',
            'raw_markdown' => '## Pendahuluan\n\nMenjadi pemimpin baru butuh adaptasi cara kerja.',
            'compiled_html' => '<p>Konten pendahuluan...</p>',
            'safe_preview_html' => '<p>Konten preview...</p>',
            'has_scripts' => false,
            'extracted_checklists' => [],
            'remotion_scene_props' => ['steps' => []],
        ]
    );

    DB::statement("
        UPDATE chapter_translations
        SET search_vector = setweight(to_tsvector('simple', coalesce(title, '')), 'A') ||
                            setweight(to_tsvector('simple', coalesce(raw_markdown, '')), 'B')
        WHERE id = :id
    ", ['id' => $t0->id]);

    // Paid Chapter (ch-7) with Rumus 3K
    $ch7 = Chapter::firstOrCreate(
        ['book_id' => 'book-1', 'slug' => 'ch-7'],
        ['chapter_index' => 7, 'is_free_sample' => false, 'remotion_composition_id' => 'ChapterInteractiveStory16x9', 'remotion_duration_frames' => 540, 'remotion_fps' => 30]
    );

    $t7 = ChapterTranslation::firstOrCreate(
        ['chapter_id' => $ch7->id, 'locale' => 'id', 'register' => 'casual'],
        [
            'title' => 'Bab 7: Obrolan 1: Menyepakati Target di Awal (Rumus 3K)',
            'raw_markdown' => '## Bab 7: Rumus 3K\n\nRumus 3K terdiri dari Keluaran, Kriteria, dan Kalender tenggat waktu.',
            'compiled_html' => '<p>Konten Rumus 3K...</p>',
            'safe_preview_html' => '<p>Cuplikan 3K...</p>',
            'has_scripts' => true,
            'extracted_checklists' => [],
            'remotion_scene_props' => ['steps' => []],
        ]
    );

    DB::statement("
        UPDATE chapter_translations
        SET search_vector = setweight(to_tsvector('simple', coalesce(title, '')), 'A') ||
                            setweight(to_tsvector('simple', coalesce(raw_markdown, '')), 'B')
        WHERE id = :id
    ", ['id' => $t7->id]);

    // English translation for ch-7
    $t7En = ChapterTranslation::firstOrCreate(
        ['chapter_id' => $ch7->id, 'locale' => 'en', 'register' => 'standard'],
        [
            'title' => 'Chapter 7: Agreeing on Targets Early (The 3C Formula)',
            'raw_markdown' => '## Chapter 7: The 3C Formula\n\nThe 3C Formula consists of Core Output, Clear Criteria, and Calendar Deadline.',
            'compiled_html' => '<p>3C Formula content...</p>',
            'safe_preview_html' => '<p>Preview 3C...</p>',
            'has_scripts' => true,
            'extracted_checklists' => [],
            'remotion_scene_props' => ['steps' => []],
        ]
    );

    DB::statement("
        UPDATE chapter_translations
        SET search_vector = setweight(to_tsvector('simple', coalesce(title, '')), 'A') ||
                            setweight(to_tsvector('simple', coalesce(raw_markdown, '')), 'B')
        WHERE id = :id
    ", ['id' => $t7En->id]);
});

test('short search query under 2 characters returns empty array', function () {
    $response = $this->getJson('/api/search?q=a');

    $response->assertOk()
        ->assertExactJson([]);
});

test('keyword search finds chapter matching tsvector and returns highlighted snippet', function () {
    $response = $this->getJson('/api/search?q=Rumus 3K&locale=id&register=casual');

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment([
            'book_id' => 'book-1',
            'chapter_slug' => 'ch-7',
            'chapter_title' => 'Bab 7: Obrolan 1: Menyepakati Target di Awal (Rumus 3K)',
            'is_free_sample' => false,
            'is_unlocked' => false,
        ]);

    $data = $response->json();
    expect($data[0]['snippet'])->toContain('<mark>');
});

test('search marks is_unlocked true for entitled user', function () {
    $user = User::create([
        'name' => 'Searcher',
        'email' => 'searcher@test.com',
        'password' => 'secret',
        'role' => 'reader',
    ]);

    UserEntitlement::create([
        'user_id' => $user->id,
        'book_id' => 'book-1',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->getJson('/api/search?q=Rumus 3K&locale=id&register=casual');

    $response->assertOk()
        ->assertJsonFragment([
            'chapter_slug' => 'ch-7',
            'is_unlocked' => true,
        ]);
});

test('english search searches only in english translations', function () {
    $response = $this->getJson('/api/search?q=Formula&locale=en');

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment([
            'chapter_slug' => 'ch-7',
            'chapter_title' => 'Chapter 7: Agreeing on Targets Early (The 3C Formula)',
        ]);
});

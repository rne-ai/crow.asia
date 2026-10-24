<?php

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterTranslation;
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

    $this->book = Book::firstOrCreate(
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

    $this->chapter = Chapter::firstOrCreate(
        ['book_id' => 'book-1', 'slug' => 'ch-0'],
        ['chapter_index' => 0, 'is_free_sample' => true, 'remotion_composition_id' => 'ChapterInteractiveStory16x9', 'remotion_duration_frames' => 540, 'remotion_fps' => 30]
    );

    ChapterTranslation::firstOrCreate(
        ['chapter_id' => $this->chapter->id, 'locale' => 'en', 'register' => 'standard'],
        [
            'title' => 'Introduction',
            'raw_markdown' => '## Introduction',
            'compiled_html' => '<p>Full content</p>',
            'safe_preview_html' => '<p>Preview</p>',
            'has_scripts' => false,
            'extracted_checklists' => [],
            'remotion_scene_props' => ['steps' => []],
        ]
    );
});

test('reader chapter view loads english metadata, currency, and translations', function () {
    $response = $this->get('/read/book-1/en/ch-0');
    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Reader/ChapterView')
        ->where('locale', 'en')
        ->where('currency', 'USD')
        ->where('translations.reader.prev_chapter', 'Previous Chapter')
        ->where('translations.reader.next_chapter', 'Next: Chapter :num')
        ->where('translations.paywall.badge', 'Exclusive to Registered Readers')
    );
});

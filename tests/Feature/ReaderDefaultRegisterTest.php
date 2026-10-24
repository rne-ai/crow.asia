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
            'titles' => ['id:casual' => 'Mahir Memimpin Tim (Kasual)', 'id:standard' => 'Mahir Memimpin Tim (Standar)'],
            'subtitles' => ['id:casual' => 'Panduan Taktis', 'id:standard' => 'Panduan Taktis'],
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
        ['chapter_id' => $this->chapter->id, 'locale' => 'id', 'register' => 'standard'],
        [
            'title' => 'Pendahuluan Standar',
            'raw_markdown' => '## Pendahuluan Standar',
            'compiled_html' => '<p>Konten standar</p>',
            'safe_preview_html' => '<p>Preview standar</p>',
            'has_scripts' => false,
            'extracted_checklists' => [],
            'remotion_scene_props' => ['steps' => []],
        ]
    );
});

test('reader defaults to standard register when no reg param or cookie is present', function () {
    $response = $this->get('/read/book-1/id/ch-0');
    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Reader/ChapterView')
        ->where('locale', 'id')
        ->where('activeRegister', 'standard')
    );
});

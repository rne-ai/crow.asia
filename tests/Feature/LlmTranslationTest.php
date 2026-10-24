<?php

use App\Ai\Agents\ManuscriptTranslatorAgent;
use App\Enums\ContentRegister;
use App\Enums\TranslationStatus;
use App\Jobs\TranslateChapterWithLlmJob;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterTranslation;
use App\Models\SupportedLocale;
use App\Services\Content\ChapterCompilerService;
use App\Services\Content\LlmTranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

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
    SupportedLocale::firstOrCreate(
        ['code' => 'ja'],
        ['name' => 'Japanese', 'native_name' => '日本語', 'flag_emoji' => '🇯🇵', 'has_dual_register' => false, 'is_active' => false]
    );

    $this->book = Book::firstOrCreate(
        ['id' => 'book-1'],
        [
            'volume_number' => 1,
            'volume_code' => 'VOL. 01',
            'price_single_idr' => 149000,
            'price_single_usd' => 9.99,
            'accent_hex' => '#1D4ED8',
            'titles' => ['en:standard' => 'Mastering Team Leadership'],
            'subtitles' => ['en:standard' => 'Tactical Guide'],
            'target_roles' => [],
            'key_frameworks' => [],
            'pdf_r2_keys' => [],
            'is_active' => true,
        ]
    );

    $this->chapter = Chapter::firstOrCreate(
        ['book_id' => 'book-1', 'slug' => 'ch-1'],
        ['chapter_index' => 1, 'is_free_sample' => true, 'remotion_composition_id' => 'ChapterInteractiveStory16x9', 'remotion_duration_frames' => 540, 'remotion_fps' => 30]
    );

    // Source translation in English Standard
    ChapterTranslation::firstOrCreate(
        ['chapter_id' => $this->chapter->id, 'locale' => 'en', 'register' => 'standard'],
        [
            'title' => 'Chapter 1: Transitioning from IC to Team Lead',
            'raw_markdown' => "## Chapter 1: Transitioning\n\nWorking with [Nama] to set expectations.\n\n- [ ] Set 3C Formula",
            'compiled_html' => '<p>Source content</p>',
            'safe_preview_html' => '<p>Preview</p>',
            'has_scripts' => false,
            'extracted_checklists' => [],
            'remotion_scene_props' => ['steps' => []],
        ]
    );
});

test('translate chapter job prompts ai agent and compiles translation in review status', function () {
    $fakedJapaneseMarkdown = "## 第1章: チームリーダーへの転換\n\n[Nama]と共に明確な目標を設定し、自立したチームを育成する。\n\n- [ ] 3Kフォーミュラを合意する";

    ManuscriptTranslatorAgent::fake([
        $fakedJapaneseMarkdown,
    ]);

    $job = new TranslateChapterWithLlmJob($this->chapter->id, 'ja');
    $job->handle(app(ChapterCompilerService::class));

    $jaTranslation = ChapterTranslation::where('chapter_id', $this->chapter->id)
        ->where('locale', 'ja')
        ->first();

    expect($jaTranslation)->not->toBeNull()
        ->and($jaTranslation->register)->toBe(ContentRegister::STANDARD)
        ->and($jaTranslation->translation_status)->toBe(TranslationStatus::REVIEW)
        ->and($jaTranslation->title)->toBe('第1章: チームリーダーへの転換')
        ->and($jaTranslation->compiled_html)->toContain('class="teammate-token"')
        ->and($jaTranslation->extracted_checklists)->toHaveCount(1);
});

test('llm translation service dispatches jobs for all chapters in a book', function () {
    Queue::fake();

    $service = new LlmTranslationService();
    $count = $service->translateBook('book-1', 'ja');

    expect($count)->toBe(1);

    Queue::assertPushed(TranslateChapterWithLlmJob::class, function ($job) {
        return $job->chapterId === $this->chapter->id && $job->targetLocale === 'ja';
    });
});

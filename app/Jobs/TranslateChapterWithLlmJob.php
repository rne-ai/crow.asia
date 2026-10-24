<?php

namespace App\Jobs;

use App\Ai\Agents\ManuscriptTranslatorAgent;
use App\Enums\ContentRegister;
use App\Enums\TranslationStatus;
use App\Models\Chapter;
use App\Models\ChapterTranslation;
use App\Services\Content\ChapterCompilerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\FailOnTimeout;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Tries(3)]
#[Backoff([15, 45, 90])]
#[Timeout(180)]
#[FailOnTimeout]
class TranslateChapterWithLlmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $chapterId,
        public readonly string $targetLocale,
    ) {}

    public function handle(ChapterCompilerService $compiler): void
    {
        // 1. Fetch source from English or Indonesian Standard
        $source = ChapterTranslation::query()
            ->where('chapter_id', $this->chapterId)
            ->whereIn('locale', ['en', 'id'])
            ->where('register', ContentRegister::STANDARD->value)
            ->orderByRaw("CASE WHEN locale = 'en' THEN 1 ELSE 2 END")
            ->firstOrFail();

        $prompt = "Target Locale Code: {$this->targetLocale}\n\n"
                . "Translate Title: {$source->title}\n\n"
                . "Translate Markdown Body:\n{$source->raw_markdown}";

        // 2. Prompt the AI translation agent
        $response = (new ManuscriptTranslatorAgent())->prompt($prompt);
        $translatedMarkdown = is_string($response) ? $response : (string) ($response->text ?? (string) $response);

        // Strip outer code fences if model wrapped response in ```markdown ... ```
        if (preg_match('/^```(?:markdown)?\s*\n(.*?)\n```$/s', trim($translatedMarkdown), $fenceMatch)) {
            $translatedMarkdown = trim($fenceMatch[1]);
        }

        // 3. Extract title from first ## line if present
        $lines = explode("\n", trim($translatedMarkdown));
        $translatedTitle = $source->title;
        if (isset($lines[0]) && str_starts_with($lines[0], '## ')) {
            $translatedTitle = trim(Str::after($lines[0], '## '));
        }

        $chapter = Chapter::query()->findOrFail($this->chapterId);

        // 4. Compile Markdown into sanitized HTML, tokenized DOM, and Remotion scene props
        $compiled = $compiler->compile(
            rawMarkdown: $translatedMarkdown,
            title: $translatedTitle,
            locale: $this->targetLocale,
            register: ContentRegister::STANDARD->value,
            bookId: $chapter->book_id,
            chapterIndex: $chapter->chapter_index,
        );

        // 5. Upsert ChapterTranslation record in REVIEW status
        $translation = ChapterTranslation::query()->updateOrCreate(
            [
                'chapter_id' => $this->chapterId,
                'locale' => $this->targetLocale,
                'register' => ContentRegister::STANDARD->value,
            ],
            [
                'title' => $translatedTitle,
                'raw_markdown' => $translatedMarkdown,
                'compiled_html' => $compiled->compiledHtml,
                'safe_preview_html' => $compiled->safePreviewHtml,
                'has_scripts' => $compiled->hasScripts,
                'extracted_checklists' => $compiled->extractedChecklists,
                'remotion_scene_props' => $compiled->remotionSceneProps,
                'translation_status' => TranslationStatus::REVIEW->value,
                'compiled_at' => now(),
            ]
        );

        // 6. Update PostgreSQL 18 tsvector search vector
        DB::statement("
            UPDATE chapter_translations
            SET search_vector = setweight(to_tsvector('simple', coalesce(title, '')), 'A') ||
                                setweight(to_tsvector('simple', coalesce(raw_markdown, '')), 'B')
            WHERE id = :id
        ", ['id' => $translation->id]);
    }
}

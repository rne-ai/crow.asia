<?php

namespace App\Http\Controllers;

use App\Enums\ContentRegister;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterTranslation;
use App\Models\ChecklistProgress;
use App\Models\SupportedLocale;
use App\Models\User;
use App\Services\Content\ChapterCacheService;
use App\Services\Content\ChapterCompilerService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReaderController extends Controller
{
    public function __construct(
        protected ChapterCacheService $cacheService,
        protected ChapterCompilerService $compiler,
    ) {}

    public function show(Request $request, string $bookId, string $locale = 'id', string $chapterSlug = 'ch-0'): Response
    {
        // 1. Validate Book
        $book = Book::query()->where('id', $bookId)->where('is_active', true)->firstOrFail();

        // 2. Resolve Locale
        $supportedLocales = SupportedLocale::active()->get()->map(fn ($l) => [
            'code' => $l->code,
            'name' => $l->name,
            'nativeName' => $l->native_name,
            'flagEmoji' => $l->flag_emoji,
            'isLlmTranslated' => $l->is_llm_translated,
        ])->all();

        $activeLocaleCodes = array_column($supportedLocales, 'code');
        if (!in_array($locale, $activeLocaleCodes, true)) {
            $locale = 'id';
        }

        // 3. Resolve Register
        // STRICT INVARIANT: If locale !== 'id', register is ALWAYS 'standard'.
        // In Indonesian (id), default register is 'standard', but user can toggle to 'casual'.
        if ($locale === 'id') {
            $regParam = $request->query('reg', $request->cookie('crow:id-register', 'standard'));
            $register = ($regParam === 'casual') ? ContentRegister::CASUAL : ContentRegister::STANDARD;
        } else {
            $register = ContentRegister::STANDARD;
        }

        // 4. Resolve Chapter
        $chapter = Chapter::query()
            ->where('book_id', $bookId)
            ->where('slug', $chapterSlug)
            ->firstOrFail();

        // 5. Determine User Access / Paywall Guard
        /** @var User|null $user */
        $user = $request->user();
        $isFreeSample = $chapter->is_free_sample;
        $hasAccess = $isFreeSample;

        if (!$hasAccess && $user) {
            $hasAccess = $user->isAdmin() || $user->hasAccessToBook($bookId);
        }

        // 6. Fetch from Redis Cache or Database
        $cached = $this->cacheService->get($bookId, $locale, $register, $chapterSlug);

        if (!$cached) {
            $translation = ChapterTranslation::query()
                ->where('chapter_id', $chapter->id)
                ->where('locale', $locale)
                ->where('register', $register->value)
                ->first();

            if (!$translation) {
                // Fallback to any available translation for this chapter in that locale
                $translation = ChapterTranslation::query()
                    ->where('chapter_id', $chapter->id)
                    ->where('locale', $locale)
                    ->firstOrFail();
            }

            $cached = $this->cacheService->warm($translation);
        }

        // 7. Paywall Masking if access is denied
        $displayHtml = $hasAccess ? $cached['compiled_html'] : $cached['safe_preview_html'];

        $sceneProps = $cached['remotion_scene_props'];
        if (!$hasAccess && isset($sceneProps['steps'])) {
            // Send only Step 0 preview, mask steps 1-3
            $sceneProps['steps'] = array_slice($sceneProps['steps'], 0, 1);
        }

        $youtubeVideoId = ($hasAccess && !empty($cached['is_youtube_enabled']))
            ? ($cached['youtube_unlisted_id'] ?? null)
            : null;

        // 8. Fetch User Checklists Progress if authenticated
        $userChecklists = [];
        if ($user) {
            $userChecklists = ChecklistProgress::query()
                ->where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->where('chapter_slug', $chapterSlug)
                ->pluck('is_completed', 'item_index')
                ->all();
        }

        $checklists = [];
        foreach ($cached['extracted_checklists'] ?? [] as $item) {
            $idx = $item['itemIndex'] ?? 0;
            $checklists[] = [
                'itemIndex' => $idx,
                'labelHtml' => $item['labelHtml'] ?? '',
                'isCompleted' => $userChecklists[$idx] ?? false,
            ];
        }

        // 9. All chapters list for sidebar
        $allChaptersRaw = Chapter::query()
            ->where('book_id', $bookId)
            ->orderBy('chapter_index')
            ->get();

        $allChapters = [];
        foreach ($allChaptersRaw as $ch) {
            $chFree = $ch->is_free_sample;
            $chUnlocked = $chFree || $hasAccess;
            $allChapters[] = [
                'index' => $ch->chapter_index,
                'slug' => $ch->slug,
                'title' => $this->getChapterTitleForSidebar($ch, $locale, $register),
                'isFreeSample' => $chFree,
                'isUnlocked' => $chUnlocked,
            ];
        }

        // 10. Assemble Props conforming strictly to ReaderPageInertiaProps
        $props = [
            'book' => [
                'id' => $book->id,
                'volumeCode' => $book->volume_code,
                'accentHex' => $book->accent_hex,
                'title' => $book->title($locale, $register),
                'subtitle' => $book->subtitle($locale, $register),
                'priceSingleIdr' => $book->price_single_idr,
                'priceSingleUsd' => (float) $book->price_single_usd,
            ],
            'locale' => $locale,
            'activeRegister' => $register->value,
            'showRegisterSwitcher' => ($locale === 'id'),
            'supportedLocales' => $supportedLocales,
            'currentChapter' => [
                'index' => $chapter->chapter_index,
                'slug' => $chapter->slug,
                'title' => $cached['title'],
                'isFreeSample' => $isFreeSample,
                'hasAccess' => $hasAccess,
                'hasScripts' => $cached['has_scripts'],
                'illustrationPath' => $chapter->illustration_path,
                'illustrationCaption' => null,
                'displayHtml' => $displayHtml,
                'checklists' => $checklists,
                'remotionConfig' => [
                    'compositionId' => $cached['remotion_scene_props']['compositionId'] ?? 'ChapterInteractiveStory16x9',
                    'width' => 1280,
                    'height' => 720,
                    'fps' => 30,
                    'durationInFrames' => 540,
                    'sceneProps' => $sceneProps,
                ],
                'optionalYoutube' => [
                    'isEnabled' => (bool) $cached['is_youtube_enabled'],
                    'videoId' => $youtubeVideoId,
                ],
            ],
            'allChapters' => $allChapters,
        ];

        return Inertia::render('Reader/ChapterView', $props);
    }

    private function getChapterTitleForSidebar(Chapter $chapter, string $locale, ContentRegister $register): string
    {
        $cached = $this->cacheService->get($chapter->book_id, $locale, $register, $chapter->slug);
        if ($cached && !empty($cached['title'])) {
            return $cached['title'];
        }

        $translation = $chapter->translations
            ->where('locale', $locale)
            ->where('register', $register)
            ->first() ?? $chapter->translations->where('locale', $locale)->first();

        return $translation?->title ?? "Bab {$chapter->chapter_index}";
    }
}

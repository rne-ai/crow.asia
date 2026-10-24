<?php

namespace App\Services\Content;

use App\Enums\ContentRegister;
use App\Models\ChapterTranslation;
use Illuminate\Support\Facades\Cache;

class ChapterCacheService
{
    public const DEFAULT_TTL_SECONDS = 604800; // 7 days

    public static function key(string $bookId, string $locale, string|ContentRegister $register, string $slug): string
    {
        $regStr = $register instanceof ContentRegister ? $register->value : $register;
        $normalizedRegister = ($locale === 'id') ? $regStr : 'standard';

        return "crow:chapter:v1:{$bookId}:{$locale}:{$normalizedRegister}:{$slug}";
    }

    public function get(string $bookId, string $locale, string|ContentRegister $register, string $slug): ?array
    {
        $key = self::key($bookId, $locale, $register, $slug);
        return Cache::get($key);
    }

    public function put(string $bookId, string $locale, string|ContentRegister $register, string $slug, array $data, ?int $ttl = null): void
    {
        $key = self::key($bookId, $locale, $register, $slug);
        Cache::put($key, $data, $ttl ?? self::DEFAULT_TTL_SECONDS);
    }

    public function forget(string $bookId, string $locale, string|ContentRegister $register, string $slug): void
    {
        $key = self::key($bookId, $locale, $register, $slug);
        Cache::forget($key);
    }

    public function warm(ChapterTranslation $translation): array
    {
        $chapter = $translation->chapter;
        $book = $chapter->book;

        $payload = [
            'book_id' => $book->id,
            'volume_code' => $book->volume_code,
            'chapter_index' => $chapter->chapter_index,
            'slug' => $chapter->slug,
            'locale' => $translation->locale,
            'register' => $translation->register->value,
            'title' => $translation->title,
            'compiled_html' => $translation->compiled_html,
            'safe_preview_html' => $translation->safe_preview_html,
            'has_scripts' => $translation->has_scripts,
            'extracted_checklists' => $translation->extracted_checklists,
            'remotion_scene_props' => $translation->remotion_scene_props,
            'is_free_sample' => $chapter->is_free_sample,
            'motif_icon' => $chapter->motif_icon,
            'is_youtube_enabled' => $chapter->is_youtube_enabled,
            'youtube_unlisted_id' => $chapter->youtube_unlisted_id,
        ];

        $this->put($book->id, $translation->locale, $translation->register, $chapter->slug, $payload);

        return $payload;
    }
}

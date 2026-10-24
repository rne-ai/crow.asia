<?php

namespace App\Services\Content;

use App\Jobs\TranslateChapterWithLlmJob;
use App\Models\Book;
use App\Models\Chapter;

class LlmTranslationService
{
    /**
     * Dispatch an AI translation job for a single chapter.
     */
    public function translateChapter(int $chapterId, string $targetLocale): void
    {
        TranslateChapterWithLlmJob::dispatch($chapterId, $targetLocale);
    }

    /**
     * Dispatch AI translation jobs for all chapters of a book.
     *
     * @return int Number of dispatched chapter jobs
     */
    public function translateBook(string $bookId, string $targetLocale): int
    {
        $book = Book::query()->findOrFail($bookId);
        $chapters = $book->chapters()->pluck('id');

        foreach ($chapters as $chapterId) {
            TranslateChapterWithLlmJob::dispatch($chapterId, $targetLocale);
        }

        return $chapters->count();
    }
}

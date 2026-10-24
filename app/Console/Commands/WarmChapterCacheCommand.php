<?php

namespace App\Console\Commands;

use App\Models\ChapterTranslation;
use App\Services\Content\ChapterCacheService;
use Illuminate\Console\Command;

class WarmChapterCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crow:warm-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pemanasan cache Redis 8 untuk seluruh 135 terjemahan bab crow.asia';

    public function handle(ChapterCacheService $cacheService): int
    {
        $this->info('Memulai pemanasan cache bab ke Redis 8...');

        $translations = ChapterTranslation::query()
            ->with(['chapter.book'])
            ->get();

        $count = 0;
        foreach ($translations as $translation) {
            $cacheService->warm($translation);
            $count++;
        }

        $this->info("✓ Sukses menghangatkan {$count} bab ke Redis 8!");

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Enums\ContentRegister;
use App\Enums\TranslationStatus;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterTranslation;
use App\Services\Content\ChapterCacheService;
use App\Services\Content\ChapterCompilerService;
use App\Services\Content\ManuscriptParserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedManuscriptsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crow:seed-manuscripts {--force : Force re-compilation and cache warming}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ingest, compile, and cache the 9 seed manuscripts from ./books/**/*.md into PostgreSQL 18 & Redis 8';

    public function handle(
        ManuscriptParserService $parser,
        ChapterCompilerService $compiler,
        ChapterCacheService $cacheService,
    ): int {
        $this->info('Starting manuscript ingestion for crow.asia...');

        // 1. Seed or update 3 Master Books & Pricing Tiers
        $this->seedBooks();
        $this->seedPricingTiers();

        // 2. Define the 9 manuscript files mapping
        $manuscripts = [
            ['path' => 'books/id/casual/book-1.md', 'bookId' => 'book-1', 'locale' => 'id', 'register' => ContentRegister::CASUAL],
            ['path' => 'books/id/casual/book-2.md', 'bookId' => 'book-2', 'locale' => 'id', 'register' => ContentRegister::CASUAL],
            ['path' => 'books/id/casual/book-3.md', 'bookId' => 'book-3', 'locale' => 'id', 'register' => ContentRegister::CASUAL],
            ['path' => 'books/id/standar/book-1.md', 'bookId' => 'book-1', 'locale' => 'id', 'register' => ContentRegister::STANDARD],
            ['path' => 'books/id/standar/book-2.md', 'bookId' => 'book-2', 'locale' => 'id', 'register' => ContentRegister::STANDARD],
            ['path' => 'books/id/standar/book-3.md', 'bookId' => 'book-3', 'locale' => 'id', 'register' => ContentRegister::STANDARD],
            ['path' => 'books/en/book-1.md', 'bookId' => 'book-1', 'locale' => 'en', 'register' => ContentRegister::STANDARD],
            ['path' => 'books/en/book-2.md', 'bookId' => 'book-2', 'locale' => 'en', 'register' => ContentRegister::STANDARD],
            ['path' => 'books/en/book-3.md', 'bookId' => 'book-3', 'locale' => 'en', 'register' => ContentRegister::STANDARD],
        ];

        $totalTranslationsCount = 0;

        foreach ($manuscripts as $item) {
            $fullPath = base_path($item['path']);
            if (!file_exists($fullPath)) {
                $this->error("File not found: {$item['path']}");
                continue;
            }

            $content = file_get_contents($fullPath);
            $parsedChapters = $parser->parse($content);

            $this->line("Processing <comment>{$item['path']}</comment> (" . count($parsedChapters) . ' chapters)...');

            foreach ($parsedChapters as $c) {
                // Ensure Chapter record exists
                $chapter = Chapter::query()->firstOrCreate(
                    [
                        'book_id' => $item['bookId'],
                        'slug' => $c['slug'],
                    ],
                    [
                        'chapter_index' => $c['index'],
                        'is_free_sample' => $c['index'] <= 1,
                        'motif_icon' => $this->resolveMotifIcon($c['index']),
                        'remotion_composition_id' => 'ChapterInteractiveStory16x9',
                        'remotion_duration_frames' => 540,
                        'remotion_fps' => 30,
                        'is_youtube_enabled' => false,
                    ]
                );

                // Compile Chapter Markdown
                $compiled = $compiler->compile(
                    rawMarkdown: $c['rawMarkdown'],
                    title: $c['title'],
                    locale: $item['locale'],
                    register: $item['register'],
                    bookId: $item['bookId'],
                    chapterIndex: $c['index'],
                );

                // Upsert ChapterTranslation
                $translation = ChapterTranslation::query()->updateOrCreate(
                    [
                        'chapter_id' => $chapter->id,
                        'locale' => $item['locale'],
                        'register' => $item['register']->value,
                    ],
                    [
                        'title' => $c['title'],
                        'raw_markdown' => $c['rawMarkdown'],
                        'compiled_html' => $compiled->compiledHtml,
                        'safe_preview_html' => $compiled->safePreviewHtml,
                        'has_scripts' => $compiled->hasScripts,
                        'extracted_checklists' => $compiled->extractedChecklists,
                        'remotion_scene_props' => $compiled->remotionSceneProps,
                        'translation_status' => TranslationStatus::PUBLISHED,
                        'compiled_at' => now(),
                    ]
                );

                // Update PostgreSQL 18 search_vector
                DB::statement("
                    UPDATE chapter_translations
                    SET search_vector = setweight(to_tsvector('simple', coalesce(title, '')), 'A') ||
                                        setweight(to_tsvector('simple', coalesce(raw_markdown, '')), 'B')
                    WHERE id = :id
                ", ['id' => $translation->id]);

                // Warm Redis 8 Cache
                $cacheService->warm($translation);

                $totalTranslationsCount++;
            }
        }

        $this->newLine();
        $this->info("✓ Ingestion complete! Total Chapter Translations: {$totalTranslationsCount}");

        return Command::SUCCESS;
    }

    private function seedBooks(): void
    {
        $booksData = [
            [
                'id' => 'book-1',
                'volume_number' => 1,
                'volume_code' => 'VOL. 01',
                'price_single_idr' => 59000,
                'price_single_usd' => 4.99,
                'accent_hex' => '#1D4ED8',
                'titles' => [
                    'id:casual' => 'Mahir Memimpin Tim',
                    'id:standard' => 'Mahir Memimpin Tim',
                    'en:standard' => 'Mastering Team Leadership',
                ],
                'subtitles' => [
                    'id:casual' => 'Panduan Taktis Transisi Individual Contributor Menjadi Team Lead Tangguh',
                    'id:standard' => 'Panduan Taktis Transisi Individual Contributor Menjadi Team Lead Tangguh',
                    'en:standard' => 'A Tactical Field Playbook for First-Time Engineering & Product Team Leads',
                ],
                'target_roles' => ['First-Time Team Lead', 'Senior Engineer', 'Product Manager'],
                'key_frameworks' => ['Rumus 3K', 'Kerangka 4x4', 'Kalibrasi L1-L4', 'Rencana 30 Hari'],
                'pdf_r2_keys' => ['a5' => 'playbooks/vol-01-a5.pdf'],
                'is_active' => true,
            ],
            [
                'id' => 'book-2',
                'volume_number' => 2,
                'volume_code' => 'VOL. 02',
                'price_single_idr' => 79000,
                'price_single_usd' => 6.99,
                'accent_hex' => '#EA580C',
                'titles' => [
                    'id:casual' => 'Napas Panjang Memimpin Tim',
                    'id:standard' => 'Napas Panjang Memimpin Tim',
                    'en:standard' => 'Endurance in Team Leadership',
                ],
                'subtitles' => [
                    'id:casual' => 'Biar Nggak Boncos Energi & Tetap Waras di Tengah Tekanan Target',
                    'id:standard' => 'Biar Nggak Boncos Energi & Tetap Waras di Tengah Tekanan Target',
                    'en:standard' => 'Navigating Burnout, Sustainable Cadence, and Psychological Safety',
                ],
                'target_roles' => ['Engineering Manager', 'Tech Lead', 'Startup Founder'],
                'key_frameworks' => ['Beban 30/70', 'Audit Energi', 'Kapasitas Berkelanjutan', 'Boundary Shield'],
                'pdf_r2_keys' => ['a5' => 'playbooks/vol-02-a5.pdf'],
                'is_active' => true,
            ],
            [
                'id' => 'book-3',
                'volume_number' => 3,
                'volume_code' => 'VOL. 03',
                'price_single_idr' => 99000,
                'price_single_usd' => 8.99,
                'accent_hex' => '#0F766E',
                'titles' => [
                    'id:casual' => 'Memimpin Para Leader',
                    'id:standard' => 'Memimpin Para Leader',
                    'en:standard' => 'Leading Other Leaders',
                ],
                'subtitles' => [
                    'id:casual' => 'Dari Mengelola Kerjaan Teknis ke Orkestrasi Manajer & Eksekutif',
                    'id:standard' => 'Dari Mengelola Kerjaan Teknis ke Orkestrasi Manajer & Eksekutif',
                    'en:standard' => 'Orchestrating Senior Engineering Leads, Directors, and Executive Alignment',
                ],
                'target_roles' => ['VP of Engineering', 'Head of Product', 'CTO'],
                'key_frameworks' => ['Orkestrasi Multi-Tim', 'Pendelegasian Bertingkat', 'Penyelarasan Eksekutif', 'Kaderisasi Leader'],
                'pdf_r2_keys' => ['a5' => 'playbooks/vol-03-a5.pdf'],
                'is_active' => true,
            ],
        ];

        foreach ($booksData as $b) {
            Book::query()->updateOrCreate(['id' => $b['id']], $b);
        }
    }

    private function seedPricingTiers(): void
    {
        $tiers = [
            [
                'id' => 'tier-book-1',
                'names' => ['id' => 'VOL. 01: Mahir Memimpin Tim', 'en' => 'VOL. 01: Mastering Team Leadership'],
                'descriptions' => ['id' => 'Menciptakan Tim yang Mandiri & Bisa Diandalkan', 'en' => 'Transition from IC to High-Impact Team Lead'],
                'features' => [
                    'id' => [
                        'Buka Penuh Bab 2 s.d. Bab 13 + Lampiran Ringkasan Meja Kerja',
                        'Templat Skrip Memberi Target, Memuji, dan Menegur Empat Mata',
                        'Format Interaktif Web-Reader + Berkas PDF Siap Cetak',
                        'Sekali Bayar • Akses Selamanya',
                    ],
                    'en' => [
                        'Full Access to Chapters 2–13 + Desk Reference Summary',
                        'Ready-to-Copy Scripts for Goal Setting, Praise, and 1-on-1 Feedback',
                        'Interactive Web-Reader + Print-Ready PDF',
                        'One-Time Payment • Lifetime Access',
                    ],
                ],
                'badges' => null,
                'price_idr' => 59000,
                'strike_price_idr' => null,
                'price_usd' => 4.99,
                'included_book_ids' => ['book-1'],
                'sort_order' => 1,
                'is_best_value' => false,
                'is_active' => true,
            ],
            [
                'id' => 'tier-book-2',
                'names' => ['id' => 'VOL. 02: Napas Panjang Memimpin Tim', 'en' => 'VOL. 02: Endurance in Team Leadership'],
                'descriptions' => ['id' => 'Mengatasi Kejenuhan & Membangun Sistem Kerja Mandiri', 'en' => 'Anti-Burnout & Sustainable Cadence'],
                'features' => [
                    'id' => [
                        'Buka Penuh Bab 2 s.d. Bab 13 + Lampiran Ringkasan Meja Kerja',
                        'Templat Skrip Pendelegasian Wewenang & Negosiasi Beban Kerja',
                        'Format Interaktif Web-Reader + Berkas PDF Siap Cetak',
                        'Sekali Bayar • Akses Selamanya',
                    ],
                    'en' => [
                        'Full Access to Chapters 2–13 + Desk Reference Summary',
                        'Delegation & Capacity Negotiation Script Templates',
                        'Interactive Web-Reader + Print-Ready PDF',
                        'One-Time Payment • Lifetime Access',
                    ],
                ],
                'badges' => null,
                'price_idr' => 79000,
                'strike_price_idr' => null,
                'price_usd' => 6.99,
                'included_book_ids' => ['book-2'],
                'sort_order' => 2,
                'is_best_value' => false,
                'is_active' => true,
            ],
            [
                'id' => 'tier-book-3',
                'names' => ['id' => 'VOL. 03: Memimpin Para Leader', 'en' => 'VOL. 03: Leading Other Leaders'],
                'descriptions' => ['id' => 'Dari Mengarahkan Tim ke Menggerakkan Divisi & Cabang', 'en' => 'Orchestrating Senior Leads & Directors'],
                'features' => [
                    'id' => [
                        'Buka Penuh Bab 2 s.d. Bab 13 + Lampiran Ringkasan Meja Kerja',
                        'Templat Skrip Membina Kepala Tim, Target Cabang, & Mediasi',
                        'Format Interaktif Web-Reader + Berkas PDF Siap Cetak',
                        'Sekali Bayar • Akses Selamanya',
                    ],
                    'en' => [
                        'Full Access to Chapters 2–13 + Desk Reference Summary',
                        'Lead Coaching, Multi-Branch Alignment, & Mediation Scripts',
                        'Interactive Web-Reader + Print-Ready PDF',
                        'One-Time Payment • Lifetime Access',
                    ],
                ],
                'badges' => null,
                'price_idr' => 99000,
                'strike_price_idr' => null,
                'price_usd' => 8.99,
                'included_book_ids' => ['book-3'],
                'sort_order' => 3,
                'is_best_value' => false,
                'is_active' => true,
            ],
            [
                'id' => 'bundle-all-3',
                'names' => ['id' => 'Complete Trilogy Box Set (Volume 01, 02 & 03)', 'en' => 'Complete Trilogy Box Set (Volume 01, 02 & 03)'],
                'descriptions' => ['id' => 'Akses menyeluruh seluruh trilogi (39 bab praktis + 3 lampiran), Bahasa Indonesia (Kasual & Standar) + English, dan seluruh berkas PDF A5.', 'en' => 'Full access to complete trilogy (39 practical chapters + 3 summaries), ID (Casual & Standard) + English, and all A5 PDFs.'],
                'features' => [
                    'id' => [
                        'Buka Penuh Seluruh 3 Buku (Total 39 Bab Praktis + 3 Lampiran)',
                        'Ratusan Contoh Kalimat & Skrip Percakapan Siap Salin',
                        'Bebas Beralih Mode Bahasa Bacaan Kapan Saja (Standar & Kasual)',
                        'Akses Web-Reader Selamanya + Unduhan 3 PDF Siap Cetak',
                    ],
                    'en' => [
                        'Full Access to All 3 Books (39 Practical Chapters + 3 Summaries)',
                        'Hundreds of Ready-to-Copy Scripts for Difficult Situations',
                        'Seamless Language & Register Switching (Standard & Casual)',
                        'Lifetime Interactive Web-Reader Access + 3 Print-Ready PDFs',
                    ],
                ],
                'badges' => ['label' => 'Best Value', 'color' => '#1D4ED8'],
                'price_idr' => 149000,
                'strike_price_idr' => 237000,
                'price_usd' => 11.99,
                'strike_price_usd' => 20.97,
                'included_book_ids' => ['book-1', 'book-2', 'book-3'],
                'sort_order' => 4,
                'is_best_value' => true,
                'is_active' => true,
            ],
        ];

        foreach ($tiers as $t) {
            \App\Models\PricingTier::query()->updateOrCreate(['id' => $t['id']], $t);
        }
    }

    private function resolveMotifIcon(int $chapterIndex): string
    {
        $icons = [
            0 => 'compass',
            1 => 'shuffle',
            2 => 'layout-grid',
            3 => 'ear',
            4 => 'help-circle',
            5 => 'target',
            6 => 'heart',
            7 => 'check-square',
            8 => 'sparkles',
            9 => 'alert-triangle',
            10 => 'calendar',
            11 => 'activity',
            12 => 'sliders',
            13 => 'flag',
            14 => 'book-open',
        ];

        return $icons[$chapterIndex] ?? 'compass';
    }
}

---
document: 08-Manuscript-Parser-And-AI-Pipeline-Spec
project: crow.asia
version: 1.0.0-greenfield
date: 2026-10-24
stack: PHP 8.5, Laravel 13, Laravel AI SDK (Laravel\Ai), Symfony HtmlSanitizer, League CommonMark, PostgreSQL 18, Redis 8
purpose: Concrete Implementation Specification for 9-MD Ingestion, Chapter Pre-Compilation, XSS Sanitization, Remotion 4-Step Auto-Extractor, and Laravel 13 AI Asian Translation Pipeline.
---

# Spesifikasi Teknis: Manuscript Parser, Compiler & Pipeline Translasi AI (`crow.asia`)

> **ACUAN WAJIB UNTUK `TASK-04` DAN `TASK-14` DI OPENCODE:**
> Dokumen ini menjabarkan algoritma deterministik untuk mem-parsing ke-9 file naskah awal (`./books/**/*.md`) tanpa pernah memuat seluruh isi file ke dalam konteks prompt LLM, mengkompilasinya menjadi HTML aman + konfigurasi animasi Remotion 16:9, serta menjalankan pipeline penerjemahan bahasa Asia menggunakan **First-Party Laravel 13 AI SDK (`Laravel\Ai`)**.

---

## 1. Pemetaan Deterministik 9 File Naskah Awal (`ManuscriptParserService.php`)

### 1.1 Matriks Sumber File Naskah
Ke-9 file naskah berformat GitHub Flavored Markdown (`.md`) di direktori `./books/` wajib dipetakan secara ketat sebagai berikut:

| File Path Relatif | `book_id` | `locale` | `register` | Default Token `[Nama]` |
| :--- | :---: | :---: | :---: | :---: |
| `books/id/casual/book-1.md` | `book-1` | `id` | `casual` | `Budi` |
| `books/id/casual/book-2.md` | `book-2` | `id` | `casual` | `Budi` |
| `books/id/casual/book-3.md` | `book-3` | `id` | `casual` | `Budi` |
| `books/id/standar/book-1.md` | `book-1` | `id` | `standard` | `Budi` |
| `books/id/standar/book-2.md` | `book-2` | `id` | `standard` | `Budi` |
| `books/id/standar/book-3.md` | `book-3` | `id` | `standard` | `Budi` |
| `books/en/book-1.md` | `book-1` | `en` | `standard` | `Alex` |
| `books/en/book-2.md` | `book-2` | `en` | `standard` | `Alex` |
| `books/en/book-3.md` | `book-3` | `en` | `standard` | `Alex` |

### 1.2 Algoritma Pemecahan 15 Bab (`ch-0` s/d `ch-14`) & Resolusi Catatan Kaki Mandiri
1. **Ekstraksi Definisi Catatan Kaki Global (`[^n]: ...`)**:
   - Sebelum memecah file berdasarkan heading `## `, ekstrak seluruh baris definisi catatan kaki di bagian akhir dokumen menggunakan regex `/^\[\^([^\]]+)\]:\s*(.+)$/m` ke dalam dictionary `$globalFootnotes[$id] = $definition`.
2. **Pemecahan Seksi Level-2 (`## `)**:
   - Pecah naskah menggunakan delimiter `/^##\s+(.+)$/m`.
   - **Abaikan Seksi Daftar Isi**: Jika judul heading `## ` mengandung `Daftar Isi` atau `Table of Contents` (case-insensitive), lewati seksi tersebut.
   - **Penggabungan Seksi Rujukan Riset ke Bab 14 (`ch-14`)**: Jika terdapat heading `## Rujukan Riset`, `## Daftar Pustaka`, atau `## Primary Research Citations` setelah Bab 14 (Lampiran/Appendix), gabungkan isi markdown-nya ke bagian bawah `ch-14` sebagai sub-heading `### Rujukan Riset Utama` sehingga jumlah bab per buku selalu **tepat 15 bab (`chapter_index` `0` s/d `14`, `slug` `ch-0` s/d `ch-14`)**.
   - **Verifikasi Jumlah Baris**: 3 buku × 15 bab × 3 variasi (`id:casual`, `id:standard`, `en:standard`) = **tepat 135 baris** pada tabel `chapter_translations`.
3. **Injeksi Catatan Kaki Mandiri per Bab**:
   - Untuk setiap bab `0..14`, cari semua referensi `[^n]` yang muncul di dalam `raw_markdown` bab tersebut.
   - Tempelkan hanya definisi `[^n]: ...` yang benar-benar direferensikan oleh bab tersebut di bagian paling bawah `raw_markdown` bab itu agar setiap bab berdiri sendiri secara utuh.

---

## 2. Spesifikasi `ChapterCompilerService.php` (Compile-on-Write & XSS Sanitizer)

Setiap kali sebuah bab disimpan (baik melalui `crow:seed-manuscripts`, editor Filament v5, maupun selesai diterjemahkan oleh `TranslateChapterWithLlmJob`), `ChapterCompilerService::compile()` wajib menghasilkan 5 output:

### 2.1 Transformasi Markdown & Token Nama Anggota Tim (`.teammate-token`)
1. **Konversi GitHub Alerts / Callouts**:
   - Ubah blockquote `> [!NOTE]`, `> [!TIP]`, `> [!IMPORTANT]`, `> [!WARNING]`, `> [!CAUTION]` menjadi blok `<aside class="crow-callout crow-callout-{type}">` dengan label taktis yang jelas.
2. **Transformasi Placeholder Nama Anggota Tim**:
   - Ubah semua kemunculan placeholder `[Nama]`, `[Nama Staf]`, `[Nama Anggota Tim]`, `[Teammate Name]`, atau `{{teammateName}}` menjadi elemen DOM terstruktur:
     ```html
     <span class="teammate-token" data-default="Budi">Budi</span>
     ```
   - Dengan struktur ini, komponen React `ScriptPersonalizerInput.tsx` cukup menjalankan `document.querySelectorAll('.teammate-token').forEach(el => el.textContent = name || el.dataset.default || 'Budi')` yang selesai dalam **`< 2ms`**.
3. **Tombol Salin Skrip Otomatis pada `<details>`**:
   - Setiap blok `<details>` yang membungkus skrip dialog 1-on-1 diberi atribut `class="crow-script-drawer"` dan tombol aksi `data-copy-script="true"` agar pembaca dapat menyalin isi dialog bersih (sudah termasuk nama anggota tim yang dipersonalisasi) ke clipboard dalam 1 klik.
4. **Sanitasi HTML Ketat (`Symfony\Component\HtmlSanitizer\HtmlSanitizer`)**:
   - Konfigurasikan `HtmlSanitizerConfig` yang mengizinkan elemen struktural GFM (`h1`..`h6`, `p`, `ul`, `ol`, `li`, `table`, `thead`, `tbody`, `tr`, `th`, `td`, `blockquote`, `pre`, `code`, `details`, `summary`, `aside`, `span`, `sup`, `sub`, `hr`, `strong`, `em`, `a`) serta atribut `class`, `id`, `data-default`, `data-copy-script`, `open`, dan `href` (hanya skema `https`, `http`, `mailto`, dan anchor `#`), serta **memblokir semua `<script>`, `<iframe>`, `<object>`, dan atribut `on*`**.

### 2.2 Pembuatan `safe_preview_html` (Paywall Guard)
- Ekstrak maksimal **3 elemen blok pertama** (`<p>` atau `<blockquote>`) dari `compiled_html` untuk disimpan ke kolom `safe_preview_html`.
- Saat `hasAccess === false` (pada Bab 2 s/d 14 untuk pengunjung yang belum membeli), `ReaderController` hanya mengirimkan `safe_preview_html` ke dalam prop `displayHtml`.

### 2.3 Ekstraksi Otomatis `remotion_scene_props` (4 Step Markers Landscape 16:9)
Jika bab belum memiliki kustomisasi manual dari Admin, `ChapterCompilerService` otomatis membangun JSON `remotion_scene_props` berdurasi `540 frames` (`18 detik @ 30 FPS`, terbagi rata menjadi 4 step @ `135 frames`):
- **Step 0 (`startFrame: 0`, `durationFrames: 135`)**: *Masalah & Konteks Lapangan* — diambil dari sub-heading pertama dan ringkasan masalah di awal bab.
- **Step 1 (`startFrame: 135`, `durationFrames: 135`)**: *Kerangka Taktis Utama* — diambil dari poin-poin langkah/rumus inti di tengah bab.
- **Step 2 (`startFrame: 270`, `durationFrames: 135`)**: *Simulasi Skrip 1-on-1* — diekstrak dari kutipan dialog di dalam `<details>` (menggunakan placeholder `{{teammateName}}`).
- **Step 3 (`startFrame: 405`, `durationFrames: 135`)**: *Target Praktik & Checklist* — diambil dari 3 item pertama `extracted_checklists`.

---

## 3. Strategi Caching Redis 8 & Search Vector (`ChapterCacheService.php`)

1. **Kunci Cache Redis 8**:
   ```php
   public static function key(string $bookId, string $locale, string $register, string $slug): string
   {
       $normalizedRegister = $locale === 'id' ? $register : 'standard';
       return "crow:chapter:v1:{$bookId}:{$locale}:{$normalizedRegister}:{$slug}";
   }
   ```
2. **Perpanjangan TTL tanpa Re-fetch (`Cache::touch`)**:
   - Gunakan fitur baru **Laravel 13 `Cache::touch($key, now()->addDays(7))`** saat pembaca aktif mengakses bab populer, dan lakukan invalidasi + pemanasan ulang (*cache warming*) otomatis setiap kali `ChapterTranslation` disimpan.
3. **Update `search_vector` & `embedding` PostgreSQL 18**:
   - Saat kompilasi selesai, jalankan:
     ```sql
     UPDATE chapter_translations
     SET search_vector = setweight(to_tsvector('simple', coalesce(title, '')), 'A') ||
                         setweight(to_tsvector('simple', coalesce(raw_markdown, '')), 'B')
     WHERE id = :id;
     ```

---

## 4. Pipeline Translasi Bahasa Asia via First-Party Laravel 13 AI SDK (`TASK-14`)

### 4.1 Arsitektur `ManuscriptTranslatorAgent.php` (`Laravel\Ai`)
Gunakan fitur **First-Party Laravel AI SDK** di Laravel 13 untuk mendefinisikan agent penerjemah yang menjaga keutuhan struktur Markdown:

```php
<?php

namespace App\Ai\Agents;

use Laravel\Ai\Agent;

class ManuscriptTranslatorAgent extends Agent
{
    public function model(): string
    {
        // Menggunakan Gemini 3.8 Flash (default cepat & akurat) atau Gemini 4 Argon dari config
        return config('services.ai.translation_model', 'gemini-3.8-flash');
    }

    public function instructions(): string
    {
        return <<<'PROMPT'
You are an executive leadership book translator for crow.asia.
Translate the provided GitHub Flavored Markdown chapter into the requested target language with a natural, practical, tactical workplace tone.

STRICT INVARIANTS (NEVER VIOLATE):
1. Preserve all Markdown headings (##, ###, ####), GFM tables, callouts (> [!NOTE], > [!TIP], > [!IMPORTANT], > [!WARNING]), and <details>/<summary> tags exactly as structured.
2. NEVER translate or alter Mermaid diagram syntax keywords (e.g., flowchart, subgraph, -->), only translate the human-readable node labels inside quotes.
3. Preserve all footnote markers ([^1], [^2]) and their bottom definitions.
4. Keep the exact placeholder token [Nama] unchanged so the DOM script personalizer can bind to it.
5. Output ONLY the translated raw Markdown without wrapping it in outer ```markdown code fences.
PROMPT;
    }
}
```

### 4.2 Background Queue Job (`TranslateChapterWithLlmJob.php`) dengan PHP Attributes Laravel 13

```php
<?php

namespace App\Jobs;

use App\Ai\Agents\ManuscriptTranslatorAgent;
use App\Enums\ContentRegister;
use App\Enums\TranslationStatus;
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
        // Ambil sumber dari Bahasa Indonesia Standar atau Bahasa Inggris sebagai acuan dasar
        $source = ChapterTranslation::query()
            ->where('chapter_id', $this->chapterId)
            ->whereIn('locale', ['en', 'id'])
            ->where('register', ContentRegister::STANDARD->value)
            ->orderByRaw("CASE WHEN locale = 'en' THEN 1 ELSE 2 END")
            ->firstOrFail();

        $prompt = "Target Locale Code: {$this->targetLocale}\n\n"
                . "Translate Title: {$source->title}\n\n"
                . "Translate Markdown Body:\n{$source->raw_markdown}";

        $translatedMarkdown = (string) ManuscriptTranslatorAgent::make()->prompt($prompt);

        // Ekstrak baris judul pertama jika ada atau gunakan judul terjemahan
        $lines = explode("\n", trim($translatedMarkdown));
        $translatedTitle = $source->title;
        if (isset($lines[0]) && str_starts_with($lines[0], '## ')) {
            $translatedTitle = trim(Str::after($lines[0], '## '));
        }

        $compiled = $compiler->compile(
            rawMarkdown: $translatedMarkdown,
            title: $translatedTitle,
            locale: $this->targetLocale,
            register: ContentRegister::STANDARD->value,
        );

        ChapterTranslation::query()->updateOrCreate(
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
    }
}
```

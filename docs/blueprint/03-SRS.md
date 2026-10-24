---
document: 03-SRS
project: crow.asia
version: 1.0.0-greenfield
date: 2026-10-24
stack: PHP 8.5, Laravel 13, Filament v5 (Livewire v4), Inertia.js v3 (React 19.3 + TS 6.0 + Vite 7), Tailwind CSS v4.3, Laravel Octane (FrankenPHP 1.13), PostgreSQL 18 + pgvector, Redis 8, @remotion/player v4.0
summary: Complete Functional and Non-Functional Software Requirements Specification for crow.asia.
---

# Software Requirements Specification (SRS): `crow.asia` v1.0

| Atribut Dokumen | Detail |
| :--- | :--- |
| **Nama Sistem** | **`crow.asia`** — Tactical Leadership Field Playbook Platform |
| **Versi Dokumen** | 1.0.0 (Greenfield Specification — 24 Oktober 2026) |
| **Acuan Arsitektur** | Pemenang `02-ADR.md`: **PHP 8.5 + Laravel 13 + Filament v5 + Inertia.js v3 (React 19.3) + Octane (FrankenPHP 1.13) + PostgreSQL 18 + Redis 8** |

---

## 1. Gambaran Umum & Karakteristik Pengguna

| Kelas Pengguna | Hak Akses & Perilaku Sistem |
| :--- | :--- |
| **1. Guest (Pengunjung Publik)** | Mengakses Landing Page Full-Width, mengikuti Kuis Diagnosis 45 Detik, membaca penuh **Bab 0 (`ch-0`) & Bab 1 (`ch-1`)** di semua bahasa (`ID` Kasual/Standar & `EN`), melihat *safe preview* (3 paragraf pembuka) pada Bab 2–14, serta melakukan *Guest Checkout* via Mayar.id. |
| **2. Entitled Reader (Pembeli Resmi)** | Pengguna yang telah membeli paket buku satuan atau bundel. Memiliki akses penuh ke seluruh 15 bab (Mode Baca & Mode Animasi 16:9) di seluruh bahasa & register, sinkronisasi *checklist* ke cloud, serta akses unduh Master PDF A5 dari Cloudflare R2. |
| **3. Admin / Editor (`crow.asia` Team)** | Mengakses `/admin` (Filament v5) untuk mengelola buku, bab, *step* animasi Remotion 16:9, *toggle* YouTube opsional, menjalankan *LLM Asian Translation Jobs* via Laravel 13 AI SDK, mengatur paket harga, dan memberikan akses buku manual (*1-Click Grant Access*). |

---

## 2. Kebutuhan Fungsional (Functional Requirements)

### 2.1 Modul Ingesti 9 File Markdown & Kompilasi Konten (`FR-INGEST`)

1. **`FR-INGEST-01` (Mapping 9 File `.md` ke Dimensi `locale` & `register`)**:
   - Command `php artisan crow:seed-manuscripts` wajib memetakan ke-9 file modal awal ke dalam tabel `books`, `chapters`, dan `chapter_translations` dengan pemetaan berikut:
     - `books/id/casual/book-{1,2,3}.md` $ightarrow$ `locale = 'id'`, `register = 'casual'`
     - `books/id/standar/book-{1,2,3}.md` $ightarrow$ `locale = 'id'`, `register = 'standard'`
     - `books/en/book-{1,2,3}.md` $ightarrow$ `locale = 'en'`, `register = 'standard'`
2. **`FR-INGEST-02` (Chapter Splitting & Self-Contained Footnotes)**:
   - Setiap file `.md` dipecah berdasarkan heading `## ` menjadi tepat **15 bab (`chapter_index 0..14`, `slug: ch-0..ch-14`)**, mengabaikan `## Daftar Isi` / `## Table of Contents`, menggabungkan sub-seksi `Rujukan Riset` / `Primary Research Citations` ke dalam `ch-14` (Lampiran/Appendix), dan menyuntikkan definisi catatan kaki (`[^n]: ...`) yang terpakai ke masing-masing bab.
3. **`FR-INGEST-03` (Compile-on-Write HTML, Sanitasi XSS, Script Token & Remotion Props)**:
   - `ChapterCompilerService` mengkompilasi `raw_markdown` menjadi:
     - `compiled_html`: HTML siap saji untuk *Callouts* (`[!NOTE]`, `[!TIP]`, `[!IMPORTANT]`, `[!WARNING]`, `[!CAUTION]`), tabel GFM, blok lipat `<details>` dengan tombol *Salin Skrip*, serta mengubah setiap placeholder `[Nama]` / `[Teammate Name]` menjadi `<span class="teammate-token" data-default="Budi">Budi</span>`. Seluruh output HTML wajib melewati **Symfony HtmlSanitizer** (whitelist tag & atribut aman) untuk mencegah XSS.
     - `safe_preview_html`: 3 paragraf pertama bab untuk tampilan terkunci (*Paywall*).
     - `extracted_checklists`: Array JSON item latihan `- [ ]`.
     - `remotion_scene_props`: JSON konfigurasi 4 *Step Markers* untuk Mode Animasi 16:9 yang diekstrak otomatis dari heading, poin kerangka kerja, dan skrip dialog bab tersebut.

---

### 2.2 Modul Bahasa (`ID`/`EN` + Asia Expansion) & In-Content Register Switcher (`FR-LOCALE`)

1. **`FR-LOCALE-01` (Global Locale Switcher di Top Navbar)**:
   - Menu bahasa di Navbar utama (`GlobalLocaleMenu.tsx`) mengambil daftar bahasa aktif dari tabel `supported_locales` (`is_active = true`).
   - Pada saat peluncuran awal, terdapat 2 pilihan aktif: **`🇮🇩 ID`** dan **`🌐 EN`**.
   - Pilihan `locale` disimpan di cookie/localStorage (`crow:locale`) dan parameter rute (`/read/{bookId}/{locale}/{chapterSlug}`).
2. **`FR-LOCALE-02` (In-Content Register Switcher Khusus Bahasa Indonesia)**:
   - Ketika `locale === 'id'`, sistem menampilkan **In-Content Register Switcher** (`InContentRegisterToggle.tsx`: `😎 Kasual (Gue/Lo)` | `👔 Standar (Saya/Kamu)`) di dalam *toolbar* konten/Reader (bukan di menu bahasa Navbar).
   - Pilihan `register` disimpan di cookie/localStorage (`crow:id-register`, default `'casual'`) dan query/props Inertia v3 sehingga perpindahan antara gaya *Gue/Lo* dan *Saya/Kamu* berlangsung instan (`< 40ms`) menggunakan `router.visit(..., { preserveScroll: true, preserveState: true })`.
   - Ketika `locale !== 'id'` (misal `'en'`, `'ja'`, `'ms'`), sistem otomatis menetapkan `register = 'standard'` dan menyembunyikan *In-Content Register Switcher*.
3. **`FR-LOCALE-03` (LLM Asian Language Translation Pipeline via Laravel 13 AI SDK)**:
   - Admin dapat mengaktifkan bahasa Asia di tabel `supported_locales` (`ms`, `ja`, `ko`, `zh-CN`, `tl`, `vi`, `th`).
   - Dari halaman `BookResource` atau `ChapterResource` di Filament v5 Admin, Admin dapat menekan tombol **"Generate AI Translation"**. Sistem men-dispatch `TranslateChapterWithLlmJob` ke Laravel Queue (dikonfigurasi dengan `#[Tries(3)]`, `#[Backoff([15, 45, 90])]`, `#[Timeout(180)]`) yang memanggil **Laravel 13 AI SDK (`Laravel\Ai`)** menggunakan model `gemini-3.8-flash` atau `gemini-4-argon`.
   - Prompt sistem wajib menjaga struktur GFM, blok Mermaid, catatan kaki `[^n]`, dan placeholder `[Nama]`, lalu langsung mengkompilasi dan menyimpannya dengan status `REVIEW` sebelum dipublikasikan (`PUBLISHED`) oleh Admin.

---

### 2.3 Modul Layout Desktop Full-Width & Responsivitas (`FR-LAYOUT`)

1. **`FR-LAYOUT-01` (Desktop Full-Width Edge-to-Edge Workspace `>= 1024px`)**:
   - Container utama pada layar desktop wajib menggunakan `w-full max-w-none` (tanpa pembatas sempit `max-w-4xl` di level halaman luar).
   - Pada halaman **Web Reader**, tampilan desktop terbagi menjadi 3 kolom proporsional yang mengisi 100% lebar viewport:
     - **Kolom Kiri (`w-72 xl:w-80 shrink-0`)**: Sidebar daftar bab & pemilih Volume Buku (dapat di-*collapse* / disembunyikan dengan tombol `Cmd/Ctrl + \`).
     - **Kolom Tengah (`flex-1 min-w-0 px-6 xl:px-12`)**: Kanvas utama Mode Baca (teks artikel di tengah dengan lebar baca ergonomis `max-w-[72ch] mx-auto`) / Mode Animasi 16:9 (melebar proporsional memenuhi kolom tengah).
     - **Kolom Kanan (`w-72 xl:w-80 shrink-0`)**: *Tactical Reader Dock* yang memuat *In-Content Register Switcher* (khusus `ID`), *Input Nama Anggota Tim*, *Tema Kertas & Ukuran Huruf*, *Tombol SOS Topik (`Ctrl+K`)*, dan *Progress Bar* latihan bab.
2. **`FR-LAYOUT-02` (Responsivitas Tablet `768px–1023px` & Mobile `< 768px`)**:
   - Pada layar Tablet dan Mobile, Kolom Kiri dan Kolom Kanan disembunyikan dari aliran horizontal dan digantikan oleh *Sticky Top Compact Bar* (berisi *Mode Switcher* & *In-Content Register Pill*) serta *Floating Bottom Navigation Bar* untuk membuka *Chapter List Drawer* dan *Display Settings Sheet*.

---

### 2.4 Modul Dual-Mode Web Reader, Remotion 16:9 & Hybrid Search (`FR-READER`)

1. **`FR-READER-01` (Dual-Mode Toggle)**:
   - Di bagian atas Kolom Tengah Reader terdapat *Segmented Switcher* untuk berpindah secara instan antara:
     - **`📖 Mode Baca Interaktif`** (Scrolling)
     - **`🎬 Mode Animasi (16:9)`** (Interactive Remotion Landscape Player)
2. **`FR-READER-02` (Mode Animasi Landscape 16:9 — `@remotion/player` v4.0)**:
   - Menggunakan [`@remotion/player`](https://www.remotion.dev/docs/player) dengan ukuran komposisi `1280 × 720` (`aspect-video` / `16:9`), `30 FPS`, bukan file video MP4.
   - Mendukung klik **Step Markers** di bawah player untuk melompat ke frame segmen tertentu, kontrol Play/Pause, kecepatan putar (`0.75x`, `1x`, `1.25x`), *Fullscreen Landscape*, serta sinkronisasi langsung dengan `teammateName`, `locale`, dan `register`.
3. **`FR-READER-03` (Modul Video YouTube Opsional — Default Off)**:
   - Kolom `chapters.is_youtube_enabled` bernilai `false` secara default.
   - Hanya jika `is_youtube_enabled === true` dan `youtube_unlisted_id` terisi pada bab tersebut (dan `hasAccess === true`), maka di dalam Mode Animasi muncul tombol opsional **`▶️ Video Penjelasan`** yang memutar video via `OptionalYouTubeShieldPlayer.tsx` (`youtube-nocookie.com` + *Transparent DOM Shield Overlay*).
4. **`FR-READER-04` (SOS Topik `Ctrl+K` — PostgreSQL 18 Hybrid Search)**:
   - Endpoint `GET /api/search?q={query}&locale={locale}&register={register}` menjalankan pencarian gabungan:
     1. Pencarian kata kunci leksikal menggunakan `ts_rank_cd(search_vector, websearch_to_tsquery('simple', :q))`.
     2. Jika ekstensi `pgvector` dan kolom `embedding` terisi, digabungkan dengan kemiripan vektor `whereVectorSimilarTo('embedding', $query)` dari Laravel 13 untuk mengembalikan 8 potongan bab & skrip 1-on-1 paling relevan dalam `< 40ms`.

---

### 2.5 Modul Admin Dashboard Filament v5, Pembayaran Mayar & Unduhan PDF (`FR-ADMIN` & `FR-PAY`)

1. **`FR-ADMIN-01` s/d `06` (Filament v5 Resources)**:
   - Mencakup `BookResource`, `ChapterResource` (menggunakan `Filament\Schemas\Schema` dengan Editor Multi-Bahasa/Register, Live Pre-Compiler, Remotion 16:9 Step Builder, & Toggle YouTube Opsional), `SupportedLocaleResource` (Manajemen Bahasa Asia & Trigger Translasi AI), `PricingTierResource`, `OrderResource` (Log Webhook Mayar & Tombol *Force Approve*), serta `UserResource` (Tombol *1-Click Grant Access*).
2. **`FR-PAY-01` s/d `04` (Mayar Checkout, Webhook Security & Cloudflare R2 PDF)**:
   - Pembuatan invoice via `POST https://api.mayar.id/hl/v2/invoice/create`.
   - Verifikasi webhook di `POST /api/webhooks/mayar` wajib memvalidasi header token rahasia (`X-Callback-Token` / signature Mayar) menggunakan `hash_equals()`, menerapkan *idempotency lock* di Redis 8 agar transaksi `PAID` yang sama tidak diproses ganda, serta dikecualikan secara eksplisit dari middleware `PreventRequestForgery`.
   - *Auto-claim* pesanan tamu berdasarkan kecocokan email terverifikasi saat login, dan unduhan Master PDF A5 dari Cloudflare R2 via *Presigned URL* berdurasi 15 menit.

---

## 3. Kebutuhan Non-Fungsional (Non-Functional Requirements)

| Kode NFR | Kategori | Target Metrik & Spesifikasi Teknis |
| :--- | :--- | :--- |
| **`NFR-PERF-01`** | Latensi Respon Server | Waktu respon JSON/HTML Inertia v3 untuk halaman bab yang sudah di-cache di Redis 8 wajib **`< 25ms` (p95)** di atas Laravel Octane (FrankenPHP 1.13). |
| **`NFR-PERF-02`** | Latensi Personalisasi Skrip | Penggantian nama anggota tim (`.teammate-token`) di DOM wajib selesai dalam **`< 2ms`** tanpa network request atau re-render pohon Markdown. |
| **`NFR-SEC-01`** | Proteksi Konten Terkunci (Paywall) | Ketika `hasAccess === false` (pada Bab 2–14), server **dilarang keras** mengirimkan `compiled_html` penuh, `remotion_scene_props` penuh, maupun `youtube_unlisted_id` di dalam payload JSON Inertia. Server hanya boleh mengirimkan `safe_preview_html` (3 paragraf pembuka). |
| **`NFR-SEC-02`** | Sanitasi HTML & Anti-XSS | Seluruh output `compiled_html` dan `safe_preview_html` wajib melewati `Symfony\Component\HtmlSanitizer\HtmlSanitizer` sebelum disimpan ke database/Redis. |
| **`NFR-A11Y-01`** | Aksesibilitas & Ergonomi | Rasio kontras teks terhadap latar minimal **4.5:1 (WCAG AA)** di semua tema kertas (`Ivory`, `Sepia`, `Obsidian`), ukuran target sentuh minimal `44×44px` di layar mobile, dan dukungan penuh `prefers-reduced-motion: reduce` pada `@remotion/player`. |

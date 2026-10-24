---
document: 00-OPENCODE-MASTER-INDEX
project: crow.asia
version: 1.0.0-greenfield
date: 2026-10-24
purpose: Master Routing Map & Atomic Task Tracker for OpenCode Agents
---

# Master Index & OpenCode Execution Plan (`crow.asia` v1.0 Greenfield)

Selamat datang di cetak biru rekayasa perangkat lunak **`crow.asia`**. Paket dokumentasi ini dirancang agar dapat dieksekusi secara deterministik oleh **OpenCode**—baik menggunakan model berkapasitas besar (**Gemini 3.8 Flash** / **Gemini 4 Argon — 1M Context Window**) maupun model lokal/menengah dengan **Context Window `<200k` token**.

---

## 1. Peta Dokumen & Panduan Penggunaan Berdasarkan Kapasitas Model

| Nama File Dokumen | Isi & Cakupan Spesifikasi | Kapan Harus Dibaca oleh Agent di OpenCode? | Estimasi Token |
| :--- | :--- | :--- | :---: |
| **`AGENTS.md`** | Protokol anti-overflow, larangan `cat books/*.md`, invarian `locale` vs `register` (`casual`/`standard`), dan aturan sintaks stack 2026 (Laravel 13, Filament v5, React 19.3, Inertia v3, Tailwind v4.3, PG 18). | **Wajib dibaca pertama kali** di setiap sesi OpenCode. | ~1.500 |
| **`01-PRD.md`** | Visi produk `crow.asia`, struktur 9 file `.md` modal awal, fitur Dual-Mode Reader, pemisahan menu bahasa vs toggle Kasual/Standar, ekspansi bahasa Asia via LLM, & fitur Admin. | Saat memahami kebutuhan bisnis atau mengerjakan Landing Page & alur produk. | ~3.800 |
| **`02-ADR.md`** | Keputusan arsitektur & matriks pemenang (**PHP 8.5 + Laravel 13 + Filament v5 + Inertia v3 React 19.3 + Octane FrankenPHP 1.13**), hosting (**Railway / Fly.io**), skema `locale` + `register`, dan alasan teknis. | Saat menyiapkan infrastruktur, Docker, atau memvalidasi keputusan teknis. | ~4.400 |
| **`03-SRS.md`** | Spesifikasi kebutuhan fungsional (`FR`) & non-fungsional (`NFR`) detail, termasuk *Laravel 13 AI Translation Pipeline*, *In-Content Register Switcher*, *Hybrid Search*, & keamanan Webhook Mayar. | Saat mengimplementasikan logika backend, validasi, & controller. | ~4.800 |
| **`04-SADoc.md`** | Diagram C4, struktur direktori monorepo `crow.asia`, DDL SQL **PostgreSQL 18 + `pgvector`** lengkap dengan `uuidv7()`, kontrak props Inertia v3, dan arsitektur keamanan. | Saat membuat migration database, model, & struktur monorepo. | ~5.600 |
| **`05-UI-UX-Design-System.md`** | Blueprint UI/UX *Tactile Crow Studio*, spesifikasi **Desktop Full-Width Layout (`100vw`)**, responsivitas Tablet/Mobile, aksesibilitas WCAG AA, dan konfigurasi `@theme` **Tailwind CSS v4.3**. | Saat membangun layout, konfigurasi CSS, dan komponen React 19.3. | ~5.000 |
| **`06-Visual-Illustration-Prompts.md`** | Kumpulan prompt *Text-to-Image* siap pakai (kompatibel dengan **Midjourney v8.1**, **FLUX.2 Pro / FLUX 3**, **Imagen 4 / Nano Banana 2**, **Ideogram 3/4**) untuk maskot, 3 sampul, dan **45 ilustrasi bab**. | Saat men-generate aset gambar/ilustrasi atau menjalankan command generator gambar via `Laravel\Ai\Image`. | ~5.200 |
| **`07-Remotion-Local-LLM-Guide.md`** | Instruksi teknis & *guardrails* khusus LLM untuk membangun animasi React 19.3 [`@remotion/player`](https://www.remotion.dev/docs/player) v4.0 rasio Landscape 16:9 (`1280x720`) bebas bug. | **Wajib dibaca tepat sebelum** menulis kode di `resources/js/Components/remotion/`. | ~4.600 |
| **`08-Manuscript-Parser-And-AI-Pipeline-Spec.md`** | Algoritma konkret & blueprint kode PHP 8.5 untuk `ManuscriptParserService`, `ChapterCompilerService` (HTML sanitizer, footnote resolver, token `[Nama]`, auto-ekstraksi 4 Step Remotion), dan `TranslateChapterWithLlmJob` berbasis **Laravel 13 AI SDK**. | **Wajib dibaca tepat sebelum** mengerjakan `TASK-04` dan `TASK-14`. | ~5.400 |
| **`09-Infrastructure-Security-And-Testing-Runbook.md`** | Blueprint lengkap `Dockerfile` (PHP 8.5 + FrankenPHP 1.13), `compose.yaml` (PG 18 + Redis 8), webhook security Mayar.id, Cloudflare R2 Presigned URL, dan kode pengujian otomatis **Pest 5**. | **Wajib dibaca tepat sebelum** mengerjakan `TASK-12`, `TASK-16`, `TASK-17`, dan `TASK-18`. | ~5.500 |

### Strategi Adaptasi Context Window untuk OpenCode:
- **Mode Model `<200k` Context Window (Hemat Memori)**:
  - Jangan pernah memuat semua file `.md` sekaligus. Ikuti daftar **Atomic Tasks** di bawah secara berurutan, dan hanya baca dokumen yang tercantum pada kolom `Dokumen Acuan yang Dibaca` untuk task yang sedang dikerjakan.
- **Mode Model `1M` Context Window (`gemini-3.8-flash` / `gemini-4-argon`)**:
  - Dapat memuat `AGENTS.md` + beberapa dokumen spesifikasi sekaligus untuk penalaran lintas-modul, namun **tetap dilarang** membaca isi penuh ke-9 file naskah buku di `./books/**/*.md` ke dalam prompt (gunakan CLI parser di `TASK-04`).

---

## 2. Daftar Atomic Tasks Eksekusi Proyek Baru (`TASK-01` s/d `TASK-18`)

Gunakan checklist di bawah ini untuk melacak progres pembangunan `crow.asia` dari nol:

| Kode Task | Status | Deskripsi Pekerjaan Atomic | Dokumen Acuan yang Dibaca | Perintah Verifikasi (`Exit Code 0`) |
| :--- | :---: | :--- | :--- | :--- |
| **`TASK-01`** | `[x]` | Inisialisasi proyek baru **Laravel 13** (PHP 8.5) + **Inertia.js v3** (`@inertiajs/react`, `@inertiajs/vite`, **React 19.3**, **TypeScript 6.0**, **Vite 7**) + **Tailwind CSS v4.3** + **Filament v5** + paket `remotion` & `@remotion/player` (`^4.0`) + **Pest 5**. | `AGENTS.md`, `04-SADoc.md` (§2) | `composer validate && npx tsc --noEmit` |
| **`TASK-02`** | `[x]` | Buat seluruh migrasi database **PostgreSQL 18** (`pgvector` extension, `supported_locales`, `users`, `books`, `chapters`, `chapter_translations` dengan `locale`, `register`, `search_vector` & `embedding`, `pricing_tiers`, `orders`, `user_entitlements`, `checklist_progress`). | `04-SADoc.md` (§3) | `php artisan migrate:fresh` |
| **`TASK-03`** | `[x]` | Buat Eloquent Models lengkap dengan relasi, casts JSON/Enum PHP 8.5 (`ContentRegister`, `PaymentStatus`, `TranslationStatus`), dan helper scopes. | `03-SRS.md` (§2.1), `04-SADoc.md` (§3) | `./vendor/bin/pest --filter=ModelTest` |
| **`TASK-04`** | `[x]` | Bangun `ChapterCompilerService` (termasuk HTML Sanitizer & Remotion 4-Step Extractor), `ManuscriptParserService`, `ChapterCacheService`, serta command `php artisan crow:seed-manuscripts` untuk mengimpor 9 file `./books/**/*.md` ke PostgreSQL 18 & Redis 8. | `08-Manuscript-Parser-And-AI-Pipeline-Spec.md` (§1–§3) | `php artisan crow:seed-manuscripts && php artisan tinker --execute="echo App\Models\ChapterTranslation::count();"` *(Output: 135)* |
| **`TASK-05`** | `[x]` | Konfigurasi Design System **Tailwind CSS v4.3** (`@import "tailwindcss";` & blok `@theme` di `resources/css/app.css`), Font (`Plus Jakarta Sans`, `JetBrains Mono`, `Patrick Hand`), Paper Themes (`Ivory`, `Sepia`, `Obsidian`), dan **Desktop Full-Width App-Shell Layout** (`FullWidthWorkspaceLayout.tsx` & `MarketingFullWidthLayout.tsx`). | `05-UI-UX-Design-System.md` (§1–§5) | `npm run build` |
| **`TASK-06`** | `[x]` | Bangun komponen navigasi global `TopNavbarFullWidth.tsx` yang memuat `GlobalLocaleMenu.tsx` (`ID` \| `EN` + Active Asian Locales) serta komponen `InContentRegisterToggle.tsx` (`Kasual` \| `Standar`) yang hanya tampil saat `locale === 'id'`. | `01-PRD.md` (§2), `05-UI-UX-Design-System.md` (§3) | `npx tsc --noEmit` |
| **`TASK-07`** | `[x]` | Bangun `ReaderController.php` (dengan Paywall Guard & Redis 8 Cache `<25ms`) serta halaman `Reader/ChapterView.tsx` untuk **Mode 1: Baca Interaktif (Scrolling)** + *Live Script Personalizer* (`<2ms` DOM token `.teammate-token`) + *Cloud-Synced Checklists* (`ChecklistController.php` + Inertia v3 optimistic updates). | `03-SRS.md` (§2.4), `04-SADoc.md` (§4) | `./vendor/bin/pest --filter=ReaderAccessTest && npx tsc --noEmit` |
| **`TASK-08`** | `[x]` | Bangun komponen **Mode 2: Animasi Landscape 16:9** (`LandscapeAnimationStage.tsx`, `ChapterInteractiveStory16x9Comp.tsx`, `Framework3KLoop16x9Comp.tsx`, `HeroTrilogy16x9Comp.tsx`) menggunakan `@remotion/player` (`1280x720`, 30 FPS) + *Interactive Step Scrubber*. | `07-Remotion-Local-LLM-Guide.md` (Semua) | `npx tsc --noEmit && npm run build` |
| **`TASK-09`** | `[ ]` | Bangun komponen **Modul Video YouTube Opsional** (`OptionalYouTubeShieldPlayer.tsx`) yang hanya dirender jika `chapter.is_youtube_enabled === true` dan `hasAccess === true`. | `03-SRS.md` (§2.4.3), `04-SADoc.md` (§5) | `npx tsc --noEmit` |
| **`TASK-10`** | `[x]` | Bangun fitur pencarian instan `SOS Topik (Ctrl+K)` (`SearchController.php` + `SosSearchModal.tsx`) berbasis **PostgreSQL 18 Hybrid Search** (`tsvector` GIN + `pgvector` semantic similarity) dan modal `ShareQuoteModal.tsx`. | `03-SRS.md` (§2.4.4), `04-SADoc.md` (§3 & §5) | `./vendor/bin/pest --filter=SearchTest` |
| **`TASK-11`** | `[x]` | Bangun `LandingController.php` dan halaman `Landing.tsx` **Full-Width Desktop** (Hero Remotion 16:9 Showcase, Kuis Diagnosis 45 Detik, Live Script Demo dengan Toggle `Kasual`/`Standar`, Matriks Harga, & FAQ). | `01-PRD.md` (§3), `05-UI-UX-Design-System.md` (§2.2 & §5) | `npx tsc --noEmit && npm run build` |
| **`TASK-12`** | `[x]` | Bangun integrasi pembayaran **Mayar.id** (`CheckoutController`, `MayarWebhookController` dengan verifikasi token & idempotency, `EntitlementSyncService` *Guest Order Auto-Claim*) & pengunduhan PDF A5 **Cloudflare R2** (`DownloadPdfController`) + halaman `/dashboard`. | `03-SRS.md` (§2.5), `09-Infrastructure-Security-And-Testing-Runbook.md` (§2) | `./vendor/bin/pest --filter=MayarWebhookTest` |
| **`TASK-13`** | `[x]` | Bangun **Dashboard Admin Filament v5 (`/admin`)**: `BookResource`, `ChapterResource` (Unified `Schema` Editor MD + Auto Pre-Compile + Remotion 16:9 Step Builder + Toggle YouTube Opsional), `PricingTierResource`, `OrderResource`, & `UserResource` (1-Click Grant Access). | `03-SRS.md` (§2.5), `04-SADoc.md` (§2) | `./vendor/bin/pest --filter=AdminPanelTest` |
| **`TASK-14`** | `[x]` | Bangun **LLM Asian Language Translation Pipeline** (`LlmTranslationService` & `TranslateChapterWithLlmJob` menggunakan **Laravel 13 AI SDK `Laravel\Ai`**) & `SupportedLocaleResource` di Filament v5 untuk menerjemahkan bab ke bahasa Asia (`ms`, `ja`, `ko`, `zh-CN`, `tl`, `vi`, `th`). | `08-Manuscript-Parser-And-AI-Pipeline-Spec.md` (§4) | `./vendor/bin/pest --filter=LlmTranslationTest` |
| **`TASK-15`** | `[x]` | Siapkan command `php artisan crow:generate-illustrations` (menggunakan `Laravel\Ai\Image` / prompt library) dan generator Master PDF A5 dari panduan `06-Visual-Illustration-Prompts.md`. | `06-Visual-Illustration-Prompts.md` | `php artisan list | grep crow:` |
| **`TASK-16`** | `[x]` | Siapkan `Dockerfile` (**PHP 8.5 + FrankenPHP 1.13** worker mode), `compose.yaml` (App, Queue Worker, PostgreSQL 18 + `pgvector`, Redis 8), dan konfigurasi deployment produksi untuk **[Railway](https://railway.com/)** / **[Fly.io](https://fly.io/)**. | `09-Infrastructure-Security-And-Testing-Runbook.md` (§1) | `docker compose config` |
| **`TASK-17`** | `[x]` | Jalankan suite pengujian otomatis lengkap **Pest 5** (Unit, Feature, & Architecture Preset `arch()->preset()->laravel()`) dan pemeriksaan tipe TypeScript 6.0. | `09-Infrastructure-Security-And-Testing-Runbook.md` (§3) | `./vendor/bin/pest && npx tsc --noEmit` |
| **`TASK-18`** | `[x]` | Audit kesiapan produksi akhir: verifikasi build aset Vite 7, pemanasan cache Redis 8 (`php artisan crow:warm-cache`), dan pemeriksaan keamanan endpoint. | `09-Infrastructure-Security-And-Testing-Runbook.md` (§4) | `npm run build && ./vendor/bin/pest` |

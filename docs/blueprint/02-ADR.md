---
document: 02-ADR
project: crow.asia
version: 1.0.0-greenfield
date: 2026-10-24
status: Accepted (Winner: PHP 8.5 + Laravel 13 + Filament v5 + Inertia v3 React 19.3 + Octane FrankenPHP 1.13)
summary: Architecture Decision Record evaluating 2026 Tech Stack, Hosting, Multi-Locale/Register Data Model, and Remotion 16:9 Engine.
---

# Architecture Decision Record (ADR): Proyek Baru `crow.asia` v1.0

| Atribut Dokumen | Detail |
| :--- | :--- |
| **ID Keputusan** | ADR-001 s/d ADR-005 (`crow.asia` Greenfield Edition) |
| **Tanggal** | 24 Oktober 2026 |
| **Status** | **ACCEPTED (Acuan Resmi SRS & SADoc)** |
| **Keputusan Pemenang** | **PHP 8.5 + Laravel 13 + Filament v5 (Livewire v4) + Inertia.js v3 (React 19.3, TypeScript 6.0, Vite 7) + Tailwind CSS v4.3 + Laravel Octane (FrankenPHP 1.13) + PostgreSQL 18 (`pgvector`) + Redis 8** |

---

## ADR-001: Pemilihan Core Tech Stack & Admin CMS untuk `crow.asia` (Oktober 2026)

### 1. Konteks & Kriteria Pemilihan
Kita membangun `crow.asia` dari nol dengan modal 9 file naskah Markdown (`.md`). Arsitektur sistem harus memenuhi 4 tuntutan utama:
1. Menyajikan pengalaman baca dan animasi yang instan (**"Sat Set"**, respon server `< 25ms`).
2. Menyediakan **Dashboard Admin CMS** yang matang untuk mengelola 135+ bab, terjemahan bahasa Asia via AI, konfigurasi animasi, harga, dan transaksi Mayar.id.
3. Mendukung komponen [`@remotion/player`](https://www.remotion.dev/docs/player) v4.0 di atas **React 19.3** untuk **Mode Animasi Landscape 16:9**.
4. Menggunakan versi teknologi yang berstatus **Active LTS / Current Stable per Oktober 2026** (menghindari PHP 8.4 yang masa dukungan aktifnya berakhir Desember 2026 atau Laravel 12 yang masa perbaikan bug-nya berakhir Agustus 2026).

### 2. Perbandingan 3 Kandidat Tech Stack

| Kriteria Evaluasi | Bobot | Kandidat 1: Next.js + Render File MD Statis | Kandidat 2: [PHP Hyperf 3.2](https://github.com/hyperf/hyperf) + Separate React SPA | **Kandidat 3: PHP 8.5 + [Laravel 13](https://laravel.com/framework/docs/releases) + [Filament v5](https://filamentphp.com/) + [Inertia.js v3](https://inertiajs.com/docs/v3/getting-started) (React 19.3) + Octane** |
| :--- | :---: | :---: | :---: | :---: |
| **1. Kecepatan Pembuatan Admin Dashboard** | 25% | 2.0 / 5 *(Buat manual dari nol)* | 1.5 / 5 *(Tidak ada Admin Panel bawaan)* | **5.0 / 5** *(Filament v5 + Livewire v4 Islands siap pakai)* |
| **2. Performa Baca & Navigasi User ("Sat Set")** | 25% | 2.5 / 5 *(Lambat jika parse MD runtime)* | **5.0 / 5** *(Swoole Coroutine `<5ms`)* | **4.9 / 5** *(Octane FrankenPHP 1.13 + Redis 8 `<15ms`)* |
| **3. Dukungan Native [`@remotion/player`](https://www.remotion.dev/docs/player)** | 20% | **5.0 / 5** *(Native React 19)* | 3.0 / 5 *(Harus pisah 2 repo/server)* | **5.0 / 5** *(Native React 19.3 di monorepo via Inertia v3)* |
| **4. Kemudahan Maintenance & AI Queue Jobs** | 15% | 3.5 / 5 *(Terbatas timeout serverless)* | 2.5 / 5 *(2 stack + risiko coroutine state)* | **5.0 / 5** *(First-party **Laravel 13 AI SDK** + Horizon Queue)* |
| **5. Kematangan Ekosistem (Auth, R2, Hybrid Search)** | 15% | 4.0 / 5 | 3.0 / 5 | **5.0 / 5** *(Native Vector Search + PreventRequestForgery)* |
| **TOTAL SKOR TERBOBOT** | **100%** | **3.25 / 5.00** | **2.98 / 5.00** | **🏆 4.98 / 5.00 (PEMENANG MUTLAK)** |

### 3. Alasan Memilih Pemenang (PHP 8.5 + Laravel 13 + Filament v5 + Inertia v3 React 19.3)
1. **Dukungan Jangka Panjang PHP 8.5 & Laravel 13**:
   - **PHP 8.5** (`8.5.12`) memberikan dukungan aktif hingga Desember 2027 dan keamanan hingga Desember 2029.
   - **Laravel 13** (rilis Maret 2026) menyediakan **First-Party Laravel AI SDK (`Laravel\Ai`)**, pencarian vektor/semantik bawaan (`whereVectorSimilarTo`), proteksi CSRF berbasis origin (`PreventRequestForgery`), serta `Queue::route()` dan PHP Attributes (`#[Middleware]`, `#[Tries]`, `#[Timeout]`) untuk orkestrasi job penerjemahan bahasa Asia.
2. **Produktif Sejak Hari Pertama dengan [Filament v5](https://filamentphp.com/)**:
   - Filament v5 berjalan di atas **Livewire v4** (mendukung *Islands* untuk re-render parsial cepat, *Parallel Async Requests*, komponen *Callout* bawaan, dan tabel bertumpuk responsif di layar mobile) serta skema deklaratif `Filament\Schemas\Schema`.
3. **Frontend Modern Tanpa Boilerplate API via [Inertia.js v3](https://inertiajs.com/docs/v3/getting-started) + React 19.3 + Tailwind CSS v4.3**:
   - **Inertia.js v3** menghapus ketergantungan `axios` yang berat dan menggantinya dengan HTTP client internal berkecepatan tinggi, hook `useHttp`, *optimistic updates*, serta plugin `@inertiajs/vite` di atas **Vite 7** dan **TypeScript 6.0**.
   - **React 19.3** dengan **React Compiler 1.0** mengoptimalkan re-render secara otomatis pada kanvas `@remotion/player` v4.0.
   - **Tailwind CSS v4.3** menyederhanakan konfigurasi design system melalui pendekatan *CSS-first* (`@import "tailwindcss";` dan `@theme` langsung di `resources/css/app.css`).

---

## ADR-002: Keputusan Hosting Produksi (Railway / Fly.io / VPS Docker dengan FrankenPHP 1.13)

1. **Mengapa Serverless Statis (Vercel) Tidak Digunakan**:
   - Lingkungan *ephemeral/stateless* mematikan keunggulan *persistent worker* dari **Laravel Octane (FrankenPHP 1.13)** dan tidak dapat menjalankan *background queue worker* berdurasi panjang untuk kompilasi buku serta terjemahan LLM lintas bahasa Asia.
2. **Keputusan Hosting Terpilih**:
   - **Pilihan Utama #1**: **[Railway](https://railway.com/) (Region Singapore `ap-southeast-1`)** — Menjalankan container Docker *always-on* (**FrankenPHP 1.13** dengan peningkatan kecepatan CGO 30% & GC 40% di atas Go 1.26, ditambah Queue Worker), serta layanan **PostgreSQL 18** dan **Redis 8** dalam jaringan privat berlatensi `< 1ms`.
   - **Pilihan Alternatif #2 & #3**: **[Fly.io](https://fly.io/) (Region `sin`)** atau **Cloud VPS Singapore/Jakarta (Hetzner / DigitalOcean / IDCloudHost)** menggunakan Docker Compose.
   - **CDN & Object Storage**: **Cloudflare DNS/CDN + Cloudflare R2** untuk menyimpan file Master PDF A5 dan aset ilustrasi `.webp`.

---

## ADR-003: Pemisahan Dimensi `locale` vs `register` pada Skema Database & UI

### 1. Konteks Kebutuhan `crow.asia`
Platform `crow.asia` memiliki 2 bahasa utama saat ini (`ID` dan `EN`) serta fitur ekspansi ke berbagai bahasa Asia (`ms`, `ja`, `ko`, `zh-CN`, `tl`, `vi`, `th`). Khusus untuk Bahasa Indonesia (`ID`), tersedia 2 variasi gaya sapaan (`Kasual Gue/Lo` dan `Standar Saya/Kamu`) yang harus dipilih oleh pembaca **di dalam area konten web**, bukan di menu bahasa utama.

### 2. Keputusan Desain Data & State (`locale` + `register`)
Kita memisahkan identitas bahasa menjadi **2 kolom independen**:
1. **`locale` (Kode Bahasa ISO — Dipilih di Menu Navbar Utama)**:
   - Nilai aktif awal: `'id'` (Bahasa Indonesia) dan `'en'` (English).
   - Nilai ekspansi Asia: `'ms'`, `'ja'`, `'ko'`, `'zh-CN'`, `'tl'`, `'vi'`, `'th'`.
   - Dikelola melalui tabel database `supported_locales` sehingga Admin bisa mengaktifkan bahasa baru kapan saja.
2. **`register` (Gaya Bahasa di Dalam Konten — Dipilih via In-Content Switcher)**:
   - Nilai yang diizinkan secara ketat di seluruh sistem: `'casual'` | `'standard'` (tidak ada nilai `'default'`).
   - Saat `locale === 'id'`, pembaca dapat memilih `'casual'` (memuat terjemahan dari `books/id/casual/`) atau `'standard'` (memuat terjemahan dari `books/id/standar/`) langsung lewat *toggle* `InContentRegisterToggle.tsx` di dalam halaman baca/konten.
   - Saat `locale !== 'id'` (misal `'en'`, `'ja'`, `'ms'`), sistem otomatis mengunci `register = 'standard'` dan menyembunyikan tombol pemilih Kasual/Standar.
3. **Kunci Unik Database & Redis 8**:
   - Tabel `chapter_translations` memiliki *unique constraint* pada `(chapter_id, locale, register)`.
   - Kunci Redis 8 Cache menggunakan pola: `crow:chapter:v1:{book_id}:{locale}:{register}:{chapter_slug}`.

---

## ADR-004: Strategi Compile-on-Write, Sanitasi HTML & Hybrid Search di PostgreSQL 18

1. **9 File `.md` Sebagai Modal Seed Awal**:
   - Ke-9 file `.md` hanya diparsing saat menjalankan seeder (`php artisan crow:seed-manuscripts`) atau saat Admin mengimpor ulang file `.md`.
2. **Kompilasi & Sanitasi Saat Simpan (`Compile-on-Write`)**:
   - Saat data disimpan, `ChapterCompilerService` mengubah Markdown menjadi HTML, membersihkan potensi injeksi skrip berbahaya melalui HTML Sanitizer (mengizinkan atribut aman seperti `class="teammate-token"` dan `data-default="Budi"`), mengekstrak `extracted_checklists`, membangun 4 *Step Markers* `remotion_scene_props`, serta menyimpannya ke **PostgreSQL 18** dan **Redis 8**.
3. **PostgreSQL 18 `uuidv7()` & Hybrid Search (`tsvector` + `pgvector`)**:
   - Seluruh tabel ber-primary key UUID menggunakan fungsi native `uuidv7()` di PostgreSQL 18 agar penyisipan baris baru terurut secara kronologis dan tidak menyebabkan fragmentasi B-tree index.
   - Fitur **SOS Topik (`Ctrl+K`)** memadukan pencarian leksikal `tsvector` (indeks GIN) dengan pencarian semantik `pgvector` (`embedding vector(768)` yang dihasilkan via `Str::of(...)->toEmbeddings()` di Laravel 13) untuk menemukan skrip kepemimpinan paling relevan bahkan ketika kata kunci yang diketik pembaca berbeda istilah dengan isi buku.

---

## ADR-005: Engine Animasi Landscape 16:9 (`@remotion/player` v4.0 Utama + YouTube Opsional)

1. **Primary Animation Engine**: Menggunakan [`@remotion/player`](https://www.remotion.dev/docs/player) (`^4.0.535`) di atas React 19.3 dengan kanvas **16:9 (`1280×720`, 30 FPS)** yang merender komponen React + SVG secara *real-time* (bukan video MP4), mendukung klik *Step Markers* dan sinkronisasi `teammateName`, `locale`, serta `register`.
2. **Optional YouTube Module**: Disimpan pada kolom `chapters.is_youtube_enabled` (default `false`). Jika diaktifkan oleh Admin pada bab tertentu, barulah muncul sub-tab video tambahan yang menggunakan `OptionalYouTubeShieldPlayer.tsx` (`youtube-nocookie.com` + *DOM Shield Overlay*).

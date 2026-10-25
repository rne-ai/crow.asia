# 00. Indeks & Ringkasan Eksekutif Sistem crow.asia

Dokumen ini merupakan gerbang utama dokumentasi teknis platform **`crow.asia`** (*Tactile Leadership Field Playbook Platform*). Seluruh informasi di dalam seri dokumen ini disusun berdasarkan kondisi arsitektur riil (*as-built*) yang aktif berjalan pada branch `development`.

---

## 1. Ringkasan Eksekutif & Filosofi Proyek

**`crow.asia`** adalah platform publikasi digital interaktif berkecepatan tinggi (*sub-25ms TTFB*) yang memadukan buku panduan kepemimpinan lapangan dengan instrumen aksi kerja nyata. Platform ini dirancang khusus bagi para pemimpin tim, manajer rekayasa (*engineering leads*), kepala operasional cabang, hingga pemilik bisnis multi-tim yang membutuhkan panduan taktis tanpa teori akademis yang mengawang-awang.

### Pilar Utama Platform:
1. **Web-First Interactive Field Playbook:** Naskah buku disajikan secara responsif melalui Web Reader modern berbasis Inertia.js v3 dan React 19, dilengkapi fitur interaktif seperti kotak skrip percakapan empat mata yang dapat disalin dengan satu klik, kanvas cerita 16:9, dan daftar periksa (*checklist*) yang tersinkronisasi ke basis data.
2. **Dual-Register Indonesian Reading Engine:** Satu-satunya platform yang menyediakan dua varian register sapaan bahasa Indonesia dalam naskah yang sama: **Gaya Kasual (`Gue/Lo`)** untuk kultur startup/komunitas sebaya dan **Gaya Standar (`Saya/Kamu`)** untuk kultur korporat formal. Pembaca dapat beralih gaya sapaan secara instan di dalam Web Reader tanpa memuat ulang halaman (*zero page-reload*).
3. **High-Performance Long-Running Runtime:** Berjalan di atas **FrankenPHP (Laravel Octane Worker Mode)** dan **PostgreSQL 18**, mengeliminasi overhead *cold-boot* PHP konvensional dan menghasilkan latensi respon ultra-cepat (< 25ms).
4. **Deterministic Video-as-Code via Remotion:** Seluruh animasi visual sampul buku fisik 3D dan pratinjau cerita diprogram secara terprogram menggunakan Remotion v4, menghasilkan animasi yang presisi, ringan, dan ramah aksesibilitas.
5. **Freemium & Idempotent Commerce:** Pengunjung dapat membaca Pendahuluan dan Bab 1 dari seluruh volume secara cuma-cuma tanpa kartu kredit. Pembelian akses penuh diintegrasikan ke payment gateway Mayar.id dengan validasi token webhook berkeamanan tinggi dan proteksi idempotensi transaksi.

---

## 2. Matriks Teknologi Lengkap (*Tech Stack*)

Berikut adalah daftar teknologi, pustaka, dan infrastruktur yang digunakan beserta peran spesifiknya dalam sistem:

| Layer / Domain | Teknologi / Package | Versi Aktual | Peran & Tanggung Jawab dalam Sistem |
| :--- | :--- | :--- | :--- |
| **Runtime Engine** | [FrankenPHP](https://frankenphp.dev/) | `1.4+` (Caddy) | Web server berkinerja tinggi berbasis Go & worker mode Laravel Octane |
| **Backend Core** | [Laravel Framework](https://laravel.com/) | `13.x` | Fondasi API, Controller, Model Eloquent, dan Routing aplikasi |
| **PHP Runtime** | [PHP](https://www.php.net/) | `8.4 / 8.5` | Bahasa pemrograman server dengan JIT compiler dan typing ketat |
| **Application Server** | [Laravel Octane](https://laravel.com/docs/octane) | `2.x` | Mengelola *worker pool* persistent di memori RAM untuk eksekusi kilat |
| **Primary Database** | [PostgreSQL](https://www.postgresql.org/) | `18-alpine` | Penyimpanan relasional, JSONB multi-locale, UUIDv7 PK, dan TsVector FTS |
| **Vector Extension** | [pgvector](https://github.com/pgvector/pgvector) | `0.8+` | Ekstensi PostgreSQL untuk penyimpanan dan pencarian similarity n-dimensi |
| **Cache & In-Memory** | [Redis](https://redis.io/) | `8-alpine` | Session handler, cache middleware kamus i18n, dan atomic lock |
| **Frontend Adapter** | [Inertia.js](https://inertiajs.com/) | `v3.x` | Jembatan full-stack SSR/SPA tanpa membangun REST API boilerplate |
| **UI Library** | [React](https://react.dev/) | `19.x` | Library antarmuka reaktif berbasis komponen dan hook modern |
| **Styling & Design** | [Tailwind CSS](https://tailwindcss.com/) | `v4.x` | Engine CSS utility-first generasi terbaru dengan performa lightning-fast |
| **Video & Motion** | [Remotion](https://www.remotion.dev/) | `v4.x` | Framework video terprogram React untuk animasi cover buku dan carousel |
| **Admin Panel** | [Filament](https://filamentphp.com/) | `v5.x` | Panel administrasi superadmin untuk manajemen buku, bab, user, dan lisensi |
| **Markdown Parser** | [League CommonMark](https://commonmark.thephpleague.com/) | `v2.6+` | Parser markdown berstandar CommonMark & GFM dengan ekstensi kustom |
| **HTML Sanitizer** | [Mews Purifier](https://github.com/mewebstudio/Purifier) | `v3.4+` | Sanitasi HTML berbasis HTMLPurifier untuk mencegah serangan XSS |
| **Test Suite** | [Pest PHP](https://pestphp.com/) | `v5.x` | Framework pengujian testing elegan berbasis PHPUnit |
| **E2E Automation** | [Playwright](https://playwright.dev/) | `v1.50+` | Audit visual otomatis dan verifikasi cross-browser multi-locale |
| **Payment Gateway** | [Mayar.id](https://mayar.id/) | REST / Webhook | Otomasi tagihan QRIS/VA dan verifikasi webhook pembayaran berlisensi |

---

## 3. Peta Direktori Repositori

Struktur organisasi berkas utama di dalam repositori `crow.asia`:

```text
crow.asia/
├── app/
│   ├── Console/Commands/        # Artisan commands kustom (SeedManuscripts, dll.)
│   ├── Enums/                   # Enum domain (ContentRegister, TranslationStatus, UserRole)
│   ├── Filament/Resources/      # Definisi resource admin panel Filament v5
│   ├── Http/
│   │   ├── Controllers/         # Controller aplikasi (Landing, Reader, Checkout, Mayar)
│   │   └── Middleware/          # Global & route middleware (HandleInertiaRequests, dll.)
│   ├── Models/                  # Model domain Eloquent (Book, Chapter, Order, User, dll.)
│   └── Services/                # Service layer (ManuscriptParserService, ChapterCompilerService)
├── books/                       # Naskah asli buku dalam format Markdown GFM
│   ├── en/                      # Naskah edisi Bahasa Inggris (book-1, book-2, book-3)
│   └── id/
│       ├── casual/              # Naskah Bahasa Indonesia varian Kasual (Gue/Lo)
│       └── standar/             # Naskah Bahasa Indonesia varian Standar (Saya/Kamu)
├── database/
│   ├── migrations/              # Skema tabel PostgreSQL
│   └── seeders/                 # Seeder inisialisasi basis data
├── docs/
│   ├── blueprint/               # Dokumen spesifikasi awal pra-implementasi (greenfield)
│   ├── superpowers/             # Riwayat perancangan dan rencana kerja pengembangan
│   └── technical/               # Dokumentasi teknis modular komprehensif (Modul 00–07)
├── lang/
│   ├── id.json                  # Kamus terpusat seluruh antarmuka Bahasa Indonesia
│   └── en.json                  # Kamus terpusat seluruh antarmuka Bahasa Inggris
├── public/
│   ├── fonts/crow/              # Font tipografi lokal (Newsreader, JetBrains Mono, Sans)
│   └── illustrations/           # Aset visual papercraft landscape.jpeg dan portrait.png
├── resources/
│   ├── css/                     # app.css (Tailwind v4, token tema kertas, animasi SVG)
│   └── js/
│       ├── Components/
│       │   ├── landing/         # Komponen landing page (LandscapeImageCarousel16x9, dll.)
│       │   ├── navigation/      # Navigasi global & dropdown bahasa (GlobalLocaleMenu)
│       │   ├── reader/          # Komponen reader (RemotionBookCoverPlayer, SosSearchModal)
│       │   └── remotion/        # Komposisi video Remotion (BookCover, HeroTrilogy, Story)
│       ├── Layouts/             # Layout dasar (MarketingFullWidthLayout, FullWidthWorkspaceLayout)
│       ├── Pages/               # Halaman utama Inertia (Landing, Checkout, Dashboard, Reader)
│       └── hooks/               # Custom React hooks (useTranslation)
├── routes/
│   ├── web.php                  # Rute antarmuka Web & Reader
│   ├── api.php                  # Rute API (Checklist toggle, Mayar Webhook)
│   └── console.php              # Rute konsol terjadwal
├── tests/
│   ├── Feature/                 # Pengujian fitur end-to-end Pest 5 (Commerce, Reader, i18n)
│   └── Unit/                    # Pengujian unit parser, compiler, arsitektur, dan model
├── compose.yaml                 # Orkestrasi Docker multi-container (FrankenPHP, Postgres, Redis)
└── README.md                    # Panduan cepat instalasi dan gateway teknis repositori
```

---

## 4. Peta Modul Dokumentasi Teknis

Dokumentasi teknis ini dibagi ke dalam 8 modul terpisah yang saling melengkapi:

1. [**Modul 00: Indeks & Ringkasan Eksekutif Sistem**](./00-index-and-overview.md) *(Dokumen Ini)*  
   *Ringkasan arsitektur, matriks teknologi lengkap, peta direktori, dan prinsip rekayasa sistem.*
2. [**Modul 01: Arsitektur Sistem & Runtime Environment**](./01-architecture-and-runtime.md)  
   *Diagram C4, FrankenPHP worker mode, lifecycle request Octane, dan pipeline middleware Inertia v3.*
3. [**Modul 02: Skema Basis Data & Model Domain**](./02-database-schema-and-models.md)  
   *Diagram ERD PostgreSQL 18, UUIDv7, JSONB multi-locale, FTS TsVector, dan policy akses MCP dbx.*
4. [**Modul 03: Pipeline Pengolahan Naskah & Compiler**](./03-manuscript-pipeline-and-compiler.md)  
   *Alur kompilasi Markdown ke HTML aman, callout script box, ekstraksi checklist, dan seeder artisan.*
5. [**Modul 04: Mesin Multibahasa Dinamis & Sistem Dual-Register**](./04-i18n-and-multilingual-engine.md)  
   *Kamus JSON terpusat, hook useTranslation, dual-register Kasual/Standar di Reader, dan mata uang ganda.*
6. [**Modul 05: Antarmuka Web Reader & Engine Video Remotion**](./05-reader-ui-and-remotion-engine.md)  
   *Tata letak 3 kolom reader, collapsible footnotes, sync checklist, dan arsitektur Remotion v4.*
7. [**Modul 06: Sistem Autentikasi, Commerce & Integrasi Mayar**](./06-commerce-auth-and-entitlements.md)  
   *Model hak akses, paywall content masking, otentikasi Filament v5, alur checkout, dan idempotensi webhook.*
8. [**Modul 07: Operasional, Pengujian & Runbook Pemeliharaan**](./07-operations-testing-and-runbook.md)  
   *Konfigurasi Docker multi-container, cheatsheet perintah artisan, strategi Pest 5, dan panduan troubleshooting.*

---

## 5. Prinsip Desain & Rekayasa Sistem (*Engineering Disciplines*)

Seluruh pengembangan di dalam repositori ini tunduk pada aturan ketat yang terdokumentasi dalam `AGENTS.md`:

- **Disiplin Koding Ponytail (Level Full):** Tulis kode seminimal mungkin yang menyelesaikan masalah secara utuh tanpa abstraksi prematur, wrapper yang tidak perlu, atau boilerplate "untuk nanti". Deletion beats addition.
- **Standar Antislop UI/UX (Mode During):** Antarmuka bebas dari klise visual AI generik (tidak ada gradasi ungu klise, neon glow, atau blob 3D murahan). Tunduk pada palet resmi *Tactile Crow Studio* (`--color-crow-ink`, `--color-crow-ivory`, `--color-crow-border`, `--color-crow-gold`), memenuhi standar kontras WCAG AA (minimal 4.5:1), dan kontrol keyboard yang aksesibel.
- **As-Built Truth:** Dokumentasi dan kode harus mencerminkan kondisi nyata repositori. Tidak ada worker antrean background fiktif; seluruh pemrosesan antrean diatur sinkron (`QUEUE_CONNECTION=sync`).
- **Integritas Basis Data via MCP DBX:** Seluruh inspeksi skema dan verifikasi data wajib menggunakan tools MCP `dbx` resmi, menghindari akses terminal ad-hoc yang tidak terisolasi.

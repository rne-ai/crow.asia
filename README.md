# 🦅 crow.asia — Pan-Asian Interactive Leadership Field Playbook

[![Laravel 13](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![FrankenPHP](https://img.shields.io/badge/FrankenPHP-Octane-blue?style=for-the-badge&logo=caddy&logoColor=white)](https://frankenphp.dev)
[![React 19](https://img.shields.io/badge/React-19.x-61DAFB?style=for-the-badge&logo=react&logoColor=black)](https://react.dev)
[![Inertia.js v3](https://img.shields.io/badge/Inertia.js-v3.x-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com)
[![Tailwind CSS v4](https://img.shields.io/badge/Tailwind_CSS-v4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![PostgreSQL 18](https://img.shields.io/badge/PostgreSQL-18_pgvector-336791?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org)
[![Remotion v4](https://img.shields.io/badge/Remotion-v4.x-FF0055?style=for-the-badge&logo=remotion&logoColor=white)](https://remotion.dev)
[![Pest 5](https://img.shields.io/badge/Pest_PHP-41_Passed-059669?style=for-the-badge&logo=pest&logoColor=white)](https://pestphp.com)

**`crow.asia`** adalah platform buku panduan kepemimpinan lapangan interaktif (*Tactile Leadership Field Playbook Platform*) berkecepatan tinggi (*sub-25ms TTFB*) yang memadukan buku panduan kepemimpinan praktis dengan instrumen aksi kerja nyata. Platform ini mencakup 3 volume buku kepemimpinan taktis:

1. **VOL. 01:** *Mahir Memimpin Tim* (Strategi Menciptakan Tim yang Mandiri & Bisa Diandalkan).
2. **VOL. 02:** *Napas Panjang Memimpin Tim* (Strategi Mengatasi Kejenuhan & Membangun Sistem Kerja Mandiri).
3. **VOL. 03:** *Memimpin Para Leader* (Strategi Naik Kelas dari Mengarahkan Tim ke Menggerakkan Divisi & Cabang).

---

## 🚀 Fitur Unggulan Platform

- **Dual-Register Indonesian Reading Engine:** Pembaca dapat beralih antara gaya **Kasual (`Gue/Lo`)** dan **Standar (`Saya/Kamu`)** secara instan di dalam Web Reader tanpa memuat ulang halaman (*zero page-reload*).
- **High-Performance In-Memory Runtime:** Berjalan di atas **FrankenPHP (Laravel Octane Worker Mode)**, memangkas overhead framework boot dan menyajikan halaman di bawah 25ms.
- **Freemium Reading Funnel:** Pendahuluan dan Bab 1 dari seluruh 3 buku terbuka gratis (*free preview*). Pembelian berbayar membuka kunci 12 bab lanjutan, simulasi kasus, rencana praktik 30 hari, dan lampiran ringkasan meja kerja.
- **Video-as-Code via Remotion v4:** Sampul buku fisik 3D tertutup beranimasi dinamis saat kursor mouse diarahkan (*hover-driven motion*), carousel 16:9 di halaman depan, dan kanvas cerita interaktif.
- **Instrumen Aksi Lapangan:** Kotak skrip percakapan 1-on-1 siap salin satu klik, diagram alur kerja Mermaid interaktif, laci catatan kaki tersembunyi (*collapsible footnotes drawer*), dan checklist cloud yang tersinkronisasi.
- **Commerce & Webhook Idempotency:** Integrasi gateway pembayaran Mayar.id dengan verifikasi token header HMAC yang aman dan pencegahan duplikasi pemberian hak akses (*zero duplicate entitlement*).

---

## ⚡ Panduan Instalasi Cepat (*Quickstart*)

### 1. Prasyarat Lingkungan
- PHP `8.4+` (CLI) & Composer `2.8+`
- Node.js `20+` & npm `10+`
- Docker Engine & Docker Compose v2
- PostgreSQL 18 lokal (atau container Docker)

### 2. Langkah Menjalankan Aplikasi
```bash
# Clone repositori
git clone https://github.com/rne-ai/crow.asia.git
cd crow.asia

# Pasang dependensi
composer install
npm install

# Konfigurasi environment
cp .env.example .env
php artisan key:generate

# Jalankan migrasi basis data
php artisan migrate

# Kompilasi dan seeding naskah lengkap (135 bab terjemahan)
php artisan crow:seed-manuscripts

# Kompilasi aset frontend
npm run build

# Jalankan local development server
php artisan serve --host=127.0.0.1 --port=8000
```

Buka peramban di `http://127.0.0.1:8000`.

---

## 🔑 Kredensial Pengujian Bawaan

| Akun Pengguna | Email | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@crow.asia` | `janganmasuk` | Akses panel `/admin` Filament & akses penuh seluruh bab berbayar |
| **Pembaca Resmi** | `mharryrosyan@gmail.com` | `janganmasuk` | Hak akses penuh berlisensi ke Buku 1, Buku 2, dan Buku 3 |

---

## 📚 Indeks Dokumentasi Teknis Lengkap (`docs/technical/`)

Dokumentasi arsitektur sistem yang mendalam telah disusun secara modular di dalam direktori `docs/technical/`:

1. [**00. Indeks & Ringkasan Eksekutif Sistem**](docs/technical/00-index-and-overview.md)  
   *Ringkasan arsitektur, filosofi Tactile Field Playbook, matriks teknologi lengkap, peta direktori repositori, dan prinsip rekayasa sistem.*
2. [**01. Arsitektur Sistem & Runtime Environment**](docs/technical/01-architecture-and-runtime.md)  
   *Diagram C4 Container, mekanisme FrankenPHP Octane worker mode, integrasi hybrid Inertia v3 & React 19, middleware pipeline, dan siklus hidup request.*
3. [**02. Skema Basis Data & Model Domain**](docs/technical/02-database-schema-and-models.md)  
   *Diagram ERD PostgreSQL 18 lengkap, tipe data UUIDv7, atribut JSONB multi-locale, indeks TsVector FTS, ekstensi pgvector, dan tata kelola basis data via MCP dbx.*
4. [**03. Pipeline Pengolahan Naskah & Kompiler**](docs/technical/03-manuscript-pipeline-and-compiler.md)  
   *Struktur naskah Markdown GFM, parsing AST bab oleh ManuscriptParserService, sanitasi HTML dan transformasi widget taktis oleh ChapterCompilerService, dan seeder artisan.*
5. [**04. Mesin Multibahasa Dinamis & Sistem Dual-Register**](docs/technical/04-i18n-and-multilingual-engine.md)  
   *Arsitektur kamus JSON terpusat (`lang/id.json` & `lang/en.json`), custom hook useTranslation, pembatasan register Kasual vs Standar di Web Reader, dan dual-currency engine.*
6. [**05. Antarmuka Web Reader & Engine Video Remotion**](docs/technical/05-reader-ui-and-remotion-engine.md)  
   *Desain ergonomis 3-kolom Web Reader, collapsible footnotes drawer, sinkronisasi checklist cloud, arsitektur Remotion v4 (Hero carousel, 3D closed-book cover, scene props), dan kepatuhan WCAG AA.*
7. [**06. Sistem Autentikasi, Commerce & Integrasi Mayar**](docs/technical/06-commerce-auth-and-entitlements.md)  
   *Autentikasi sesi Redis, otorisasi panel Filament v5 superadmin, proteksi konten berbayar (paywall masking), inisiasi checkout Mayar, dan idempotensi webhook callback.*
8. [**07. Operasional, Pengujian & Runbook Pemeliharaan**](docs/technical/07-operations-testing-and-runbook.md)  
   *Topologi Docker multi-container, cheatsheet perintah artisan, strategi suite pengujian Pest 5 (41+ tests green), backup & pemulihan database, dan troubleshooting produksi.*

---

## 🧪 Pengujian Sistem (*Testing*)

Jalankan pengujian otomatis menggunakan Pest PHP:

```bash
# Menjalankan seluruh test suite
./vendor/bin/pest

# Menjalankan pengujian spesifik
./vendor/bin/pest tests/Feature/ReaderAccessTest.php
./vendor/bin/pest tests/Feature/MayarWebhookTest.php
```

---

## ⚖️ Lisensi & Hak Cipta

© 2026 **crow.asia**. Seluruh hak cipta dilindungi undang-undang.  
Didesain dan dikembangkan dengan standar keahlian tinggi (*Craftsmanship Over Cliché*).

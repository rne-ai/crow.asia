---
document: 09-Infrastructure-Security-And-Testing-Runbook
project: crow.asia
version: 1.0.0-greenfield
date: 2026-10-24
stack: Docker, PHP 8.5, FrankenPHP 1.13, Laravel 13, PostgreSQL 18 + pgvector, Redis 8, Mayar.id Webhook, Cloudflare R2, Pest 5
purpose: Production Infrastructure Blueprints (Dockerfile, compose.yaml), Webhook & Paywall Security Implementation, and Automated Pest 5 Verification Suite.
---

# Runbook Infrastruktur, Keamanan & Pengujian Otomatis (`crow.asia` v1.0)

> **ACUAN WAJIB UNTUK `TASK-12`, `TASK-16`, `TASK-17`, DAN `TASK-18` DI OPENCODE:**
> Dokumen ini berisi konfigurasi siap pakai untuk container produksi **PHP 8.5 + FrankenPHP 1.13**, **PostgreSQL 18 (`pgvector`)**, **Redis 8**, pengamanan webhook **Mayar.id**, pengunduhan **Cloudflare R2**, serta spesifikasi pengujian **Pest 5**.

---

## 1. Blueprint Kontainer Produksi (`Dockerfile` & `compose.yaml`)

### 1.1 `Dockerfile` (Multi-Stage Build: Node 24 / Vite 7 + PHP 8.5 FrankenPHP 1.13)

```dockerfile
# Stage 1: Build Frontend Assets (React 19.3 + Tailwind CSS v4.3 + Remotion v4.0)
FROM node:24-alpine AS frontend-builder
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci
COPY resources/ ./resources/
COPY public/ ./public/
COPY vite.config.ts tsconfig.json ./
RUN npm run build

# Stage 2: Production Runtime (FrankenPHP 1.13 + PHP 8.5 Worker Mode)
FROM dunglas/frankenphp:1.13-php8.5-alpine

RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    redis \
    intl \
    zip \
    opcache \
    pcntl \
    bcmath

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-autoloader --no-scripts --prefer-dist

COPY . .
COPY --from=frontend-builder /app/public/build ./public/build

RUN composer dump-autoload --optimize --classmap-authoritative \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache

ENV SERVER_NAME=":8000"
ENV OCTANE_SERVER="frankenphp"
EXPOSE 8000

CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000", "--workers=4", "--max-requests=1000"]
```

### 1.2 `compose.yaml` (PostgreSQL 18 + `pgvector`, Redis 8, Octane App & Queue Worker)

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8000:8000"
    environment:
      APP_ENV: production
      OCTANE_SERVER: frankenphp
      DB_CONNECTION: pgsql
      DB_HOST: postgres
      DB_PORT: 5432
      DB_DATABASE: crow_asia
      DB_USERNAME: crow
      DB_PASSWORD: ${DB_PASSWORD:-secret}
      REDIS_HOST: redis
      REDIS_PORT: 6379
      CACHE_STORE: redis
      QUEUE_CONNECTION: redis
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    restart: unless-stopped

  queue:
    build:
      context: .
      dockerfile: Dockerfile
    command: ["php", "artisan", "queue:work", "redis", "--queue=default,translations", "--tries=3", "--timeout=180"]
    environment:
      APP_ENV: production
      DB_CONNECTION: pgsql
      DB_HOST: postgres
      DB_PORT: 5432
      DB_DATABASE: crow_asia
      DB_USERNAME: crow
      DB_PASSWORD: ${DB_PASSWORD:-secret}
      REDIS_HOST: redis
      REDIS_PORT: 6379
      CACHE_STORE: redis
      QUEUE_CONNECTION: redis
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    restart: unless-stopped

  postgres:
    image: pgvector/pgvector:pg18
    environment:
      POSTGRES_DB: crow_asia
      POSTGRES_USER: crow
      POSTGRES_PASSWORD: ${DB_PASSWORD:-secret}
    ports:
      - "5432:5432"
    volumes:
      - pgdata:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U crow -d crow_asia"]
      interval: 5s
      timeout: 5s
      retries: 5
    restart: unless-stopped

  redis:
    image: redis:8.2-alpine
    command: ["redis-server", "--appendonly", "yes", "--maxmemory", "512mb", "--maxmemory-policy", "allkeys-lru"]
    ports:
      - "6379:6379"
    volumes:
      - redisdata:/data
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 5s
      timeout: 3s
      retries: 5
    restart: unless-stopped

volumes:
  pgdata:
  redisdata:
```

---

## 2. Keamanan Webhook Mayar.id, Idempotency & Cloudflare R2 Presigned URL (`TASK-12`)

### 2.1 Pengecualian `PreventRequestForgery` di `bootstrap/app.php` (Laravel 13)
Pastikan endpoint webhook Mayar dikecualikan dari pemeriksaan CSRF origin/token bawaan Laravel 13:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'api/webhooks/mayar',
    ]);
})
```

### 2.2 Verifikasi Signature/Token & Idempotency Lock di `MayarWebhookController.php`
1. **Validasi Token Konstan-Waktu (`hash_equals`)**:
   - Bandingkan header `X-Callback-Token` dari request dengan `config('services.mayar.webhook_token')` menggunakan `hash_equals()`. Jika tidak cocok, kembalikan `HTTP 401 Unauthorized`.
2. **Idempotency Lock via Redis 8 (`Cache::lock`)**:
   - Gunakan `Cache::lock("mayar:webhook:{$orderNumber}", 15)` dan periksa apakah `order->payment_status === PaymentStatus::PAID`. Jika sudah `PAID`, langsung kembalikan `HTTP 200 OK` tanpa menduplikasi `user_entitlements`.
3. **Auto-Claim Guest Order (`EntitlementSyncService.php`)**:
   - Jika pembeli melakukan checkout sebagai tamu (`user_id === null`), sistem membuat atau menautkan akun berdasarkan `customer_email`, lalu memberikan baris `user_entitlements` untuk setiap buku di dalam `pricing_tier->included_book_ids` (`["book-1"]` atau `["book-1", "book-2", "book-3"]`).

### 2.3 Proteksi Unduhan Master PDF A5 (`DownloadPdfController.php`)
- Memeriksa apakah pengguna yang sedang login memiliki baris aktif di `user_entitlements` untuk `book_id` yang diminta.
- Jika berhak, panggil `Storage::disk('r2')->temporaryUrl($r2Key, now()->addMinutes(15))` dan redirect pengguna ke *Presigned URL* Cloudflare R2 tersebut.

---

## 3. Spesifikasi Suite Pengujian Otomatis **Pest 5** (`TASK-03` s/d `TASK-17`)

Seluruh perintah verifikasi di `00-OPENCODE-MASTER-INDEX.md` dijalankan menggunakan **Pest 5** (`./vendor/bin/pest`). Berikut adalah cakupan wajib untuk setiap file test:

| Nama File Test | Asersi Wajib yang Diuji |
| :--- | :--- |
| **`tests/Unit/ArchitectureTest.php`** | Menjalankan `arch()->preset()->laravel()`, memastikan tidak ada penggunaan `dd()`, `dump()`, `env()` di luar file config, dan memastikan Enum berada di `App\Enums`. |
| **`tests/Feature/ModelTest.php`** | Menguji relasi `Book` $ightarrow$ `Chapter` $ightarrow$ `ChapterTranslation`, memastikan constraint `register IN ('casual', 'standard')` bekerja, dan memastikan UUID dibuat dengan `uuidv7()`. |
| **`tests/Feature/ReaderAccessTest.php`** | 1) Memastikan Guest dapat membaca penuh `ch-0` & `ch-1` (`hasAccess === true`).<br/>2) Memastikan Guest yang mengakses `ch-2` s/d `ch-14` hanya menerima `safe_preview_html` (`hasAccess === false`) dan tidak menerima `youtube_unlisted_id`.<br/>3) Memastikan saat `locale === 'id'` flag `showRegisterSwitcher === true`, dan saat `locale === 'en'` flag `showRegisterSwitcher === false` serta `activeRegister === 'standard'`. |
| **`tests/Feature/SearchTest.php`** | Menguji endpoint `/api/search?q=Rumus+3K&locale=id&register=casual` mengembalikan hasil bab yang sesuai dari indeks `search_vector` PostgreSQL 18. |
| **`tests/Feature/MayarWebhookTest.php`** | 1) Menolak webhook dengan `X-Callback-Token` salah (`401`).<br/>2) Memproses webhook `PAID` untuk `bundle-all-3` dan membuat tepat 3 baris `user_entitlements` (`book-1`, `book-2`, `book-3`).<br/>3) Menguji *idempotency* saat payload webhook yang sama dikirim 2 kali berturut-turut. |
| **`tests/Feature/AdminPanelTest.php`** | Menguji akses `/admin` ditolak untuk `role === 'reader'` (`403`) dan diizinkan untuk `role === 'admin'` (`200`), serta menguji fitur *1-Click Grant Access* pada `UserResource`. |
| **`tests/Feature/LlmTranslationTest.php`** | Memalsukan respons AI (`ManuscriptTranslatorAgent::fake(...)`) dan memastikan `TranslateChapterWithLlmJob` menyimpan terjemahan baru dengan `register = 'standard'`, mempertahankan `<span class="teammate-token">`, dan berstatus `REVIEW`. |

---

## 4. Checklist Kesiapan Produksi Akhir (`TASK-18`)

Sebelum menandai proyek selesai, jalankan urutan verifikasi berikut dan pastikan seluruhnya menghasilkan `Exit Code 0`:
1. `php artisan migrate:fresh && php artisan crow:seed-manuscripts` (Memastikan 135 bab terkompilasi ke PostgreSQL 18 & Redis 8).
2. `npx tsc --noEmit` (Memastikan 0 error tipe pada TypeScript 6.0 & React 19.3).
3. `npm run build` (Memastikan bundle Vite 7 & Tailwind CSS v4.3 sukses dibangun).
4. `./vendor/bin/pest` (Memastikan seluruh Feature, Unit, dan Architecture test lulus 100%).

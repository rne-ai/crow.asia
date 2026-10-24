---
project: crow.asia
version: 1.0.0-greenfield
date: 2026-10-24
target_agent: OpenCode (Local LLM / Gemini 3.8 Flash / Gemini 4 Argon / <200k Context Models)
stack: PHP 8.5, Laravel 13, Filament v5 (Livewire v4), Inertia.js v3 (React 19.3 + TypeScript 6.0 + Vite 7), Tailwind CSS v4.3, Laravel Octane (FrankenPHP 1.13), PostgreSQL 18 + pgvector, Redis 8, @remotion/player v4.0, Pest 5
---

# AGENTS.md — Operational Guardrails for `crow.asia` (OpenCode Agent Edition)

> **CRITICAL FOR OPENCODE AGENTS (BOTH `<200k` AND `1M` CONTEXT MODELS):**
> Read this file FIRST before executing any command or reading any other specification document. This repository uses **Progressive Context Loading** so that models with `<200k` token context windows never suffer from context truncation, while `1M` token models (`gemini-3.8-flash` or `gemini-4-argon`) maintain deterministic execution accuracy across all modules.

---

## 1. CONTEXT WINDOW & ANTI-OVERFLOW PROTOCOL (MANDATORY)

1. **NEVER Read Full Book Manuscripts (`books/**/*.md`) into LLM Context**:
   - The 9 seed manuscripts inside `./books/en/book-{1,2,3}.md`, `./books/id/casual/book-{1,2,3}.md`, and `./books/id/standar/book-{1,2,3}.md` contain ~16,000 words (~1,050 lines) each (~145,000 words total).
   - **STRICTLY FORBIDDEN**: Do NOT run `cat`, `read_file`, or `view_file` on entire files inside `./books/`. Doing so immediately exhausts a `<200k` context window and degrades reasoning precision.
   - **ALLOWED**: If you need to inspect a manuscript's header or callout format, run `head -n 35 books/id/casual/book-1.md`. All manuscript ingestion MUST be performed programmatically via PHP CLI (`php artisan crow:seed-manuscripts`).
2. **Progressive Document Loading (Load Only What Current Task Needs)**:
   - Do NOT read all specification documents (`01-PRD.md` through `09-Infrastructure-Security-And-Testing-Runbook.md`) at once if you are operating on a `<200k` context model.
   - Open `00-OPENCODE-MASTER-INDEX.md`, check the current atomic task (`TASK-01` .. `TASK-18`), and read **ONLY** the specific document sections listed under that task's `Dokumen Acuan yang Dibaca`.
3. **One Atomic Task Per Turn**:
   - Pick the first unchecked `[ ]` task in `00-OPENCODE-MASTER-INDEX.md`.
   - Implement **complete, production-ready code** (zero `// TODO`, zero `// ... rest of code` placeholders).
   - Run the task's **Verification Command** (`./vendor/bin/pest`, `npx tsc --noEmit`, `npm run build`, etc.).
   - Mark the task `[x]` only after the verification command exits with code `0`.

---

## 2. CORE PRODUCT & ARCHITECTURAL INVARIANTS FOR `crow.asia`

1. **Brand & Domain Identity**:
   - Project name and domain: **`crow.asia`** (Pan-Asian Interactive Leadership Field Playbook Platform).
2. **Two-Tier Language (`locale`) vs Indonesian Register (`register`) Architecture**:
   - **Global Menu Locales (`locale`)**: The top navigation language switcher (`GlobalLocaleMenu.tsx` inside `TopNavbarFullWidth.tsx`) ONLY displays **country/language locales**: currently **`ID` (Bahasa Indonesia)** and **`EN` (English)**, and dynamically lists additional Asian languages (`ms`, `ja`, `ko`, `zh-CN`, `tl`, `vi`, `th`) once their `supported_locales.is_active` flag is enabled by Admin.
   - **In-Content Indonesian Register Switcher (`register`)**: `casual` (*Gue/Lo*) vs `standard` (*Saya/Kamu*) is **NEVER** placed inside the global language dropdown. When—and ONLY when—the active locale is **`ID` (`locale === 'id'`)**, the `InContentRegisterToggle.tsx` component (`😎 Kasual (Gue/Lo)` | `👔 Standar (Saya/Kamu)`) is rendered inside the content toolbar / Reader dock.
   - **Strict Register Constraint**: Across PostgreSQL (`chapter_translations.register`), Redis cache keys, and TypeScript types (`ContentRegister`), the ONLY valid values for `register` are `'casual'` and `'standard'` (**NEVER** use `'default'`). When `locale === 'id'`, `register` defaults to `'casual'` (switchable to `'standard'`). When `locale !== 'id'` (`'en'`, `'ja'`, `'ms'`, etc.), `register` MUST always be `'standard'` and `InContentRegisterToggle.tsx` MUST be hidden.
3. **Desktop Full-Width Layout (`100vw`) + Tablet/Mobile Responsive**:
   - On Desktop (`lg: >= 1024px`, `xl: >= 1280px`, `2xl: >= 1536px`), the platform uses a **Full-Width Edge-to-Edge Workspace Layout** (`w-full max-w-none`)—combining a collapsible left navigation rail (`280px`–`320px`), a fluid center reading/animation canvas (`flex-1 min-w-0`), and a sticky right tactical toolkit panel (`300px`–`340px`).
   - On Tablet (`768px–1023px`) and Mobile (`< 768px`), panels collapse cleanly into responsive slide-over drawers and sticky action bars with zero horizontal overflow and touch targets `>= 44x44px`.
4. **Dual-Mode Web Reader & Optional YouTube Add-On**:
   - The Reader view features **only 2 primary modes**:
     1. **`Mode Baca Interaktif` (Scrolling Mode)**: Served from Redis 8 pre-compiled and sanitized HTML (`<25ms`), with `<2ms` DOM token replacement for `[Nama]` (`.teammate-token`) and cloud-synced checklists.
     2. **`Mode Animasi` (Landscape 16:9 `@remotion/player`)**: Real-time React 19.3 / SVG animation at `1280x720` (`16:9`), 30 FPS—**NOT an MP4 video**. Before writing any Remotion code, you MUST read `07-Remotion-Local-LLM-Guide.md`.
   - **Optional YouTube Feature (`is_youtube_enabled`)**: Default is `false`. The `OptionalYouTubeShieldPlayer.tsx` component and sub-toggle are rendered ONLY if the Admin explicitly sets `is_youtube_enabled = true` and provides a `youtube_unlisted_id` for that chapter (and the user has chapter access).

---

## 3. STRICT 2026 TECH STACK GUARDRAILS (ANTI-LEGACY HALLUCINATION RULES)

Many LLMs have pre-2026 training habits. You MUST enforce the following modern syntax and API rules:

1. **PHP 8.5 & Laravel 13**:
   - Require `php: ^8.5` and `laravel/framework: ^13.0`.
   - Use **Laravel 13 AI SDK (`Laravel\Ai`)** for LLM translation and embedding generation (`Str::of($text)->toEmbeddings()`), **NOT** raw cURL or unmaintained third-party wrappers.
   - Use Laravel 13 `PreventRequestForgery` middleware (and explicitly exclude `/api/webhooks/mayar` with signature verification).
   - Use Laravel 13 declarative PHP Attributes where appropriate (`#[Middleware]`, `#[Authorize]`, `#[Tries(3)]`, `#[Backoff([10, 30, 60])]`, `#[Timeout(180)]`).
   - **Octane / FrankenPHP Worker Safety**: NEVER store per-request state, `Auth::user()`, or request references in static class properties or singleton constructor properties. Inject dependencies at the method level or use `#[Scoped]` container bindings.
2. **Filament v5 (Livewire v4)**:
   - Require `filament/filament: ^5.0`.
   - Filament v5 uses the unified `Filament\Schemas\Schema` API for forms and infolists, Livewire v4 Islands for isolated partial re-renders, native `Callout` components, and mobile-friendly stacked tables. Do NOT mix legacy Filament v2/v3 `Form` signatures where v5 `Schema` is required.
3. **Inertia.js v3 + React 19.3 + TypeScript 6.0 + Vite 7**:
   - Require `@inertiajs/react: ^3.0`, `@inertiajs/vite: ^3.0`, `react: ^19.3`, `typescript: ^6.0`, and `vite: ^7.0`.
   - **NO Axios**: Inertia.js v3 removed Axios in favor of its built-in lightweight HTTP client and `useHttp` / `router` APIs (`router.visit`, `router.post` with optimistic updates). Do NOT install or import `axios`.
   - **React 19.3 Rules**: Pass `ref` directly as a standard prop (do NOT wrap components in deprecated `React.forwardRef`). Because **React Compiler 1.0** is enabled in Vite, avoid cluttering components with unnecessary manual `useMemo` / `useCallback` wrappers unless required for external imperative refs.
   - **TypeScript 6.0 Rules**: In `tsconfig.json`, use `"moduleResolution": "bundler"`. Never use deprecated `--target ES5`, `--moduleResolution node10`, or `--baseUrl` without `paths`.
4. **Tailwind CSS v4.3 (CSS-First Configuration)**:
   - Require `tailwindcss: ^4.3` and `@tailwindcss/vite: ^4.3`.
   - **NO `tailwind.config.js`**: Do NOT create a legacy `tailwind.config.js` or use `@tailwind base; @tailwind components; @tailwind utilities;`.
   - Configure all design tokens (`--color-crow-ink`, `--color-crow-ivory`, `--font-sans`, etc.) directly inside `resources/css/app.css` using `@import "tailwindcss";` and the `@theme { ... }` directive.
5. **PostgreSQL 18 + `pgvector` & Redis 8**:
   - Use PostgreSQL 18 native `uuidv7()` as the default UUID generator (`DEFAULT uuidv7()`) on all UUID primary keys (`users`, `orders`, `user_entitlements`, `checklist_progress`) for time-ordered B-tree index locality.
   - Combine PostgreSQL `tsvector` GIN full-text search with `pgvector` (`vector(768)`) for hybrid keyword + semantic search in SOS Topic Search (`Ctrl+K`).

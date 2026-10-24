---
document: 05-UI-UX-Design-System
project: crow.asia
version: 1.0.0-greenfield
date: 2026-10-24
purpose: Frontend UI/UX Design System, Tailwind CSS v4.3 @theme Configuration, Accessibility Standards, and Full-Width Layout Blueprint
---

# Spesifikasi Sistem Desain UI/UX `crow.asia` (Tailwind CSS v4.3 + React 19.3)

Dokumen ini menetapkan spesifikasi visual dan tata letak frontend **`crow.asia`**. Seluruh aturan tata letak (**Desktop Full-Width `100vw`**, **responsivitas Tablet/Mobile**, **pemisahan Menu Bahasa Global vs In-Content Register Switcher `Kasual`/`Standar`**, **Dual-Mode Reader**, serta standar aksesibilitas **WCAG AA**) dikunci secara presisi menggunakan arsitektur *CSS-first* **Tailwind CSS v4.3**.

---

## 1. Karakter Visual & Standar Anti-Cliché UI

### 1.1 Arah Estetika Terpilih: *Tactile Crow Studio* (Hybrid Editorial + Sketsa Taktis)

| Dimensi Desain | Opsi 1: *Warm Whiteboard Doodle* | Opsi 2: *Swiss Crow Architectural* | **🏆 Arah Resmi `crow.asia`: *Tactile Crow Studio* (Hybrid Editorial + Sketsa Taktis)** |
| :--- | :--- | :--- | :--- |
| **Kesan & Karakter** | Santai, penuh coretan spidol, kartu bergaris tebal seperti komik. | Sangat formal, grid kaku, terkesan seperti laporan konsultan eksekutif. | **Tajam, cerdas, dan taktis.** Tipografi baca bersih dan nyaman di mata, dipadukan dengan sketsa diagram papan tulis (*whiteboard schematics*) serta lencana taktis khas `crow.asia`. |
| **Kesesuaian dengan Dual-Register (`Kasual` & `Standar`)** | Terlalu santai saat user memilih mode `Standar (Saya/Kamu)` atau `English`. | Terlalu kaku saat user memilih mode `Kasual (Gue/Lo)`. | **Adaptif penuh.** Menyatu dengan gaya `Kasual (Gue/Lo)` maupun `Standar (Saya/Kamu)` dan `English`. |
| **Kenyamanan di Layar Full-Width Desktop** | Garis tepi tebal di semua panel terasa bising di monitor besar. | Rapi di monitor lebar, namun terasa dingin. | Menggunakan *Clean Hairline Grid* (`1px solid #E5E0D5`) untuk struktur utama Full-Width, dan memberi aksen warna tegas pada komponen interaktif (Skrip, Callout, & Kanvas Remotion 16:9). |

### 1.2 Aturan Ketat Anti-Slop UI (Wajib Dipatuhi Agent)
1. **Tanpa Gradasi Ungu/Neon Klise**: Dilarang menggunakan gradasi ungu-indigo (`from-purple-600 to-indigo-600`) atau efek bayangan neon menyala (*glow shadows*). Gunakan permukaan solid hangat (`#FAF8F5`, `#FFFFFF`, `#121316`) dengan satu aksen warna per volume buku.
2. **Tanpa Glassmorphism Berlebihan**: Hindari kartu transparan kabur berlapis-lapis yang menurunkan kontras teks bacaan.
3. **Batas Lebar Baris Bacaan Ergonomis (`65ch–75ch`) di Dalam Panggung Full-Width**: Meskipun aplikasi menggunakan layout `100vw` penuh dari tepi kiri ke kanan layar monitor, blok paragraf di dalam `PreCompiledArticle.tsx` harus dibatasi `max-w-[72ch] mx-auto` agar mata pembaca tidak lelah menyapu baris yang terlalu panjang, sementara kanvas **Remotion 16:9** dan tabel perbandingan diizinkan melebar hingga `max-w-5xl mx-auto`.
4. **Standar Aksesibilitas Web (WCAG AA)**:
   - Rasio kontras teks utama terhadap latar kertas minimal **4.5:1**.
   - Setiap tombol, tab, dan tautan wajib memiliki state `:focus-visible` yang tegas (`focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600`).
   - Target sentuh (*touch target*) semua kontrol interaktif di layar mobile/tablet minimal **`44×44px`** (`min-h-11 min-w-11`).

---

## 2. Arsitektur Layout: Desktop Full-Width (`100vw`) & Responsif Tablet/Mobile

Ketika `crow.asia` dibuka di **Desktop (`>= 1024px`)**, website menggunakan **Full-Width Layout (`w-full max-w-none`)** yang memanfaatkan seluruh ruang layar monitor tanpa menyisakan ruang kosong mati di kiri-kanan.

### 2.1 Struktur Layout Halaman Web Reader di Desktop (Full-Width 3-Pane Workspace)

```text
+----------------------------------------------------------------------------------------------------------------------+
| TopNavbarFullWidth.tsx: [🦅 crow.asia]  VOL.01 • VOL.02 • VOL.03  [🔍 SOS Topik (Ctrl+K)]  [🇮🇩 ID ▾] [🌙] [Dashboard] |
+---------------------------+--------------------------------------------------------------+---------------------------+
| ◄ DAFTAR BAB (Collapsible)|  Bab 7 dari 15  •  [📖 Mode Baca Interaktif] [🎬 Animasi 16:9]| 🎛️ TACTICAL READER DOCK   |
| Width: 280px - 320px      |  Width: flex-1 min-w-0 (Fluid Full-Width Canvas)             | Width: 300px - 340px      |
|                           |                                                              |                           |
| [VOL.01] [VOL.02] [VOL.03]|  # Bab 7: Menyepakati Target di Awal (Rumus 3K)              | GAYA BAHASA (KHUSUS ID):  |
|                           |                                                              | [😎 Kasual] [👔 Standar]  |
| ✓ 00. Pendahuluan (Free)  |  +--------------------------------------------------------+  | ------------------------- |
| ✓ 01. Peralihan Peran     |  | 🎬 PANGGUNG ANIMASI 16:9 / ILUSTRASI BAB               |  | 👤 PERSONALISASI SKRIP:   |
| ✓ 02. Kerangka Kerja 4x4  |  | (Saat Mode Animasi 16:9 aktif, kanvas @remotion/player |  | Nama Anggota Tim:         |
| ✓ 03. Skill 1: Menyimak   |  |  1280x720 melebar proporsional di kolom tengah ini)    |  | [ Budi                 ]  |
| ✓ 04. Skill 2: Bertanya   |  +--------------------------------------------------------+  | Semua skrip otomatis siap |
| ✓ 05. Skill 3: Fakta      |                                                              | salin memakai nama ini.   |
| ✓ 06. Skill 4: Keyakinan  |  [Isi Naskah Bab Pre-Compiled max-w-[72ch] mx-auto...]       | ------------------------- |
| ▶ 07. Obrolan 1: Rumus 3K |                                                              | 🎨 TEMA KERTAS BACA:      |
| ✓ 08. Obrolan 2A: Pujian  |  +--------------------------------------------------------+  | (•) Ivory  ( ) Sepia      |
| ✓ 09. Obrolan 2B: Koreksi |  | 💬 SKRIP 1-ON-1 SIAP SALIN                    [Salin]  |  | ( ) Crow Obsidian (Night) |
| ✓ 10. Evaluasi 30/70      |  | "Eh Budi, tujuan laporan ini buat rapat direksi..."    |  | ------------------------- |
| ✓ 11. Simulasi 4 Minggu   |  +--------------------------------------------------------+  | 🔠 UKURAN TEKS:           |
| ✓ 12. Kalibrasi L1-L4     |                                                              | [  A  ] [  A+ ] [ A++ ]   |
| ✓ 13. Rencana 30 Hari     |  [⬅ Bab Sebelumnya]                      [Lanjut: Bab 8 ➡]  | ------------------------- |
| ✓ 14. Lampiran & Riset    |                                                              | ✅ PROGRES LATIHAN BAB:   |
|                           |                                                              | [██████░░░░] 3/5 Selesai  |
+---------------------------+--------------------------------------------------------------+---------------------------+
```

### 2.2 Struktur Layout Halaman Landing Page di Desktop (Full-Width Bento Showcase)

```text
+----------------------------------------------------------------------------------------------------------------------+
| TopNavbarFullWidth.tsx: [🦅 crow.asia]  Katalog 3 Playbook • Diagnosis 45 Detik • Demo Skrip • Harga  [🇮🇩 ID ▾] [Masuk]|
+----------------------------------------------------------------------------------------------------------------------+
|  +----------------------------------------------------+  +--------------------------------------------------------+  |
|  | ★ TACTICAL LEADERSHIP FIELD PLAYBOOK               |  | 🎬 LIVE REMOTION 16:9 INTERACTIVE STUDIO               |  |
|  |                                                    |  | (Rasio Landscape 16:9 Penuh — 1280x720 @ 30fps)        |  |
|  | Baru Jadi Leader atau Mulai Kewalahan Ngurusin     |  |                                                        |  |
|  | Tim? Ini Contekan Meja Kerja Lo.                   |  | [Visualisasi Interaktif Buku + Simulasi Skrip 1-on-1]  |  |
|  |                                                    |  |                                                        |  |
|  | Gaya Bahasa ID: [😎 Kasual (Gue/Lo)] [👔 Standar]  |  | Pilih Preview Playbook:                                |  |
|  |                                                    |  | [VOL.01 Team Lead] [VOL.02 Anti-Burnout] [VOL.03 Div]  |  |
|  | [📖 Baca Gratis Bab 0 & 1]  [⚡ Lihat Paket Harga]  |  +--------------------------------------------------------+  |
|  +----------------------------------------------------+                                                              |
+----------------------------------------------------------------------------------------------------------------------+
```

### 2.3 Aturan Responsivitas Breakpoints (Desktop $ightarrow$ Tablet $ightarrow$ Mobile)

| Breakpoint | Rentang Layar | Perilaku Layout `crow.asia` |
| :--- | :--- | :--- |
| **Desktop Wide (`xl` & `2xl`)** | `>= 1280px` | **3-Pane Full-Width**: Sidebar Kiri (`300px`) + Panggung Tengah (`flex-1 px-10`) + Dock Kanan (`320px`) terbuka penuh secara simultan. |
| **Desktop / Laptop (`lg`)** | `1024px – 1279px` | **3-Pane Compact Full-Width**: Sidebar Kiri (`260px`, dapat di-toggle tutup dengan `Ctrl+\` agar Panggung Tengah makin lega) + Panggung Tengah (`flex-1 px-6`) + Dock Kanan (`280px`). |
| **Tablet (`md`)** | `768px – 1023px` | **2-Pane / Slide-Over**: Sidebar Kiri berubah menjadi *Slide-Over Drawer*, sedangkan kontrol *In-Content Register Switcher (`Kasual`/`Standar`)* dan *Mode Baca/Animasi* menempel di *Sticky Sub-Header* atas. |
| **Mobile (`sm` & `<768px`)** | `< 768px` | **Single-Column Full-Bleed**: Padding tepi `px-4`, *Sticky Top Bar* menampilkan *Toggle Mode (`📖 Baca` \| `🎬 Animasi 16:9`)* dan *Toggle `Kasual`/`Standar`* (saat `ID`), serta *Floating Bottom Pill* (`min-h-12`) untuk membuka Daftar Bab & Pengaturan Tampilan. |

---

## 3. Spesifikasi Komponen Navigasi Bahasa vs In-Content Register Switcher

1. **`TopNavbarFullWidth.tsx` + `GlobalLocaleMenu.tsx` (Di Top Navbar Kanan)**:
   - Hanya menampilkan bendera + kode bahasa negara dari tabel `supported_locales`:
     - `🇮🇩 ID — Bahasa Indonesia`
     - `🌐 EN — English`
     - *(Jika Admin sudah mem-publish terjemahan AI Asia, otomatis muncul di bawahnya dengan badge `AI`: `🇲🇾 MS — Bahasa Melayu`, `🇯🇵 JA — 日本語`, dll.)*
2. **`InContentRegisterToggle.tsx` (Di Dalam Konten Web — Hanya Render jika `locale === 'id'`)**:
   - Berupa komponen *Segmented Pill Switch* yang terletak di:
     1. **Landing Page**: Tepat di atas tombol CTA Hero dan di atas kotak *Live Script Personalizer Demo*.
     2. **Web Reader**: Di bagian paling atas *Tactical Reader Dock* (`TacticalRightDock.tsx` pada desktop) dan di *Sticky Sub-Header* tepat di bawah judul bab (mobile/tablet).
   - Desain Visual Toggle:
     - Opsi Kiri (`register === 'casual'`): **`😎 Santai (Gue/Lo)`** — indikator titik warna kuning amber (`#F59E0B`).
     - Opsi Kanan (`register === 'standard'`): **`👔 Standar (Saya/Kamu)`** — indikator titik warna biru kobalt (`#2563EB`).
   - Saat diklik, teks bab dan skrip dialog di dalam Mode Baca maupun Mode Animasi Remotion 16:9 langsung berganti tanpa *page reload* penuh menggunakan `router.visit(url, { preserveScroll: true, preserveState: true })` milik Inertia.js v3.

---

## 4. Sistem Palet Warna, Tipografi & Konfigurasi Tailwind CSS v4.3 (`resources/css/app.css`)

### 4.1 Palet Warna Inti & Tema Kertas Baca

| Token CSS / Tailwind v4.3 | Hex Code | Peruntukan UI & Kontras |
| :--- | :---: | :--- |
| `--color-crow-ink` | `#111113` | Warna teks utama (rasio kontras `17.8:1` di atas Ivory), ikon `crow.asia`, dan garis tepi tegas. |
| `--color-crow-ivory` | `#FAF8F5` | Latar belakang utama *Warm Ivory Paper* (Default, nyaman untuk baca durasi panjang). |
| `--color-crow-sepia` | `#F4ECD8` | Latar belakang mode baca *Archival Sepia*. |
| `--color-crow-obsidian` | `#121316` | Latar belakang mode malam (*Night Mode*) dengan teks `#F4F4F5` (rasio kontras `16.4:1`). |
| `--color-vol-1-cobalt` | `#1D4ED8` | Warna aksen identitas **Buku 1 (Mahir Memimpin Tim)**. |
| `--color-vol-2-terracotta` | `#EA580C` | Warna aksen identitas **Buku 2 (Napas Panjang Memimpin Tim)**. |
| `--color-vol-3-emerald` | `#0F766E` | Warna aksen identitas **Buku 3 (Memimpin Para Leader)**. |
| `--color-crow-gold` | `#FFE66D` | Warna sorotan stabilo (*Tactical Highlight*), lencana *Best Value*, dan step aktif Remotion. |

### 4.2 Blueprint Konfigurasi `resources/css/app.css` (Tailwind CSS v4.3 CSS-First)

> **PERINGATAN UNTUK AGENT LLM**: Jangan membuat file `tailwind.config.js`. Gunakan blok `@theme` di bawah ini secara langsung di dalam `resources/css/app.css`:

```css
@import "tailwindcss";

@theme {
  --font-sans: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
  --font-serif: "Newsreader", "Source Serif 4", ui-serif, Georgia, serif;
  --font-mono: "JetBrains Mono", ui-monospace, SFMono-Regular, monospace;
  --font-hand: "Patrick Hand", cursive;

  --color-crow-ink: #111113;
  --color-crow-ivory: #FAF8F5;
  --color-crow-sepia: #F4ECD8;
  --color-crow-obsidian: #121316;
  --color-crow-border: #E5E0D5;
  --color-crow-gold: #FFE66D;

  --color-vol-1-cobalt: #1D4ED8;
  --color-vol-2-terracotta: #EA580C;
  --color-vol-3-emerald: #0F766E;
}

/* Tema Kertas Baca pada Container Reader */
[data-paper-theme="ivory"] {
  background-color: var(--color-crow-ivory);
  color: var(--color-crow-ink);
}

[data-paper-theme="sepia"] {
  background-color: var(--color-crow-sepia);
  color: #1C1917;
}

[data-paper-theme="obsidian"] {
  background-color: var(--color-crow-obsidian);
  color: #F4F4F5;
}

/* Styling Token Nama Anggota Tim (.teammate-token) */
.teammate-token {
  display: inline-block;
  padding: 0 0.35rem;
  border-radius: 0.375rem;
  background-color: var(--color-crow-gold);
  color: var(--color-crow-ink);
  font-family: var(--font-mono);
  font-weight: 700;
}
```

---

## 5. Spesifikasi Komponen Konversi di `Landing.tsx`

1. **Hero Section (Split 50/50 di Layar Lebar)**:
   - Kolom Kiri: Headline tegas sesuai bahasa & register aktif, `InContentRegisterToggle.tsx` (saat `locale === 'id'`), dan 2 tombol aksi utama (`Baca Gratis Bab 0 & 1` serta `Lihat Paket Harga`).
   - Kolom Kanan: Panggung interaktif `HeroTrilogy16x9Comp.tsx` di dalam `@remotion/player` (`1280×720`) dengan pemilih cepat preview Volume 01, 02, dan 03.
2. **Kuis Diagnosis Kepemimpinan 45 Detik**:
   - Komponen interaktif 3 langkah tanpa reload yang memetakan masalah utama pengunjung (*"Tim masih nunggu disuapin"*, *"Gue kewalahan & hampir burnout"*, atau *"Para team lead di bawah gue jalan sendiri-sendiri"*) ke Volume Playbook yang tepat.
3. **Live Script Personalizer Demo**:
   - Kotak simulasi tempat pengunjung mengetik nama staf mereka dan melihat perubahan skrip 1-on-1 secara langsung antara gaya `Kasual (Gue/Lo)` dan `Standar (Saya/Kamu)`.

---
document: 06-Visual-Illustration-Prompts
project: crow.asia
version: 1.0.0-greenfield
date: 2026-10-24
purpose: Complete Text-to-Image Prompt Library for Brand Mascot, Book Covers, Landing Page, and all 45 Chapters across Books 1, 2, and 3 (2026 Frontier Image Models).
---

# Library Prompt Text-to-Image Visual & Ilustrasi Konten `crow.asia`

Dokumen ini berisi kumpulan lengkap **Prompt Text-to-Image** untuk menghasilkan aset visual dan ilustrasi yang relevan dengan isi naskah **`crow.asia`**. Seluruh prompt dirancang untuk model generatif terbaru per Oktober 2026: **Midjourney v8.1 / v7**, **Black Forest Labs FLUX.2 Pro / FLUX 3 Image Klein**, **Google Imagen 4 / Gemini 3.1 Flash Lite Image (Nano Banana 2)** via **Laravel 13 AI SDK (`Laravel\Ai\Image`)**, **OpenAI GPT Image 2.5**, dan **Ideogram 3.0 / 4.0**.

---

## 1. Master Style Prompt Prefix & Negative Prompt

Agar seluruh 45 bab dan 3 sampul buku memiliki kesatuan gaya visual (*visual consistency*) khas **`crow.asia`**, selalu gabungkan **Master Style Prefix** di bawah ini di awal setiap prompt:

### 1.1 Master Style Prefix (Gaya *Tactile Editorial & Whiteboard Schematic*)
> **Master Prefix**:
> `"Tactical editorial technical illustration in the hybrid style of Monocle Magazine, Stripe Press, and hand-drawn architectural whiteboard schematics, warm ivory paper background (#FAF8F5), crisp carbon black ink linework (#111113), subtle isometric geometric composition, featuring a subtle clever minimalist geometric crow emblem as a small corner motif, clean studio lighting, high contrast, vector precision, clean composition, text-free, no labels, no watermarks --ar 16:9"`

### 1.2 Negative Prompt Standar
> **Negative Prompt**:
> `"photorealistic human faces, stock photo, 3D plastic render, purple neon gradient, cyberpunk, blurry text, gibberish labels, watermark, signature, distorted hands, dark gloomy horror background, cluttered background"`

### 1.3 Otomatisasi via Laravel 13 AI SDK (`php artisan crow:generate-illustrations`)
Dengan **Laravel 13 AI SDK**, geração ilustrasi bab dapat dijalankan secara terprogram menggunakan `Laravel\Ai\Image`:
```php
use Laravel\Ai\Image;

$image = Image::of($masterPrefix . ' ' . $chapterPrompt)
    ->aspectRatio('16:9')
    ->generate();
```

---

## 2. Prompt Identitas Brand, Maskot & 3 Sampul Buku (`public/covers/`)

| Kode Aset | Target File | Rasio | Prompt Text-to-Image Lengkap |
| :--- | :--- | :---: | :--- |
| **Brand Mascot / Emblem** | `public/brand/crow-mascot.webp` | `1:1` | `"Minimalist geometric tactical crow mascot perched on a brass architectural compass, wearing subtle round architect glasses, holding a glowing golden key in its beak, warm ivory paper background (#FAF8F5), bold carbon ink linework (#111113) with cobalt blue (#1D4ED8) and warm yellow (#FFE66D) accents, Monocle Magazine editorial illustration style, clean vector, text-free, no labels, no watermarks --ar 1:1"` |
| **Sampul Buku 1 (`book-1`)** | `public/covers/book-1-cover.webp` | `3:4` | `"Editorial book cover illustration for a leadership playbook, isometric architectural drafting table where a clever geometric crow conductor orchestrates four synchronized brass and cobalt blue (#1D4ED8) mechanical gears in harmony, warm ivory background (#FAF8F5), crisp carbon ink lines (#111113), generous clean top margin, Stripe Press and Monocle aesthetic, text-free, no labels, no watermarks --ar 3:4"` |
| **Sampul Buku 2 (`book-2`)** | `public/covers/book-2-cover.webp` | `3:4` | `"Editorial book cover illustration for an anti-burnout leadership playbook, a calm geometric crow architect standing behind a translucent geometric glass shield that transforms chaotic storm waves into three neat terracotta orange (#EA580C) and warm gold (#FFE66D) building blocks on a conveyor belt, warm ivory background (#FAF8F5), crisp carbon ink linework, text-free, no labels, no watermarks --ar 3:4"` |
| **Sampul Buku 3 (`book-3`)** | `public/covers/book-3-cover.webp` | `3:4` | `"Editorial book cover illustration for an executive divisional leadership playbook, an architectural lighthouse on a blueprint grid projecting deep alpine emerald (#0F766E) and golden alignment beams across a suspension bridge connecting three independent command towers, guided by a geometric crow navigator, warm ivory background (#FAF8F5), crisp ink lines, text-free, no labels, no watermarks --ar 3:4"` |

---

## 3. Prompt Ilustrasi 15 Bab — Buku 1: *Mahir Memimpin Tim* (`Accent: Cobalt Blue #1D4ED8`)

Seluruh ilustrasi bab menggunakan rasio **Landscape `16:9`** agar selaras dengan kanvas Mode Animasi di `crow.asia`. Gunakan *Master Prefix* di atas ditambah deskripsi subjek spesifik di bawah ini:

| Bab (`slug`) | Motif Visual | Konteks Materi Bab | Prompt Spesifik Subjek (`--ar 16:9`, Accent `#1D4ED8`) |
| :--- | :--- | :--- | :--- |
| **`book-1:ch-0`** | `compass` | Pendahuluan: Rasanya Baru Diangkat Jadi Team Leader | `"A precision brass-and-cobalt architectural compass resting on an isometric grid desk next to a first-time captain star badge and a clever geometric crow silhouette, warm ivory background (#FAF8F5), cobalt blue (#1D4ED8) and gold (#FFE66D) accents --ar 16:9"` |
| **`book-1:ch-1`** | `gears` | Bab 1: Peralihan dari Kerja Sendiri ke Mengarahkan Tim | `"An isometric mechanical diagram showing one master conductor gear smoothly driving four synchronized smaller team gears without friction, cobalt blue (#1D4ED8) and yellow (#FFE66D) highlights on warm ivory paper (#FAF8F5) --ar 16:9"` |
| **`book-1:ch-2`** | `matrix` | Bab 2: Kerangka Kerja 4x4 (4 Obrolan Inti + 4 Skill) | `"A clean 4x4 architectural matrix blueprint on a drafting table where four vertical conversation pillars intersect with four horizontal communication skill beams, glowing cobalt blue (#1D4ED8) nodes --ar 16:9"` |
| **`book-1:ch-3`** | `waveform` | Bab 3: Skill 1 — Menyimak Sampai Paham | `"A half-closed laptop and face-down smartphone beside an acoustic precision lens that transforms a tangled chaotic thread into a calm, clear harmonic sine wave, cobalt blue (#1D4ED8) accent --ar 16:9"` |
| **`book-1:ch-4`** | `key` | Bab 4: Skill 2 — Bertanya untuk Memancing Solusi | `"A minimalist architectural key shaped like a question mark being handed over to unlock an isometric puzzle box, warm yellow (#FFE66D) and cobalt blue (#1D4ED8) accents on ivory paper --ar 16:9"` |
| **`book-1:ch-5`** | `lens` | Bab 5: Skill 3 — Menyampaikan Fakta Tanpa Menyerang Pribadi | `"An optical precision magnifying loupe examining an objective workflow chart on a neutral table, replacing pointing fingers with clear geometric data blocks, cobalt blue (#1D4ED8) accent --ar 16:9"` |
| **`book-1:ch-6`** | `battery` | Bab 6: Skill 4 — Menguatkan Rasa Percaya Diri Tim | `"Four ascending architectural staircase platforms with a glowing golden-cobalt energy core slotting into the top platform to power autonomous action, cobalt blue (#1D4ED8) accent --ar 16:9"` |
| **`book-1:ch-7`** | `target` | Bab 7: Obrolan 1 — Menyepakati Target di Awal (Rumus 3K) | `"Three concentric alignment rings locking into a single crisp bullseye target on an engineering whiteboard, cobalt blue (#1D4ED8) and gold (#FFE66D) accents --ar 16:9"` |
| **`book-1:ch-8`** | `spotlight` | Bab 8: Obrolan 2A — Apresiasi Langsung Saat Tim Kerja Bagus | `"An architectural desk lamp casting a warm golden spotlight (#FFE66D) directly onto a newly fitted cobalt blue (#1D4ED8) keystone puzzle piece in a team structure --ar 16:9"` |
| **`book-1:ch-9`** | `trajectory` | Bab 9: Obrolan 2B — Koreksi Empat Mata Saat Melenceng | `"A private navigation chart showing an off-track dotted trajectory line gently and respectfully guided back onto the main cobalt blue (#1D4ED8) vector path via a compass calibration --ar 16:9"` |
| **`book-1:ch-10`** | `telescope` | Bab 10: Obrolan 3 — Evaluasi Berkala Tanpa Kejutan (30/70) | `"A split-proportion 30/70 architectural horizon diagram with a small 30% rearview mirror and a large 70% brass telescope pointing toward an ascending cobalt blue (#1D4ED8) future grid --ar 16:9"` |
| **`book-1:ch-11`** | `podium` | Bab 11: Studi Kasus & Simulasi 4 Minggu (Rina & Budi) | `"An isometric 4-week tactical simulation board showing four connected milestone stations progressing from initial coaching to full team autonomy, cobalt blue (#1D4ED8) accent --ar 16:9"` |
| **`book-1:ch-12`** | `calibration` | Bab 12: Kalibrasi Gaya Pendampingan (L1–L4 ke G1–G4) | `"A four-quadrant precision calibration dial matching readiness levels with guidance styles, progressing from training wheels to solo flight, cobalt blue (#1D4ED8) accent --ar 16:9"` |
| **`book-1:ch-13`** | `cadence` | Bab 13: Rencana Praktik 30 Hari di Kantor | `"A spiral tactical desk journal open to a 30-day habit checklist grid with checked boxes, beside a fountain pen and a warm cup of coffee, cobalt blue (#1D4ED8) and gold (#FFE66D) accents --ar 16:9"` |
| **`book-1:ch-14`** | `toolbox` | Bab 14: Lampiran Contekan Meja Kerja & Rujukan Riset | `"An open modular Swiss architect toolbox holding neatly indexed conversation script cards, 1-on-1 templates, and research citation tabs, cobalt blue (#1D4ED8) accent --ar 16:9"` |

---

## 4. Prompt Ilustrasi 15 Bab — Buku 2: *Napas Panjang Memimpin Tim* (`Accent: Terracotta Orange #EA580C`)

| Bab (`slug`) | Motif Visual | Konteks Materi Bab | Prompt Spesifik Subjek (`--ar 16:9`, Accent `#EA580C`) |
| :--- | :--- | :--- | :--- |
| **`book-2:ch-0`** | `fire` | Pendahuluan: Titik Balik Leader yang Hampir Burnout | `"An overloaded precarious stack of burning folders transforming into a calm, self-running isometric conveyor belt system, terracotta orange (#EA580C) and teal (#0D9488) accents on ivory paper --ar 16:9"` |
| **`book-2:ch-1`** | `blueprint` | Bab 1: Dari Pemadam Kebakaran ke Arsitek Sistem Kerja | `"A retired emergency firehose hung neatly on a wall beside an architectural drafting table displaying a clean parallel workflow pipeline, terracotta orange (#EA580C) accent --ar 16:9"` |
| **`book-2:ch-2`** | `shield` | Bab 2: 4 Penataan Struktural & 4 Keterampilan Energi | `"Four interlocking structural rings surrounding a glowing protected energy core on a technical blueprint grid, terracotta orange (#EA580C) and warm gold (#FFE66D) accents --ar 16:9"` |
| **`book-2:ch-3`** | `lens` | Bab 3: Teknik Glass Shield (Fakta vs Beban Emosi) | `"A transparent geometric glass shield prism splitting a dark turbulent emotional storm cloud into three neat, objective, actionable terracotta (#EA580C) cubes on a desk --ar 16:9"` |
| **`book-2:ch-4`** | `key` | Bab 4: Anti-Monkey Management (Stop Ambil Alih Tugas) | `"A clean directional vector barrier gently deflecting incoming task arrows back into their rightful owner lanes on a team sprint board, terracotta orange (#EA580C) accent --ar 16:9"` |
| **`book-2:ch-5`** | `balance` | Bab 5: Negosiasi Kapasitas ke Bos Pakai Data Neraca | `"A precision brass architectural balance scale weighing project workload blocks against a clear 100% capacity threshold line for trade-off negotiation, terracotta orange (#EA580C) accent --ar 16:9"` |
| **`book-2:ch-6`** | `switch` | Bab 6: Protokol Detachment Jam 6 Sore (Saklar 18:00) | `"A minimalist Braun-inspired mechanical toggle switch flipped cleanly to OFF beside a closed laptop and a calm evening horizon line, terracotta orange (#EA580C) accent --ar 16:9"` |
| **`book-2:ch-7`** | `calendar` | Bab 7: Audit Kalender 3P (Pangkas, Padatkan, Pindahkan) | `"A weekly calendar grid where cluttered small meeting balloons are snipped away to reveal bold, protected golden-terracotta (#EA580C) deep-focus blocks --ar 16:9"` |
| **`book-2:ch-8`** | `relay` | Bab 8: Mencetak Tangan Kanan (Second-in-Command) | `"Two synchronized parallel running tracks passing a glowing relay torch seamlessly across operational checkpoints, terracotta orange (#EA580C) and gold (#FFE66D) accents --ar 16:9"` |
| **`book-2:ch-9`** | `gateway` | Bab 9: Pintu Gerbang Antrean Kerja & Batas Ekspektasi | `"An architectural intake funnel and sluice gate organizing scattered chaotic request arrows into a calm, single-file prioritized queue, terracotta orange (#EA580C) accent --ar 16:9"` |
| **`book-2:ch-10`** | `blueprint` | Bab 10: Job Crafting 30/70 (Otomatisasi vs Strategis) | `"A precision architectural proportion block divided into a 70% automated mechanical base and a 30% elevated strategic innovation tower, terracotta orange (#EA580C) accent --ar 16:9"` |
| **`book-2:ch-11`** | `equilibrium` | Bab 11: Simulasi 4 Minggu Pemulihan dari Kemelut Lembur | `"Four progressive isometric stages showing a tilted, overloaded desk structure returning to horizontal equilibrium and calm tea-drinking flow, terracotta orange (#EA580C) accent --ar 16:9"` |
| **`book-2:ch-12`** | `gauge` | Bab 12: Barometer Kelelahan L1–L4 & Protokol Pemulihan | `"An analog precision thermometer and dial gauge with four distinct energy zones and recovery station markers, terracotta orange (#EA580C) and teal (#0D9488) accents --ar 16:9"` |
| **`book-2:ch-13`** | `pacemaker` | Bab 13: Ritme Kepemimpinan Maraton 30 Hari | `"A steady, rhythmic marathon pulse wave running smoothly across a long-distance architectural elevation chart, terracotta orange (#EA580C) accent on warm ivory paper --ar 16:9"` |
| **`book-2:ch-14`** | `toolbox` | Bab 14: Lampiran Kotak P3K Ketahanan Kerja & Riset | `"A structured workplace resilience first-aid kit open to reveal priority trade-off scripts, evening shutdown checklists, and research cards, terracotta orange (#EA580C) accent --ar 16:9"` |

---

## 5. Prompt Ilustrasi 15 Bab — Buku 3: *Memimpin Para Leader* (`Accent: Alpine Emerald #0F766E`)

| Bab (`slug`) | Motif Visual | Konteks Materi Bab | Prompt Spesifik Subjek (`--ar 16:9`, Accent `#0F766E`) |
| :--- | :--- | :--- | :--- |
| **`book-3:ch-0`** | `lighthouse` | Pendahuluan: Naik Kelas Jadi Pemimpin Para Leader | `"An architectural lighthouse on an isometric grid coast projecting emerald (#0F766E) and golden alignment beams toward three synchronized ships steered by team captains --ar 16:9"` |
| **`book-3:ch-1`** | `compass` | Bab 1: Berhenti Menarik Benang Sendiri, Pandu Para Kapten | `"A central strategy map table surrounded by three autonomous compass nodes operating in harmony without marionette strings, deep emerald (#0F766E) accent --ar 16:9"` |
| **`book-3:ch-2`** | `bridge` | Bab 2: 4 Forum Penyelarasan & 4 Skill Pembinaan Leader | `"An isometric golden-emerald (#0F766E) suspension bridge connecting three independent divisional towers across a canyon on an engineering blueprint --ar 16:9"` |
| **`book-3:ch-3`** | `key` | Bab 3: Coaching the Coach (Tanpa Merampas Mikrofon) | `"A cascading two-tier coaching schematic showing a senior leader empowering a team lead, who in turn guides their squad, deep emerald (#0F766E) and gold (#FFE66D) accents --ar 16:9"` |
| **`book-3:ch-4`** | `shield` | Bab 4: Aturan Anti-Skip-Level & Menjaga Wibawa Komando | `"An architectural chain-of-command shield reinforcing the direct link between a frontline team lead and their team members, preventing bypass arrows, deep emerald (#0F766E) accent --ar 16:9"` |
| **`book-3:ch-5`** | `telescope` | Bab 5: Menerjemahkan Visi Abstrak Direksi Jadi Aksi Divisi | `"An optical crystal prism translating a single high-altitude executive beam into three concrete, actionable departmental mission tracks, deep emerald (#0F766E) accent --ar 16:9"` |
| **`book-3:ch-6`** | `balance` | Bab 6: Managing Up & Across (Aliansi Lintas Departemen) | `"A multi-axis organizational gyroscopic compass aligning vertical executive expectations with horizontal peer-department collaboration, deep emerald (#0F766E) accent --ar 16:9"` |
| **`book-3:ch-7`** | `matrix` | Bab 7: Piagam Penyelarasan 3S (Sasaran, Serah-Terima, Sinyal) | `"Three interlocking hexagonal blueprint modules locking together to eliminate organizational silos, deep emerald (#0F766E) accent --ar 16:9"` |
| **`book-3:ch-8`** | `lens` | Bab 8: Bedah 1-on-1 Level Manajer (Kerangka Audit 4P) | `"A four-panel architectural diagnostic instrument inspecting Progress, People, Cross-Team Process, and Leader Growth, deep emerald (#0F766E) accent --ar 16:9"` |
| **`book-3:ch-9`** | `bridge` | Bab 9: Mediasi Gesekan Silo Antar-Tim dalam 15 Menit | `"Two separated departmental plates being joined cleanly and permanently by a precision interlocking dovetail joint on a blueprint, deep emerald (#0F766E) accent --ar 16:9"` |
| **`book-3:ch-10`** | `gauge` | Bab 10: Skip-Level Beretika & Kalibrasi Budaya Divisi | `"A multi-layer acoustic sonar diagram reading deep ground-truth organizational signals while keeping the team lead layer illuminated and respected, deep emerald (#0F766E) accent --ar 16:9"` |
| **`book-3:ch-11`** | `podium` | Bab 11: Simulasi 4 Minggu Menyatukan Tiga Tim yang Silo | `"Three previously diverging departmental vectors progressively aligning into a single cohesive forward formation across four weekly checkpoints, deep emerald (#0F766E) accent --ar 16:9"` |
| **`book-3:ch-12`** | `calibration` | Bab 12: Kalibrasi Kematangan Para Team Leader (L1–L4) | `"Four ascending architectural pillars showing increasing structural span and leadership autonomy from newly promoted lead to strategic multiplier, deep emerald (#0F766E) accent --ar 16:9"` |
| **`book-3:ch-13`** | `cadence` | Bab 13: Cetak Biru Transisi 90 Hari Kepala Divisi (30-60-90) | `"A three-phase master architectural timeline with crisp milestone flags, deep emerald (#0F766E) accent --ar 16:9"` |
| **`book-3:ch-14`** | `toolbox` | Bab 14: Lampiran Playbook Eksekutif Divisi & Riset | `"An executive architectural portfolio binder open to charter templates, audit sheets, succession matrices, and organizational research citations, deep emerald (#0F766E) accent --ar 16:9"` |

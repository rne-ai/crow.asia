# AGENTS.md

## Repository Agent Instructions

Repository ini menggunakan Git sebagai source of truth untuk source code dan file yang memang merupakan bagian dari produk.

### 1. Artefak Agent Dilarang Masuk ke Repository

Setiap agent LLM yang bekerja di repository ini **WAJIB memastikan artefak yang dihasilkan selama proses kerja tidak ikut di-commit atau di-push ke repository**.

Yang dimaksud artefak antara lain, tetapi tidak terbatas pada:

- catatan internal agent
- hasil brainstorming
- planning files
- scratch files
- temporary files
- intermediate output
- hasil eksperimen
- generated reports yang hanya digunakan selama development
- context files
- prompt/context dumps
- log hasil kerja agent
- file hasil evaluasi atau analisis internal
- metadata atau state yang hanya diperlukan oleh AI coding agent
- file/folder yang dibuat untuk membantu proses reasoning atau task execution

### 2. Gunakan `.gitignore`

Sebelum membuat file atau folder baru untuk kebutuhan internal agent:

1. Periksa `.gitignore`.
2. Gunakan lokasi/path yang memang sudah di-ignore apabila sesuai.
3. Jika artefak membutuhkan kategori baru, tambahkan rule yang sesuai ke `.gitignore` **sebelum artefak tersebut dibuat**.
4. Jangan mengandalkan asumsi bahwa file "tidak penting" berarti aman untuk dibiarkan untracked.

Repository ini sudah menyediakan ignore rules untuk berbagai AI/development artifacts, termasuk:

```text
.agy/
.antigravity/
.brainstorming/
.cursor/
.kiro/
.notes/
.opencode/
.superpowers/
notes/
docs/superpowers/
docs/project-docs/
docs/notes/
```

Agent **WAJIB menghormati rules tersebut** dan tidak boleh memindahkan, mengganti nama, atau membuat salinan artefak ke lokasi lain hanya untuk menghindari `.gitignore`.

### 3. Jangan Mengubah `.gitignore` untuk Memasukkan Artefak

Agent tidak boleh:

- menghapus ignore rule yang sudah ada tanpa alasan yang jelas dan relevan terhadap project;
- mengubah ignore rule agar artefak agent menjadi trackable;
- menggunakan `git add -f` untuk memasukkan artefak agent;
- melakukan commit terhadap file yang seharusnya merupakan artefak internal;
- melakukan push terhadap artefak internal.

Jika sebuah file seharusnya hanya digunakan oleh agent selama proses development, file tersebut harus tetap berada di luar tracked repository.

### 4. Source Code vs. Artefak

Sebelum membuat file baru, agent harus menentukan apakah file tersebut:

**A. Merupakan bagian dari produk/repository**, atau

**B. Hanya diperlukan untuk membantu agent menyelesaikan task.**

Jika termasuk kategori B, file tersebut **tidak boleh menjadi tracked file**.

Contoh file yang biasanya termasuk artefak internal:

```text
planning.md
brainstorm.md
scratch.md
analysis.md
task-notes.md
implementation-notes.md
research.md
agent-log.md
```

Nama file bukan satu-satunya penentu. Sebuah file tetap dianggap artefak apabila tujuan utamanya hanya untuk membantu proses kerja agent.

### 5. Verifikasi Git Sebelum Commit

Sebelum membuat commit, agent **WAJIB memeriksa Git status** dan memastikan tidak ada artefak internal yang akan ikut di-commit.

Minimal lakukan:

```bash
git status --short
```

Jika terdapat file yang merupakan artefak agent, file tersebut harus:

- dihapus dari working tree, atau
- dipindahkan ke lokasi yang di-ignore.

Jangan menggunakan `git add -f` terhadap artefak internal.

### 6. Folder `books/`

Folder `books/` merupakan bagian dari repository dan dapat berisi file yang memang sengaja disimpan sebagai project artifact/content.

Namun, agent tetap harus membedakan antara:

- file yang memang merupakan bagian dari isi repository; dan
- file sementara yang dibuat hanya untuk membantu proses pengerjaan.

Artefak internal tidak boleh disimpan di `books/` kecuali file tersebut memang dimaksudkan sebagai bagian dari repository.

### 7. Prinsip Utama

> **Jika sebuah file hanya dibuat untuk membantu agent berpikir, merencanakan, bereksperimen, atau menyelesaikan task, file tersebut bukan bagian dari repository dan tidak boleh di-commit maupun di-push.**

Source code, configuration, documentation, tests, assets, dan file lain yang memang diperlukan oleh aplikasi tetap boleh di-track apabila merupakan bagian yang disengaja dari project.

Agent bertanggung jawab memastikan working tree tetap bersih dari artefak internal sebelum melakukan commit atau meminta user melakukan commit/push.

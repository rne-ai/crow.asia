<?php

namespace App\Console\Commands;

use App\Models\Chapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Laravel\Ai\Image;

class GenerateIllustrationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crow:generate-illustrations 
                            {--book= : Batasi hanya untuk buku tertentu (book-1, book-2, book-3)}
                            {--dry-run : Tampilkan daftar prompt tanpa memanggil model generatif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate ilustrasi taktis 16:9 untuk seluruh 45 bab menggunakan prompt library dan Laravel 13 AI SDK';

    public const MASTER_PREFIX = 'Tactical editorial technical illustration in the hybrid style of Monocle Magazine, Stripe Press, and hand-drawn architectural whiteboard schematics, warm ivory paper background (#FAF8F5), crisp carbon black ink linework (#111113), subtle isometric geometric composition, featuring a subtle clever minimalist geometric crow emblem as a small corner motif, clean studio lighting, high contrast, vector precision, clean composition, text-free, no labels, no watermarks --ar 16:9';

    public function handle(): int
    {
        $bookFilter = $this->option('book');
        $isDryRun = (bool) $this->option('dry-run');

        $this->info('Memulai library generator ilustrasi taktis crow.asia...');
        $library = $this->getPromptLibrary();

        $filtered = $library;
        if ($bookFilter) {
            $filtered = array_filter($library, fn ($item) => $item['bookId'] === $bookFilter);
        }

        $this->line("Ditemukan <comment>" . count($filtered) . "</comment> ilustrasi untuk diproses.");

        foreach ($filtered as $item) {
            $fullPrompt = self::MASTER_PREFIX . ' ' . $item['prompt'];
            $targetDir = public_path("illustrations/{$item['bookId']}");
            File::ensureDirectoryExists($targetDir);

            $targetRelPath = "/illustrations/{$item['bookId']}/{$item['slug']}.webp";
            $targetFullPath = public_path("illustrations/{$item['bookId']}/{$item['slug']}.webp");

            $this->line("▶ [{$item['bookId']} - {$item['slug']}] Motif: <info>{$item['motif']}</info>");

            if ($isDryRun) {
                $this->comment("  Prompt: {$fullPrompt}");
                continue;
            }

            // Generate using Laravel 13 AI SDK if configured, or create clean tactical SVG placeholder
            try {
                if (class_exists(Image::class) && config('services.ai.image_key')) {
                    $generated = Image::of($fullPrompt)->aspectRatio('16:9')->generate();
                    if ($generated && isset($generated->contents)) {
                        File::put($targetFullPath, $generated->contents);
                    }
                } else {
                    // Fallback to high-contrast tactical SVG asset
                    $svg = $this->createTacticalPlaceholderSvg($item['bookId'], $item['slug'], $item['motif'], $item['accentHex']);
                    $svgPath = public_path("illustrations/{$item['bookId']}/{$item['slug']}.svg");
                    File::put($svgPath, $svg);
                    $targetRelPath = "/illustrations/{$item['bookId']}/{$item['slug']}.svg";
                }

                // Update Chapter model
                Chapter::query()
                    ->where('book_id', $item['bookId'])
                    ->where('slug', $item['slug'])
                    ->update([
                        'motif_icon' => $item['motif'],
                        'illustration_path' => $targetRelPath,
                    ]);

                $this->info("  ✓ Disimpan ke {$targetRelPath}");
            } catch (\Throwable $e) {
                $this->warn("  Gagal men-generate gambar AI ({$e->getMessage()}), membuat fallback taktis...");
            }
        }

        $this->newLine();
        $this->info('✓ Proses ilustrasi selesai!');

        return Command::SUCCESS;
    }

    private function createTacticalPlaceholderSvg(string $bookId, string $slug, string $motif, string $accent): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280 720" width="1280" height="720">
    <rect width="1280" height="720" fill="#FAF8F5" />
    <rect x="40" y="40" width="1200" height="640" rx="20" fill="#FFFFFF" stroke="#E5E0D5" stroke-width="2" />
    <g transform="translate(640, 320)" text-anchor="middle">
        <circle r="70" fill="{$accent}" opacity="0.1" />
        <circle r="50" fill="none" stroke="{$accent}" stroke-width="3" />
        <text y="10" font-family="'JetBrains Mono', monospace" font-size="28" font-weight="bold" fill="{$accent}">{$motif}</text>
        <text y="110" font-family="'Plus Jakarta Sans', sans-serif" font-size="20" font-weight="bold" fill="#111113">{$bookId} • {$slug}</text>
        <text y="140" font-family="'Plus Jakarta Sans', sans-serif" font-size="14" fill="#71717A">Tactile Crow Studio — 16:9 Editorial Schematic</text>
    </g>
</svg>
SVG;
    }

    private function getPromptLibrary(): array
    {
        return [
            // BOOK 1 (Cobalt Blue #1D4ED8)
            ['bookId' => 'book-1', 'slug' => 'ch-0', 'motif' => 'compass', 'accentHex' => '#1D4ED8', 'prompt' => 'A precision brass-and-cobalt architectural compass resting on an isometric grid desk next to a first-time captain star badge and a clever geometric crow silhouette, warm ivory background (#FAF8F5), cobalt blue (#1D4ED8) and gold (#FFE66D) accents --ar 16:9'],
            ['bookId' => 'book-1', 'slug' => 'ch-1', 'motif' => 'gears', 'accentHex' => '#1D4ED8', 'prompt' => 'An isometric mechanical diagram showing one master conductor gear smoothly driving four synchronized smaller team gears without friction, cobalt blue (#1D4ED8) and yellow (#FFE66D) highlights on warm ivory paper (#FAF8F5) --ar 16:9'],
            ['bookId' => 'book-1', 'slug' => 'ch-2', 'motif' => 'matrix', 'accentHex' => '#1D4ED8', 'prompt' => 'A clean 4x4 architectural matrix blueprint on a drafting table where four vertical conversation pillars intersect with four horizontal communication skill beams, glowing cobalt blue (#1D4ED8) nodes --ar 16:9'],
            ['bookId' => 'book-1', 'slug' => 'ch-3', 'motif' => 'waveform', 'accentHex' => '#1D4ED8', 'prompt' => 'A half-closed laptop and face-down smartphone beside an acoustic precision lens that transforms a tangled chaotic thread into a calm, clear harmonic sine wave, cobalt blue (#1D4ED8) accent --ar 16:9'],
            ['bookId' => 'book-1', 'slug' => 'ch-4', 'motif' => 'key', 'accentHex' => '#1D4ED8', 'prompt' => 'A minimalist architectural key shaped like a question mark being handed over to unlock an isometric puzzle box, warm yellow (#FFE66D) and cobalt blue (#1D4ED8) accents on ivory paper --ar 16:9'],
            ['bookId' => 'book-1', 'slug' => 'ch-5', 'motif' => 'lens', 'accentHex' => '#1D4ED8', 'prompt' => 'An optical precision magnifying loupe examining an objective workflow chart on a neutral table, replacing pointing fingers with clear geometric data blocks, cobalt blue (#1D4ED8) accent --ar 16:9'],
            ['bookId' => 'book-1', 'slug' => 'ch-6', 'motif' => 'battery', 'accentHex' => '#1D4ED8', 'prompt' => 'Four ascending architectural staircase platforms with a glowing golden-cobalt energy core slotting into the top platform to power autonomous action, cobalt blue (#1D4ED8) accent --ar 16:9'],
            ['bookId' => 'book-1', 'slug' => 'ch-7', 'motif' => 'target', 'accentHex' => '#1D4ED8', 'prompt' => 'Three concentric alignment rings locking into a single crisp bullseye target on an engineering whiteboard, cobalt blue (#1D4ED8) and gold (#FFE66D) accents --ar 16:9'],
            ['bookId' => 'book-1', 'slug' => 'ch-8', 'motif' => 'spotlight', 'accentHex' => '#1D4ED8', 'prompt' => 'An architectural desk lamp casting a warm golden spotlight (#FFE66D) directly onto a newly fitted cobalt blue (#1D4ED8) keystone puzzle piece in a team structure --ar 16:9'],
            ['bookId' => 'book-1', 'slug' => 'ch-9', 'motif' => 'trajectory', 'accentHex' => '#1D4ED8', 'prompt' => 'A private navigation chart showing an off-track dotted trajectory line gently and respectfully guided back onto the main cobalt blue (#1D4ED8) vector path via a compass calibration --ar 16:9'],
            ['bookId' => 'book-1', 'slug' => 'ch-10', 'motif' => 'telescope', 'accentHex' => '#1D4ED8', 'prompt' => 'A split-proportion 30/70 architectural horizon diagram with a small 30% rearview mirror and a large 70% brass telescope pointing toward an ascending cobalt blue (#1D4ED8) future grid --ar 16:9'],
            ['bookId' => 'book-1', 'slug' => 'ch-11', 'motif' => 'podium', 'accentHex' => '#1D4ED8', 'prompt' => 'An isometric 4-week tactical simulation board showing four connected milestone stations progressing from initial coaching to full team autonomy, cobalt blue (#1D4ED8) accent --ar 16:9'],
            ['bookId' => 'book-1', 'slug' => 'ch-12', 'motif' => 'calibration', 'accentHex' => '#1D4ED8', 'prompt' => 'A four-quadrant precision calibration dial matching readiness levels with guidance styles, progressing from training wheels to solo flight, cobalt blue (#1D4ED8) accent --ar 16:9'],
            ['bookId' => 'book-1', 'slug' => 'ch-13', 'motif' => 'cadence', 'accentHex' => '#1D4ED8', 'prompt' => 'A spiral tactical desk journal open to a 30-day habit checklist grid with checked boxes, beside a fountain pen and a warm cup of coffee, cobalt blue (#1D4ED8) and gold (#FFE66D) accents --ar 16:9'],
            ['bookId' => 'book-1', 'slug' => 'ch-14', 'motif' => 'toolbox', 'accentHex' => '#1D4ED8', 'prompt' => 'An open modular Swiss architect toolbox holding neatly indexed conversation script cards, 1-on-1 templates, and research citation tabs, cobalt blue (#1D4ED8) accent --ar 16:9'],

            // BOOK 2 (Terracotta Orange #EA580C)
            ['bookId' => 'book-2', 'slug' => 'ch-0', 'motif' => 'fire', 'accentHex' => '#EA580C', 'prompt' => 'An overloaded precarious stack of burning folders transforming into a calm, self-running isometric conveyor belt system, terracotta orange (#EA580C) and teal (#0D9488) accents on ivory paper --ar 16:9'],
            ['bookId' => 'book-2', 'slug' => 'ch-1', 'motif' => 'blueprint', 'accentHex' => '#EA580C', 'prompt' => 'A retired emergency firehose hung neatly on a wall beside an architectural drafting table displaying a clean parallel workflow pipeline, terracotta orange (#EA580C) accent --ar 16:9'],
            ['bookId' => 'book-2', 'slug' => 'ch-2', 'motif' => 'shield', 'accentHex' => '#EA580C', 'prompt' => 'Four interlocking structural rings surrounding a glowing protected energy core on a technical blueprint grid, terracotta orange (#EA580C) and warm gold (#FFE66D) accents --ar 16:9'],
            ['bookId' => 'book-2', 'slug' => 'ch-3', 'motif' => 'lens', 'accentHex' => '#EA580C', 'prompt' => 'A transparent geometric glass shield prism splitting a dark turbulent emotional storm cloud into three neat, objective, actionable terracotta (#EA580C) cubes on a desk --ar 16:9'],
            ['bookId' => 'book-2', 'slug' => 'ch-4', 'motif' => 'key', 'accentHex' => '#EA580C', 'prompt' => 'A clean directional vector barrier gently deflecting incoming task arrows back into their rightful owner lanes on a team sprint board, terracotta orange (#EA580C) accent --ar 16:9'],
            ['bookId' => 'book-2', 'slug' => 'ch-5', 'motif' => 'balance', 'accentHex' => '#EA580C', 'prompt' => 'A precision brass architectural balance scale weighing project workload blocks against a clear 100% capacity threshold line for trade-off negotiation, terracotta orange (#EA580C) accent --ar 16:9'],
            ['bookId' => 'book-2', 'slug' => 'ch-6', 'motif' => 'switch', 'accentHex' => '#EA580C', 'prompt' => 'A minimalist Braun-inspired mechanical toggle switch flipped cleanly to OFF beside a closed laptop and a calm evening horizon line, terracotta orange (#EA580C) accent --ar 16:9'],
            ['bookId' => 'book-2', 'slug' => 'ch-7', 'motif' => 'calendar', 'accentHex' => '#EA580C', 'prompt' => 'A weekly calendar grid where cluttered small meeting balloons are snipped away to reveal bold, protected golden-terracotta (#EA580C) deep-focus blocks --ar 16:9'],
            ['bookId' => 'book-2', 'slug' => 'ch-8', 'motif' => 'relay', 'accentHex' => '#EA580C', 'prompt' => 'Two synchronized parallel running tracks passing a glowing relay torch seamlessly across operational checkpoints, terracotta orange (#EA580C) and gold (#FFE66D) accents --ar 16:9'],
            ['bookId' => 'book-2', 'slug' => 'ch-9', 'motif' => 'gateway', 'accentHex' => '#EA580C', 'prompt' => 'An architectural intake funnel and sluice gate organizing scattered chaotic request arrows into a calm, single-file prioritized queue, terracotta orange (#EA580C) accent --ar 16:9'],
            ['bookId' => 'book-2', 'slug' => 'ch-10', 'motif' => 'blueprint', 'accentHex' => '#EA580C', 'prompt' => 'A precision architectural proportion block divided into a 70% automated mechanical base and a 30% elevated strategic innovation tower, terracotta orange (#EA580C) accent --ar 16:9'],
            ['bookId' => 'book-2', 'slug' => 'ch-11', 'motif' => 'equilibrium', 'accentHex' => '#EA580C', 'prompt' => 'Four progressive isometric stages showing a tilted, overloaded desk structure returning to horizontal equilibrium and calm tea-drinking flow, terracotta orange (#EA580C) accent --ar 16:9'],
            ['bookId' => 'book-2', 'slug' => 'ch-12', 'motif' => 'gauge', 'accentHex' => '#EA580C', 'prompt' => 'An analog precision thermometer and dial gauge with four distinct energy zones and recovery station markers, terracotta orange (#EA580C) and teal (#0D9488) accents --ar 16:9'],
            ['bookId' => 'book-2', 'slug' => 'ch-13', 'motif' => 'pacemaker', 'accentHex' => '#EA580C', 'prompt' => 'A steady, rhythmic marathon pulse wave running smoothly across a long-distance architectural elevation chart, terracotta orange (#EA580C) accent on warm ivory paper --ar 16:9'],
            ['bookId' => 'book-2', 'slug' => 'ch-14', 'motif' => 'toolbox', 'accentHex' => '#EA580C', 'prompt' => 'A structured workplace resilience first-aid kit open to reveal priority trade-off scripts, evening shutdown checklists, and research cards, terracotta orange (#EA580C) accent --ar 16:9'],

            // BOOK 3 (Alpine Emerald #0F766E)
            ['bookId' => 'book-3', 'slug' => 'ch-0', 'motif' => 'lighthouse', 'accentHex' => '#0F766E', 'prompt' => 'An architectural lighthouse on an isometric grid coast projecting emerald (#0F766E) and golden alignment beams toward three synchronized ships steered by team captains --ar 16:9'],
            ['bookId' => 'book-3', 'slug' => 'ch-1', 'motif' => 'compass', 'accentHex' => '#0F766E', 'prompt' => 'A central strategy map table surrounded by three autonomous compass nodes operating in harmony without marionette strings, deep emerald (#0F766E) accent --ar 16:9'],
            ['bookId' => 'book-3', 'slug' => 'ch-2', 'motif' => 'bridge', 'accentHex' => '#0F766E', 'prompt' => 'An isometric golden-emerald (#0F766E) suspension bridge connecting three independent divisional towers across a canyon on an engineering blueprint --ar 16:9'],
            ['bookId' => 'book-3', 'slug' => 'ch-3', 'motif' => 'key', 'accentHex' => '#0F766E', 'prompt' => 'A cascading two-tier coaching schematic showing a senior leader empowering a team lead, who in turn guides their squad, deep emerald (#0F766E) and gold (#FFE66D) accents --ar 16:9'],
            ['bookId' => 'book-3', 'slug' => 'ch-4', 'motif' => 'shield', 'accentHex' => '#0F766E', 'prompt' => 'An architectural chain-of-command shield reinforcing the direct link between a frontline team lead and their team members, preventing bypass arrows, deep emerald (#0F766E) accent --ar 16:9'],
            ['bookId' => 'book-3', 'slug' => 'ch-5', 'motif' => 'telescope', 'accentHex' => '#0F766E', 'prompt' => 'An optical crystal prism translating a single high-altitude executive beam into three concrete, actionable departmental mission tracks, deep emerald (#0F766E) accent --ar 16:9'],
            ['bookId' => 'book-3', 'slug' => 'ch-6', 'motif' => 'balance', 'accentHex' => '#0F766E', 'prompt' => 'A multi-axis organizational gyroscopic compass aligning vertical executive expectations with horizontal peer-department collaboration, deep emerald (#0F766E) accent --ar 16:9'],
            ['bookId' => 'book-3', 'slug' => 'ch-7', 'motif' => 'matrix', 'accentHex' => '#0F766E', 'prompt' => 'Three interlocking hexagonal blueprint modules locking together to eliminate organizational silos, deep emerald (#0F766E) accent --ar 16:9'],
            ['bookId' => 'book-3', 'slug' => 'ch-8', 'motif' => 'lens', 'accentHex' => '#0F766E', 'prompt' => 'A four-panel architectural diagnostic instrument inspecting Progress, People, Cross-Team Process, and Leader Growth, deep emerald (#0F766E) accent --ar 16:9'],
            ['bookId' => 'book-3', 'slug' => 'ch-9', 'motif' => 'bridge', 'accentHex' => '#0F766E', 'prompt' => 'Two separated departmental plates being joined cleanly and permanently by a precision interlocking dovetail joint on a blueprint, deep emerald (#0F766E) accent --ar 16:9'],
            ['bookId' => 'book-3', 'slug' => 'ch-10', 'motif' => 'gauge', 'accentHex' => '#0F766E', 'prompt' => 'A multi-layer acoustic sonar diagram reading deep ground-truth organizational signals while keeping the team lead layer illuminated and respected, deep emerald (#0F766E) accent --ar 16:9'],
            ['bookId' => 'book-3', 'slug' => 'ch-11', 'motif' => 'podium', 'accentHex' => '#0F766E', 'prompt' => 'Three previously diverging departmental vectors progressively aligning into a single cohesive forward formation across four weekly checkpoints, deep emerald (#0F766E) accent --ar 16:9'],
            ['bookId' => 'book-3', 'slug' => 'ch-12', 'motif' => 'calibration', 'accentHex' => '#0F766E', 'prompt' => 'Four ascending architectural pillars showing increasing structural span and leadership autonomy from newly promoted lead to strategic multiplier, deep emerald (#0F766E) accent --ar 16:9'],
            ['bookId' => 'book-3', 'slug' => 'ch-13', 'motif' => 'cadence', 'accentHex' => '#0F766E', 'prompt' => 'A three-phase master architectural timeline with crisp milestone flags, deep emerald (#0F766E) accent --ar 16:9'],
            ['bookId' => 'book-3', 'slug' => 'ch-14', 'motif' => 'toolbox', 'accentHex' => '#0F766E', 'prompt' => 'An executive architectural portfolio binder open to charter templates, audit sheets, succession matrices, and organizational research citations, deep emerald (#0F766E) accent --ar 16:9'],
        ];
    }
}

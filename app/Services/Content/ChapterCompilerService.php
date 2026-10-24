<?php

namespace App\Services\Content;

use App\Enums\ContentRegister;
use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class ChapterCompilerService
{
    private HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $allowedElements = [
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'p', 'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'blockquote', 'pre', 'code', 'details', 'summary', 'aside', 'span',
            'sup', 'sub', 'hr', 'strong', 'em', 'a', 'div', 'section',
        ];

        $config = (new HtmlSanitizerConfig())
            ->allowSafeElements()
            ->allowElement('aside', ['class'])
            ->allowElement('details', ['class', 'open', 'data-copy-script'])
            ->allowElement('summary', ['class'])
            ->allowElement('span', ['class', 'data-default'])
            ->allowAttribute('class', $allowedElements)
            ->allowAttribute('id', $allowedElements)
            ->allowAttribute('data-default', ['span'])
            ->allowAttribute('data-copy-script', ['details'])
            ->allowRelativeLinks()
            ->allowLinkSchemes(['http', 'https', 'mailto']);

        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function compile(
        string $rawMarkdown,
        string $title,
        string $locale,
        string|ContentRegister $register = 'standard',
        string $bookId = 'book-1',
        int $chapterIndex = 0,
    ): CompiledChapterDto {
        $regValue = $register instanceof ContentRegister ? $register->value : $register;
        $defaultName = ($locale === 'en') ? 'Alex' : 'Budi';

        // 1. Transform GitHub alerts/callouts before markdown rendering
        $processedMd = $this->transformCallouts($rawMarkdown);

        // 2. Extract checklists from raw markdown
        $extractedChecklists = $this->extractChecklists($rawMarkdown);

        // 3. Compile Markdown to HTML
        $html = Str::markdown($processedMd);

        // 4. Transform <details> drawers to include tactical classes & copy script attribute
        $html = preg_replace_callback(
            '/<details(\s*[^>]*)>/i',
            function ($matches) {
                $attrs = $matches[1];
                if (!str_contains($attrs, 'class=')) {
                    $attrs .= ' class="crow-script-drawer"';
                }
                if (!str_contains($attrs, 'data-copy-script=')) {
                    $attrs .= ' data-copy-script="true"';
                }
                return '<details' . $attrs . '>';
            },
            $html
        );

        // 5. Replace teammate token placeholders
        $html = $this->replaceTeammateTokens($html, $defaultName);

        // 6. Strict HTML sanitization
        $compiledHtml = $this->sanitizer->sanitize($html);

        // 7. Check if chapter has script drawers
        $hasScripts = str_contains($compiledHtml, 'crow-script-drawer');

        // 8. Generate safe preview HTML (first 3 block paragraphs/blockquotes)
        $safePreviewHtml = $this->generateSafePreview($compiledHtml);

        // 9. Auto-extract Remotion 4-step scene props
        $remotionSceneProps = $this->extractRemotionSceneProps(
            rawMarkdown: $rawMarkdown,
            bookId: $bookId,
            chapterIndex: $chapterIndex,
            chapterTitle: $title,
            locale: $locale,
            register: $regValue,
            teammateName: $defaultName,
            extractedChecklists: $extractedChecklists,
        );

        return new CompiledChapterDto(
            compiledHtml: $compiledHtml,
            safePreviewHtml: $safePreviewHtml,
            hasScripts: $hasScripts,
            extractedChecklists: $extractedChecklists,
            remotionSceneProps: $remotionSceneProps,
        );
    }

    private function transformCallouts(string $markdown): string
    {
        return preg_replace_callback(
            '/^>\s*\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\]\s*(.*?)\n((?:^>.*(?:\n|$))*)/m',
            function ($matches) {
                $type = strtolower($matches[1]);
                $title = trim($matches[2]);
                $lines = explode("\n", $matches[3]);
                $bodyLines = [];
                foreach ($lines as $line) {
                    $bodyLines[] = preg_replace('/^>\s?/', '', $line);
                }
                $body = trim(implode("\n", $bodyLines));
                $header = $title ? "**{$matches[1]}: {$title}**\n\n" : "**{$matches[1]}**\n\n";

                return "<aside class=\"crow-callout crow-callout-{$type}\">\n\n" . $header . $body . "\n\n</aside>\n";
            },
            $markdown
        );
    }

    private function replaceTeammateTokens(string $content, string $defaultName): string
    {
        $tokenHtml = "<span class=\"teammate-token\" data-default=\"{$defaultName}\">{$defaultName}</span>";
        $patterns = [
            '/\[Nama\]/',
            '/\[Nama Staf\]/i',
            '/\[Nama Anggota Tim\]/i',
            '/\[Teammate Name\]/i',
            '/\{\{teammateName\}\}/',
        ];

        return preg_replace($patterns, $tokenHtml, $content);
    }

    private function generateSafePreview(string $html): string
    {
        preg_match_all('/<(p|blockquote)[^>]*>.*?<\/\1>/si', $html, $matches);

        if (!empty($matches[0])) {
            $previewBlocks = array_slice($matches[0], 0, 3);
            return implode("\n", $previewBlocks);
        }

        return Str::limit(strip_tags($html), 300);
    }

    private function extractChecklists(string $markdown): array
    {
        preg_match_all('/^[-*]\s+\[([ xX])\]\s+(.+)$/m', $markdown, $matches, PREG_SET_ORDER);
        $checklists = [];

        foreach ($matches as $i => $m) {
            $checklists[] = [
                'itemIndex' => $i,
                'labelHtml' => trim(strip_tags(Str::markdown(trim($m[2])))),
                'isCompleted' => strtolower($m[1]) === 'x',
            ];
        }

        return $checklists;
    }

    private function extractRemotionSceneProps(
        string $rawMarkdown,
        string $bookId,
        int $chapterIndex,
        string $chapterTitle,
        string $locale,
        string $register,
        string $teammateName,
        array $extractedChecklists,
    ): array {
        // Find subheadings
        preg_match_all('/^###\s+(.+)$/m', $rawMarkdown, $subheadMatches);
        $subheads = $subheadMatches[1] ?? [];

        // Step 0: Masalah & Konteks Lapangan
        $step0Headline = $subheads[0] ?? 'Masalah & Konteks Lapangan';
        $step0Sub = 'Tantangan nyata yang dihadapi saat memimpin tim di lapangan.';

        // Step 1: Kerangka Taktis Utama
        $step1Headline = $subheads[1] ?? 'Kerangka Taktis';
        $step1Sub = 'Rumus dan langkah terstruktur untuk mengeksekusi solusi.';

        // Step 2: Simulasi Skrip 1-on-1
        $step2Headline = 'Simulasi Skrip 1-on-1';
        $step2Sub = 'Praktik percakapan empat mata yang siap disalin dan diterapkan.';
        $leaderText = "Halo {{teammateName}}, yuk kita selaraskan target dan ekspektasi kerjaan ini.";
        $staffText = "Siap, apa saja poin utama yang perlu diperhatikan?";

        if (preg_match('/<details>.*?```(?:text)?\s*(.*?)\s*```.*?<\/details>/s', $rawMarkdown, $dialogueMatch)) {
            $dialogueRaw = trim($dialogueMatch[1]);
            $lines = explode("\n", $dialogueRaw);
            if (!empty($lines[0])) {
                $leaderText = Str::limit(trim($lines[0], '" '), 160);
            }
        }

        // Step 3: Target Praktik & Checklist
        $step3Headline = 'Target Praktik Minggu Ini';
        $step3Sub = 'Langkah konkret yang harus dituntaskan langsung di meja kerja.';
        $bullets = [];
        foreach (array_slice($extractedChecklists, 0, 3) as $c) {
            $bullets[] = Str::limit($c['labelHtml'], 70);
        }
        if (empty($bullets)) {
            $bullets = [
                'Tinjau kembali ekspektasi target bersama tim',
                'Jalankan kalibrasi tugas secara empat mata',
                'Dokumentasikan kesepakatan di lembar kerja',
            ];
        }

        return [
            'bookId' => $bookId,
            'chapterIndex' => $chapterIndex,
            'chapterTitle' => $chapterTitle,
            'motifIcon' => 'compass',
            'locale' => $locale,
            'register' => $register,
            'teammateName' => $teammateName,
            'accentHex' => $bookId === 'book-2' ? '#EA580C' : ($bookId === 'book-3' ? '#0F766E' : '#1D4ED8'),
            'steps' => [
                [
                    'stepIndex' => 0,
                    'label' => '01. Masalah & Konteks',
                    'startFrame' => 0,
                    'durationFrames' => 135,
                    'headline' => $step0Headline,
                    'subheadline' => $step0Sub,
                ],
                [
                    'stepIndex' => 1,
                    'label' => '02. Kerangka Taktis',
                    'startFrame' => 135,
                    'durationFrames' => 135,
                    'headline' => $step1Headline,
                    'subheadline' => $step1Sub,
                ],
                [
                    'stepIndex' => 2,
                    'label' => '03. Simulasi Skrip',
                    'startFrame' => 270,
                    'durationFrames' => 135,
                    'headline' => $step2Headline,
                    'subheadline' => $step2Sub,
                    'dialogue' => [
                        'leaderSpeaker' => 'Leader',
                        'leaderText' => $leaderText,
                        'staffSpeaker' => 'Tim',
                        'staffText' => $staffText,
                    ],
                ],
                [
                    'stepIndex' => 3,
                    'label' => '04. Checklist Praktik',
                    'startFrame' => 405,
                    'durationFrames' => 135,
                    'headline' => $step3Headline,
                    'subheadline' => $step3Sub,
                    'bullets' => $bullets,
                ],
            ],
        ];
    }
}

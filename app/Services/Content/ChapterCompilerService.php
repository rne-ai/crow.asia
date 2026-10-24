<?php

namespace App\Services\Content;

use App\Enums\ContentRegister;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class ChapterCompilerService
{
    private HtmlSanitizer $sanitizer;
    private MarkdownConverter $converter;

    public function __construct()
    {
        // 1. Configure League CommonMark with GFM Tables & Footnotes
        $environment = new Environment([
            'footnote' => [
                'backref_class' => 'footnote-backref',
                'backref_symbol' => '↩',
                'container_add_hr' => true,
                'container_class' => 'footnotes',
                'ref_class' => 'footnote-ref',
            ],
            'table' => [
                'wrap' => [
                    'enabled' => true,
                    'tag' => 'div',
                    'attributes' => ['class' => 'table-responsive'],
                ],
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new FootnoteExtension());

        $this->converter = new MarkdownConverter($environment);

        // 2. Configure Symfony HtmlSanitizer
        $allowedElements = [
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'p', 'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'blockquote', 'pre', 'code', 'details', 'summary', 'aside', 'span',
            'sup', 'sub', 'hr', 'strong', 'em', 'a', 'div', 'section', 'ins', 'mark',
        ];

        $config = (new HtmlSanitizerConfig())
            ->allowSafeElements()
            ->allowElement('aside', ['class'])
            ->allowElement('details', ['class', 'open', 'data-copy-script'])
            ->allowElement('summary', ['class'])
            ->allowElement('span', ['class', 'data-default'])
            ->allowElement('pre', ['class'])
            ->allowElement('code', ['class'])
            ->allowElement('sup', ['id'])
            ->allowElement('div', ['class', 'id', 'role'])
            ->allowElement('li', ['class', 'id', 'role'])
            ->allowElement('a', ['class', 'id', 'role', 'rev', 'href'])
            ->allowElement('th', ['align', 'class'])
            ->allowElement('td', ['align', 'class'])
            ->allowElement('ins', ['class'])
            ->allowElement('mark', ['class'])
            ->allowAttribute('class', $allowedElements)
            ->allowAttribute('id', $allowedElements)
            ->allowAttribute('role', $allowedElements)
            ->allowAttribute('rev', ['a'])
            ->allowAttribute('align', ['th', 'td'])
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

        // Strip duplicate initial heading if it matches or starts with ##
        $processedMd = preg_replace('/^##\s+.*?\r?\n+/m', '', $processedMd, 1);

        // 2. Extract checklists from raw markdown
        $extractedChecklists = $this->extractChecklists($rawMarkdown);

        // 3. Compile Markdown to HTML via League CommonMark (with Tables and Footnotes)
        $html = (string) $this->converter->convert($processedMd);

        // 4. Wrap Mermaid diagram blocks with header badge
        $html = preg_replace(
            '/<pre><code class="language-mermaid">(.*?)<\/code><\/pre>/s',
            '<div class="crow-mermaid-block"><div class="crow-mermaid-header"><span class="crow-mermaid-badge">📊 DIAGRAM ALUR KERJA • SKEMA TAKTIS</span></div><pre class="mermaid">$1</pre></div>',
            $html
        );

        // 5. Transform <details> script drawers to include tactical classes & copy script attribute
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

        // 6. Replace teammate token placeholders
        $html = $this->replaceTeammateTokens($html, $defaultName);

        // 7. Strict HTML sanitization
        $compiledHtml = $this->sanitizer->sanitize($html);

        // 8. Check if chapter has script drawers
        $hasScripts = str_contains($compiledHtml, 'crow-script-drawer');

        // 9. Generate safe preview HTML (first 3 block paragraphs/blockquotes)
        $safePreviewHtml = $this->generateSafePreview($compiledHtml);

        // 10. Auto-extract Remotion 4-step scene props
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
            '/^>\s*\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\](?:\s+(.+?))?\r?\n((?:^>.*(?:\r?\n|$))*)/m',
            function ($matches) {
                $type = strtolower($matches[1]);
                $title = isset($matches[2]) ? trim($matches[2]) : '';
                $title = trim(ltrim($title, '> '));

                $lines = preg_split('/\r?\n/', $matches[3]);
                $bodyLines = [];
                foreach ($lines as $line) {
                    $cleaned = preg_replace('/^>\s?/', '', $line);
                    if ($cleaned !== null && $cleaned !== '') {
                        $bodyLines[] = $cleaned;
                    }
                }
                $body = trim(implode("\n", $bodyLines));

                $iconMap = [
                    'note' => '📌',
                    'tip' => '💡',
                    'important' => '⚡',
                    'warning' => '⚠️',
                    'caution' => '🛑',
                ];
                $icon = $iconMap[$type] ?? '📌';
                $typeLabel = strtoupper($matches[1]);

                $headerText = $title ? "{$icon} {$typeLabel}: {$title}" : "{$icon} {$typeLabel}";

                return "<aside class=\"crow-callout crow-callout-{$type}\">\n\n<div class=\"crow-callout-title\">{$headerText}</div>\n\n{$body}\n\n</aside>\n\n";
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
                'labelHtml' => trim(strip_tags((string) $this->converter->convert(trim($m[2])))),
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
        preg_match_all('/^###\s+(.+)$/m', $rawMarkdown, $subheadMatches);
        $subheads = $subheadMatches[1] ?? [];

        $step0Headline = $subheads[0] ?? 'Masalah & Konteks Lapangan';
        $step0Sub = 'Tantangan nyata yang dihadapi saat memimpin tim di lapangan.';

        $step1Headline = $subheads[1] ?? 'Kerangka Taktis';
        $step1Sub = 'Rumus dan langkah terstruktur untuk mengeksekusi solusi.';

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

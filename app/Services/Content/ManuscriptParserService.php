<?php

namespace App\Services\Content;

class ManuscriptParserService
{
    /**
     * Parse raw manuscript content into exactly 15 chapters (index 0..14).
     *
     * @return array<int, array{index: int, slug: string, title: string, rawMarkdown: string}>
     */
    public function parse(string $content): array
    {
        // 1. Extract global footnotes definitions
        preg_match_all('/^\[\^([^\]]+)\]:\s*(.+)$/m', $content, $fnMatches, PREG_SET_ORDER);
        $globalFootnotes = [];
        foreach ($fnMatches as $m) {
            $globalFootnotes[$m[1]] = trim($m[2]);
        }

        // 2. Split content by Level-2 headings (## )
        $sections = preg_split('/^##\s+/m', $content);
        array_shift($sections); // Remove preamble before first ##

        $chapters = [];
        foreach ($sections as $s) {
            $firstLineBreak = strpos($s, "\n");
            if ($firstLineBreak === false) {
                $title = trim($s);
                $body = '';
            } else {
                $title = trim(substr($s, 0, $firstLineBreak));
                $body = trim(substr($s, $firstLineBreak));
            }

            // Ignore Table of Contents
            if (preg_match('/(daftar isi|table of contents)/i', $title)) {
                continue;
            }

            // Merge Research Citations / Appendix Bibliographies into Chapter 14 (Lampiran)
            if (preg_match('/(rujukan riset|daftar pustaka|primary research citations)/i', $title) && count($chapters) >= 15) {
                $lastIndex = count($chapters) - 1;
                $chapters[$lastIndex]['rawMarkdown'] .= "\n\n### " . $title . "\n\n" . $body;
                continue;
            }

            $index = count($chapters);
            $slug = "ch-{$index}";
            $rawMarkdown = "## " . $title . "\n\n" . $body;

            $chapters[] = [
                'index' => $index,
                'slug' => $slug,
                'title' => $this->cleanTitle($title),
                'rawMarkdown' => $rawMarkdown,
            ];
        }

        // 3. Inject referenced footnotes for self-contained chapter markdown
        foreach ($chapters as &$chapter) {
            $chapter['rawMarkdown'] = $this->injectFootnotes($chapter['rawMarkdown'], $globalFootnotes);
        }

        return $chapters;
    }

    private function cleanTitle(string $title): string
    {
        // Clean markdown emphasis characters for title field
        $clean = str_replace(['*', '_', '`'], '', $title);
        return trim($clean);
    }

    private function injectFootnotes(string $markdown, array $globalFootnotes): string
    {
        if (empty($globalFootnotes)) {
            return $markdown;
        }

        // Find inline footnote references like [^1], [^ccl]
        preg_match_all('/(?<!: )\[\^([^\]]+)\](?!\:)/', $markdown, $matches);
        $referencedKeys = array_unique($matches[1] ?? []);

        $injectedDefinitions = [];
        foreach ($referencedKeys as $key) {
            if (isset($globalFootnotes[$key])) {
                // Check if definition is already inside the markdown
                if (!preg_match('/^\[' . preg_quote("^{$key}", '/') . '\]:/m', $markdown)) {
                    $injectedDefinitions[] = "[^{$key}]: " . $globalFootnotes[$key];
                }
            }
        }

        if (!empty($injectedDefinitions)) {
            $markdown .= "\n\n" . implode("\n", $injectedDefinitions);
        }

        return $markdown;
    }
}

<?php

namespace App\Services\Content;

class CompiledChapterDto
{
    /**
     * @param array<int, array{itemIndex: int, labelHtml: string, isCompleted: boolean}> $extractedChecklists
     * @param array<string, mixed> $remotionSceneProps
     */
    public function __construct(
        public readonly string $compiledHtml,
        public readonly string $safePreviewHtml,
        public readonly bool $hasScripts,
        public readonly array $extractedChecklists,
        public readonly array $remotionSceneProps,
    ) {}
}

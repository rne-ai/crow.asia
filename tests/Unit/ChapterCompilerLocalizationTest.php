<?php

use App\Services\Content\ChapterCompilerService;

test('compiler generates english badges for en locale and indonesian badges for id locale', function () {
    $compiler = app(ChapterCompilerService::class);

    $enCompiled = $compiler->compile(
        rawMarkdown: "```mermaid\ngraph TD\nA-->B\n```\n\n- [ ] First action item\n\nSome text[^1]\n\n[^1]: Research citation footnote",
        bookId: 'book-1',
        chapterIndex: 1,
        title: 'Title',
        locale: 'en',
        register: 'standard'
    );

    expect($enCompiled->compiledHtml)->toContain('WORKFLOW DIAGRAM • TACTICAL SCHEMA');
    expect($enCompiled->compiledHtml)->toContain('PRACTICE ACTIONS • WORKSPACE STEPS');
    expect($enCompiled->compiledHtml)->toContain('Footnotes');

    $idCompiled = $compiler->compile(
        rawMarkdown: "```mermaid\ngraph TD\nA-->B\n```\n\n- [ ] Aksi meja kerja 1\n\nCatatan[^1]\n\n[^1]: Rujukan riset",
        bookId: 'book-1',
        chapterIndex: 1,
        title: 'Judul',
        locale: 'id',
        register: 'casual'
    );

    expect($idCompiled->compiledHtml)->toContain('DIAGRAM ALUR KERJA • SKEMA TAKTIS');
    expect($idCompiled->compiledHtml)->toContain('LATIHAN PRAKTIK • AKSI MEJA KERJA');
    expect($idCompiled->compiledHtml)->toContain('Catatan Kaki');
});

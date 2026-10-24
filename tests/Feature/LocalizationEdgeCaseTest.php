<?php

use App\Models\PricingTier;
use App\Models\SupportedLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    SupportedLocale::firstOrCreate(
        ['code' => 'en'],
        ['name' => 'English', 'native_name' => 'English', 'flag_emoji' => '🌐', 'has_dual_register' => false, 'is_active' => true]
    );

    SupportedLocale::firstOrCreate(
        ['code' => 'id'],
        ['name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'flag_emoji' => '🇮🇩', 'has_dual_register' => true, 'is_active' => true]
    );
});

test('invalid or inactive locale query gracefully falls back to id locale and IDR currency', function () {
    $response = $this->get('/?locale=invalid_locale_xyz');
    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('locale', 'id')
        ->where('currency', 'IDR')
    );
});

test('compiler accurately fetches localized badges for any supported locale', function () {
    $compiler = app(\App\Services\Content\ChapterCompilerService::class);
    $compiled = $compiler->compile(
        rawMarkdown: "```mermaid\ngraph TD\nA-->B\n```\n- [ ] Task 1",
        bookId: 'book-1',
        chapterIndex: 1,
        title: 'Title',
        locale: 'en',
        register: 'standard'
    );

    expect($compiled->compiledHtml)->toContain('WORKFLOW DIAGRAM • TACTICAL SCHEMA');
    expect($compiled->compiledHtml)->toContain('PRACTICE ACTIONS • WORKSPACE STEPS');
});

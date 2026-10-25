<?php

use Inertia\Testing\AssertableInertia as Assert;

test('landing page translations contain refined copywriting without jargon', function () {
    $response = $this->get('/?locale=id');
    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('translations.landing.hero.badge', 'PANDUAN PRAKTIS MEMIMPIN ORANG • BACA GRATIS BAB AWAL')
        ->where('translations.landing.hero.cta_free', 'Baca Gratis Pendahuluan & Bab 1')
        ->where('translations.landing.hero.cta_find_book', 'Cari Buku yang Cocok Untukmu ↓')
        ->has('translations.landing.hero.carousel.slides')
        ->where('translations.landing.quiz.header_badge', 'PANDUAN PILIH BUKU (30 DETIK)')
        ->where('translations.landing.catalog.status_free_sample', '🔓 GRATIS BAB AWAL')
        ->where('translations.landing.catalog.chapters_count_label', '13 Bab Praktis + Lampiran')
        ->has('translations.landing.comparison_table.rows')
        ->has('translations.landing.faq.items')
    );
});

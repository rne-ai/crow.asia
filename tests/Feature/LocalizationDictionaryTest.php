<?php

use Inertia\Testing\AssertableInertia as Assert;

test('inertia responses share translations dictionary, active locale, and currency', function () {
    $response = $this->get('/?locale=en');
    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Landing')
        ->where('locale', 'en')
        ->where('currency', 'USD')
        ->has('translations.nav.search_placeholder')
        ->has('translations.landing.hero.title')
    );
});

test('inertia responses fallback to id dictionary when locale is id', function () {
    $response = $this->get('/?locale=id');
    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Landing')
        ->where('locale', 'id')
        ->where('currency', 'IDR')
        ->where('translations.nav.search_placeholder', 'Cari Topik / Skrip...')
    );
});

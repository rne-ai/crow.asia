<?php

use Inertia\Testing\AssertableInertia as Assert;

test('catalog cards provide single start reading action and cta paragraph', function () {
    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('translations.landing.catalog.start_reading', 'Mulai Membaca')
        ->has('translations.landing.catalog.cta_paragraphs.book-1')
        ->has('translations.landing.catalog.cta_paragraphs.book-2')
        ->has('translations.landing.catalog.cta_paragraphs.book-3')
    );
});

<?php

use Inertia\Testing\AssertableInertia as Assert;

test('top navigation contains translated placeholder and auth links', function () {
    $response = $this->get('/?locale=en');
    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('locale', 'en')
        ->where('translations.nav.login', 'Sign In')
        ->where('translations.nav.search_placeholder', 'Search topics & scripts...')
        ->where('translations.nav.book_titles.book-1', 'VOL. 01: Mastering Team Leadership')
    );
});

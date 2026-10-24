<?php

use Inertia\Testing\AssertableInertia as Assert;

test('header navigation displays diagnosis first, then catalog, then pricing', function () {
    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('translations.nav.leadership_diagnosis', 'Diagnosis Kepemimpinan')
        ->where('translations.nav.playbooks_catalog', 'Katalog Playbook')
        ->where('translations.nav.pricing', 'Paket Harga')
    );
});

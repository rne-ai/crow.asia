<?php

use Inertia\Testing\AssertableInertia as Assert;

test('landing page inertia response does not contain register prop', function () {
    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Landing')
        ->missing('register')
    );
});

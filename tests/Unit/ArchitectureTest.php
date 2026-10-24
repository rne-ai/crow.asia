<?php

use App\Providers\Filament\AdminPanelProvider;

arch()->preset()->laravel()->ignoring([
    AdminPanelProvider::class,
]);

arch('app does not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->not->toBeUsed();

arch('enums are placed inside App\Enums')
    ->expect('App\Enums')
    ->toBeEnums();

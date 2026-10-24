<?php

namespace App\Filament\Resources\SupportedLocales\Pages;

use App\Filament\Resources\SupportedLocales\SupportedLocaleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSupportedLocale extends EditRecord
{
    protected static string $resource = SupportedLocaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\Patrons\Pages;

use App\Filament\Resources\Patrons\PatronResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPatron extends EditRecord
{
    protected static string $resource = PatronResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

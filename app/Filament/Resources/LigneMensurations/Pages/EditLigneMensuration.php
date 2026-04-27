<?php

namespace App\Filament\Resources\LigneMensurations\Pages;

use App\Filament\Resources\LigneMensurations\LigneMensurationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLigneMensuration extends EditRecord
{
    protected static string $resource = LigneMensurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

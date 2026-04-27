<?php

namespace App\Filament\Resources\FicheMesures\Pages;

use App\Filament\Resources\FicheMesures\FicheMesureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFicheMesure extends EditRecord
{
    protected static string $resource = FicheMesureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

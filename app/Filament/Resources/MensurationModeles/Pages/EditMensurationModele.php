<?php

namespace App\Filament\Resources\MensurationModeles\Pages;

use App\Filament\Resources\MensurationModeles\MensurationModeleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMensurationModele extends EditRecord
{
    protected static string $resource = MensurationModeleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

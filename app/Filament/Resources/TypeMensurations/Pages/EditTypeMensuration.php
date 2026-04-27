<?php

namespace App\Filament\Resources\TypeMensurations\Pages;

use App\Filament\Resources\TypeMensurations\TypeMensurationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTypeMensuration extends EditRecord
{
    protected static string $resource = TypeMensurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\FicheClients\Pages;

use App\Filament\Resources\FicheClients\FicheClientResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFicheClient extends EditRecord
{
    protected static string $resource = FicheClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

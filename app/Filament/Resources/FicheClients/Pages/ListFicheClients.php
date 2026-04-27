<?php

namespace App\Filament\Resources\FicheClients\Pages;

use App\Filament\Resources\FicheClients\FicheClientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFicheClients extends ListRecords
{
    protected static string $resource = FicheClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

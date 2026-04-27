<?php

namespace App\Filament\Resources\FicheMesures\Pages;

use App\Filament\Resources\FicheMesures\FicheMesureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFicheMesures extends ListRecords
{
    protected static string $resource = FicheMesureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

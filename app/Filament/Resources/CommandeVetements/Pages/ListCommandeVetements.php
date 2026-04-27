<?php

namespace App\Filament\Resources\CommandeVetements\Pages;

use App\Filament\Resources\CommandeVetements\CommandeVetementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommandeVetements extends ListRecords
{
    protected static string $resource = CommandeVetementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

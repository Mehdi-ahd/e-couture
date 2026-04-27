<?php

namespace App\Filament\Resources\ModeleVetements\Pages;

use App\Filament\Resources\ModeleVetements\ModeleVetementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListModeleVetements extends ListRecords
{
    protected static string $resource = ModeleVetementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

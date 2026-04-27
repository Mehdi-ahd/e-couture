<?php

namespace App\Filament\Resources\TypeVetements\Pages;

use App\Filament\Resources\TypeVetements\TypeVetementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTypeVetements extends ListRecords
{
    protected static string $resource = TypeVetementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

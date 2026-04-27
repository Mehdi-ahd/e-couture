<?php

namespace App\Filament\Resources\TypeMensurations\Pages;

use App\Filament\Resources\TypeMensurations\TypeMensurationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTypeMensurations extends ListRecords
{
    protected static string $resource = TypeMensurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

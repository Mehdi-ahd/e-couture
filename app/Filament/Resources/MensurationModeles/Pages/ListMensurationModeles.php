<?php

namespace App\Filament\Resources\MensurationModeles\Pages;

use App\Filament\Resources\MensurationModeles\MensurationModeleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMensurationModeles extends ListRecords
{
    protected static string $resource = MensurationModeleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

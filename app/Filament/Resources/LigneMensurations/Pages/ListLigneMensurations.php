<?php

namespace App\Filament\Resources\LigneMensurations\Pages;

use App\Filament\Resources\LigneMensurations\LigneMensurationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLigneMensurations extends ListRecords
{
    protected static string $resource = LigneMensurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

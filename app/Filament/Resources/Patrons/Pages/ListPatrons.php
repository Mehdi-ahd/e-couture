<?php

namespace App\Filament\Resources\Patrons\Pages;

use App\Filament\Resources\Patrons\PatronResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPatrons extends ListRecords
{
    protected static string $resource = PatronResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\PiecePatrons\Pages;

use App\Filament\Resources\PiecePatrons\PiecePatronResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPiecePatrons extends ListRecords
{
    protected static string $resource = PiecePatronResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

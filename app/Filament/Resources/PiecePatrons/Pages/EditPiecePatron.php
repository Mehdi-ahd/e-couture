<?php

namespace App\Filament\Resources\PiecePatrons\Pages;

use App\Filament\Resources\PiecePatrons\PiecePatronResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPiecePatron extends EditRecord
{
    protected static string $resource = PiecePatronResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

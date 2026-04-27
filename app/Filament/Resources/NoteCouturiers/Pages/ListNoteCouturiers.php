<?php

namespace App\Filament\Resources\NoteCouturiers\Pages;

use App\Filament\Resources\NoteCouturiers\NoteCouturierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNoteCouturiers extends ListRecords
{
    protected static string $resource = NoteCouturierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

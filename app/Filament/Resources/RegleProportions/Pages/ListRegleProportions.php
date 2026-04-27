<?php

namespace App\Filament\Resources\RegleProportions\Pages;

use App\Filament\Resources\RegleProportions\RegleProportionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRegleProportions extends ListRecords
{
    protected static string $resource = RegleProportionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\RegleProportions\Pages;

use App\Filament\Resources\RegleProportions\RegleProportionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRegleProportion extends EditRecord
{
    protected static string $resource = RegleProportionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

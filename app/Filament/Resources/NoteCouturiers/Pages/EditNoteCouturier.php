<?php

namespace App\Filament\Resources\NoteCouturiers\Pages;

use App\Filament\Resources\NoteCouturiers\NoteCouturierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNoteCouturier extends EditRecord
{
    protected static string $resource = NoteCouturierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

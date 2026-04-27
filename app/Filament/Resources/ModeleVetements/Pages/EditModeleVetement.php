<?php

namespace App\Filament\Resources\ModeleVetements\Pages;

use App\Filament\Resources\ModeleVetements\ModeleVetementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditModeleVetement extends EditRecord
{
    protected static string $resource = ModeleVetementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

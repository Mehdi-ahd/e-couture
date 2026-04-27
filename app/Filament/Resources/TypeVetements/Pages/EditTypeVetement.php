<?php

namespace App\Filament\Resources\TypeVetements\Pages;

use App\Filament\Resources\TypeVetements\TypeVetementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTypeVetement extends EditRecord
{
    protected static string $resource = TypeVetementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

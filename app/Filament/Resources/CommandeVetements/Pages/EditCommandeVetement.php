<?php

namespace App\Filament\Resources\CommandeVetements\Pages;

use App\Filament\Resources\CommandeVetements\CommandeVetementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCommandeVetement extends EditRecord
{
    protected static string $resource = CommandeVetementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\AnnotationPatrons\Pages;

use App\Filament\Resources\AnnotationPatrons\AnnotationPatronResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAnnotationPatron extends EditRecord
{
    protected static string $resource = AnnotationPatronResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

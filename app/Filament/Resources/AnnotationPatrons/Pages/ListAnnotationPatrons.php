<?php

namespace App\Filament\Resources\AnnotationPatrons\Pages;

use App\Filament\Resources\AnnotationPatrons\AnnotationPatronResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAnnotationPatrons extends ListRecords
{
    protected static string $resource = AnnotationPatronResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

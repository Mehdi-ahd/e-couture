<?php

namespace App\Filament\Resources\AnnotationPatrons;

use App\Filament\Resources\AnnotationPatrons\Pages\CreateAnnotationPatron;
use App\Filament\Resources\AnnotationPatrons\Pages\EditAnnotationPatron;
use App\Filament\Resources\AnnotationPatrons\Pages\ListAnnotationPatrons;
use App\Filament\Resources\AnnotationPatrons\Schemas\AnnotationPatronForm;
use App\Filament\Resources\AnnotationPatrons\Tables\AnnotationPatronsTable;
use App\Models\AnnotationPatron;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AnnotationPatronResource extends Resource
{
    protected static ?string $model = AnnotationPatron::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return AnnotationPatronForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnnotationPatronsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAnnotationPatrons::route('/'),
            'create' => CreateAnnotationPatron::route('/create'),
            'edit' => EditAnnotationPatron::route('/{record}/edit'),
        ];
    }
}

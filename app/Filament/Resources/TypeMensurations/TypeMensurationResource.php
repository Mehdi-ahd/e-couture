<?php

namespace App\Filament\Resources\TypeMensurations;

use App\Filament\Resources\TypeMensurations\Pages\CreateTypeMensuration;
use App\Filament\Resources\TypeMensurations\Pages\EditTypeMensuration;
use App\Filament\Resources\TypeMensurations\Pages\ListTypeMensurations;
use App\Filament\Resources\TypeMensurations\Schemas\TypeMensurationForm;
use App\Filament\Resources\TypeMensurations\Tables\TypeMensurationsTable;
use App\Models\TypeMesure;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TypeMensurationResource extends Resource
{
    protected static ?string $model = TypeMesure::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nom';

    public static function form(Schema $schema): Schema
    {
        return TypeMensurationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TypeMensurationsTable::configure($table);
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
            'index' => ListTypeMensurations::route('/'),
            'create' => CreateTypeMensuration::route('/create'),
            'edit' => EditTypeMensuration::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\TypeVetements;

use App\Filament\Resources\TypeVetements\Pages\CreateTypeVetement;
use App\Filament\Resources\TypeVetements\Pages\EditTypeVetement;
use App\Filament\Resources\TypeVetements\Pages\ListTypeVetements;
use App\Filament\Resources\TypeVetements\Schemas\TypeVetementForm;
use App\Filament\Resources\TypeVetements\Tables\TypeVetementsTable;
use App\Models\TypeVetement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TypeVetementResource extends Resource
{
    protected static ?string $model = TypeVetement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nom';

    public static function form(Schema $schema): Schema
    {
        return TypeVetementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TypeVetementsTable::configure($table);
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
            'index' => ListTypeVetements::route('/'),
            'create' => CreateTypeVetement::route('/create'),
            'edit' => EditTypeVetement::route('/{record}/edit'),
        ];
    }
}

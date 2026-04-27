<?php

namespace App\Filament\Resources\ModeleVetements;

use App\Filament\Resources\ModeleVetements\Pages\CreateModeleVetement;
use App\Filament\Resources\ModeleVetements\Pages\EditModeleVetement;
use App\Filament\Resources\ModeleVetements\Pages\ListModeleVetements;
use App\Filament\Resources\ModeleVetements\Schemas\ModeleVetementForm;
use App\Filament\Resources\ModeleVetements\Tables\ModeleVetementsTable;
use App\Models\ModeleVetement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ModeleVetementResource extends Resource
{
    protected static ?string $model = ModeleVetement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nom';

    public static function form(Schema $schema): Schema
    {
        return ModeleVetementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModeleVetementsTable::configure($table);
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
            'index' => ListModeleVetements::route('/'),
            'create' => CreateModeleVetement::route('/create'),
            'edit' => EditModeleVetement::route('/{record}/edit'),
        ];
    }
}

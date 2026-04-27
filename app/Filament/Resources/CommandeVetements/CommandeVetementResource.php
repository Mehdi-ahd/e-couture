<?php

namespace App\Filament\Resources\CommandeVetements;

use App\Filament\Resources\CommandeVetements\Pages\CreateCommandeVetement;
use App\Filament\Resources\CommandeVetements\Pages\EditCommandeVetement;
use App\Filament\Resources\CommandeVetements\Pages\ListCommandeVetements;
use App\Filament\Resources\CommandeVetements\Schemas\CommandeVetementForm;
use App\Filament\Resources\CommandeVetements\Tables\CommandeVetementsTable;
use App\Models\CommandeVetement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CommandeVetementResource extends Resource
{
    protected static ?string $model = CommandeVetement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'external_id';

    public static function form(Schema $schema): Schema
    {
        return CommandeVetementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommandeVetementsTable::configure($table);
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
            'index' => ListCommandeVetements::route('/'),
            'create' => CreateCommandeVetement::route('/create'),
            'edit' => EditCommandeVetement::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\MensurationModeles;

use App\Filament\Resources\MensurationModeles\Pages\CreateMensurationModele;
use App\Filament\Resources\MensurationModeles\Pages\EditMensurationModele;
use App\Filament\Resources\MensurationModeles\Pages\ListMensurationModeles;
use App\Filament\Resources\MensurationModeles\Schemas\MensurationModeleForm;
use App\Filament\Resources\MensurationModeles\Tables\MensurationModelesTable;
use App\Models\MensurationModele;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Ressource Filament pour la gestion des mensurations des modeles.
 * Interface d administration pour les mensurations associees aux modeles de vetements.
 */
class MensurationModeleResource extends Resource
{
    protected static ?string $model = MensurationModele::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'external_id';

    public static function form(Schema $schema): Schema
    {
        return MensurationModeleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MensurationModelesTable::configure($table);
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
            'index' => ListMensurationModeles::route('/'),
            'create' => CreateMensurationModele::route('/create'),
            'edit' => EditMensurationModele::route('/{record}/edit'),
        ];
    }
}

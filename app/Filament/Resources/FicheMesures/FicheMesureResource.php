<?php

namespace App\Filament\Resources\FicheMesures;

use App\Filament\Resources\FicheMesures\Pages\CreateFicheMesure;
use App\Filament\Resources\FicheMesures\Pages\EditFicheMesure;
use App\Filament\Resources\FicheMesures\Pages\ListFicheMesures;
use App\Filament\Resources\FicheMesures\Schemas\FicheMesureForm;
use App\Filament\Resources\FicheMesures\Tables\FicheMesuresTable;
use App\Models\FicheMesure;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Ressource Filament pour la gestion des fiches de mesures.
 * Interface d administration pour les fiches de mesures dans le panneau d administration.
 */
class FicheMesureResource extends Resource
{
    protected static ?string $model = FicheMesure::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'external_id';

    public static function form(Schema $schema): Schema
    {
        return FicheMesureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FicheMesuresTable::configure($table);
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
            'index' => ListFicheMesures::route('/'),
            'create' => CreateFicheMesure::route('/create'),
            'edit' => EditFicheMesure::route('/{record}/edit'),
        ];
    }
}

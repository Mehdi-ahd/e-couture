<?php

namespace App\Filament\Resources\PiecePatrons;

use App\Filament\Resources\PiecePatrons\Pages\CreatePiecePatron;
use App\Filament\Resources\PiecePatrons\Pages\EditPiecePatron;
use App\Filament\Resources\PiecePatrons\Pages\ListPiecePatrons;
use App\Filament\Resources\PiecePatrons\Schemas\PiecePatronForm;
use App\Filament\Resources\PiecePatrons\Tables\PiecePatronsTable;
use App\Models\PiecePatron;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Ressource Filament pour la gestion des pieces de patron.
 * Interface d administration pour les pieces individuelles de patrons dans le panneau d administration.
 */
class PiecePatronResource extends Resource
{
    protected static ?string $model = PiecePatron::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nom';

    public static function form(Schema $schema): Schema
    {
        return PiecePatronForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PiecePatronsTable::configure($table);
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
            'index' => ListPiecePatrons::route('/'),
            'create' => CreatePiecePatron::route('/create'),
            'edit' => EditPiecePatron::route('/{record}/edit'),
        ];
    }
}

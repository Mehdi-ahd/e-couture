<?php

namespace App\Filament\Resources\Patrons;

use App\Filament\Resources\Patrons\Pages\CreatePatron;
use App\Filament\Resources\Patrons\Pages\EditPatron;
use App\Filament\Resources\Patrons\Pages\ListPatrons;
use App\Filament\Resources\Patrons\Schemas\PatronForm;
use App\Filament\Resources\Patrons\Tables\PatronsTable;
use App\Models\Patron;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Ressource Filament pour la gestion des patrons de couture.
 * Interface d administration pour les patrons dans le panneau d administration.
 */
class PatronResource extends Resource
{
    protected static ?string $model = Patron::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'external_id';

    public static function form(Schema $schema): Schema
    {
        return PatronForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatronsTable::configure($table);
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
            'index' => ListPatrons::route('/'),
            'create' => CreatePatron::route('/create'),
            'edit' => EditPatron::route('/{record}/edit'),
        ];
    }
}

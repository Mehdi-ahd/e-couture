<?php

namespace App\Filament\Resources\LigneMensurations;

use App\Filament\Resources\LigneMensurations\Pages\CreateLigneMensuration;
use App\Filament\Resources\LigneMensurations\Pages\EditLigneMensuration;
use App\Filament\Resources\LigneMensurations\Pages\ListLigneMensurations;
use App\Filament\Resources\LigneMensurations\Schemas\LigneMensurationForm;
use App\Filament\Resources\LigneMensurations\Tables\LigneMensurationsTable;
use App\Models\LigneMensuration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LigneMensurationResource extends Resource
{
    protected static ?string $model = LigneMensuration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'external_id';

    public static function form(Schema $schema): Schema
    {
        return LigneMensurationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LigneMensurationsTable::configure($table);
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
            'index' => ListLigneMensurations::route('/'),
            'create' => CreateLigneMensuration::route('/create'),
            'edit' => EditLigneMensuration::route('/{record}/edit'),
        ];
    }
}

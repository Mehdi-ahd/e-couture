<?php

namespace App\Filament\Resources\FicheClients;

use App\Filament\Resources\FicheClients\Pages\CreateFicheClient;
use App\Filament\Resources\FicheClients\Pages\EditFicheClient;
use App\Filament\Resources\FicheClients\Pages\ListFicheClients;
use App\Filament\Resources\FicheClients\Schemas\FicheClientForm;
use App\Filament\Resources\FicheClients\Tables\FicheClientsTable;
use App\Models\FicheClient;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FicheClientResource extends Resource
{
    protected static ?string $model = FicheClient::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'external_id';

    public static function form(Schema $schema): Schema
    {
        return FicheClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FicheClientsTable::configure($table);
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
            'index' => ListFicheClients::route('/'),
            'create' => CreateFicheClient::route('/create'),
            'edit' => EditFicheClient::route('/{record}/edit'),
        ];
    }
}

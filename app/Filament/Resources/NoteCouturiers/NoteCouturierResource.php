<?php

namespace App\Filament\Resources\NoteCouturiers;

use App\Filament\Resources\NoteCouturiers\Pages\CreateNoteCouturier;
use App\Filament\Resources\NoteCouturiers\Pages\EditNoteCouturier;
use App\Filament\Resources\NoteCouturiers\Pages\ListNoteCouturiers;
use App\Filament\Resources\NoteCouturiers\Schemas\NoteCouturierForm;
use App\Filament\Resources\NoteCouturiers\Tables\NoteCouturiersTable;
use App\Models\NoteCouturier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NoteCouturierResource extends Resource
{
    protected static ?string $model = NoteCouturier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'external_id';

    public static function form(Schema $schema): Schema
    {
        return NoteCouturierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NoteCouturiersTable::configure($table);
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
            'index' => ListNoteCouturiers::route('/'),
            'create' => CreateNoteCouturier::route('/create'),
            'edit' => EditNoteCouturier::route('/{record}/edit'),
        ];
    }
}

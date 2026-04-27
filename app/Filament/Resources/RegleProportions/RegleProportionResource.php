<?php

namespace App\Filament\Resources\RegleProportions;

use App\Filament\Resources\RegleProportions\Pages\CreateRegleProportion;
use App\Filament\Resources\RegleProportions\Pages\EditRegleProportion;
use App\Filament\Resources\RegleProportions\Pages\ListRegleProportions;
use App\Filament\Resources\RegleProportions\Schemas\RegleProportionForm;
use App\Filament\Resources\RegleProportions\Tables\RegleProportionsTable;
use App\Models\RegleProportion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RegleProportionResource extends Resource
{
    protected static ?string $model = RegleProportion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nom';

    public static function form(Schema $schema): Schema
    {
        return RegleProportionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegleProportionsTable::configure($table);
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
            'index' => ListRegleProportions::route('/'),
            'create' => CreateRegleProportion::route('/create'),
            'edit' => EditRegleProportion::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\RegleProportions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RegleProportionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nom')
                    ->required(),
                TextInput::make('mensuration_source_id')
                    ->required()
                    ->numeric(),
                TextInput::make('mensuration_cible_id')
                    ->required()
                    ->numeric(),
                TextInput::make('coefficient')
                    ->required()
                    ->numeric(),
                TextInput::make('offset')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('source_metier'),
                TextInput::make('version')
                    ->required()
                    ->numeric()
                    ->default(1),
                Toggle::make('est_active')
                    ->required(),
            ]);
    }
}

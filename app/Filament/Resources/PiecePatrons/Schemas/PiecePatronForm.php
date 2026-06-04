<?php

namespace App\Filament\Resources\PiecePatrons\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PiecePatronForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('patron_id')
                    ->required()
                    ->numeric(),
                TextInput::make('nom')
                    ->required(),
                TextInput::make('ordre')
                    ->required()
                    ->numeric()
                    ->default(1),
                Textarea::make('donnees_geometriques')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}

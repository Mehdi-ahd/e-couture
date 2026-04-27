<?php

namespace App\Filament\Resources\TypeMensurations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TypeMensurationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('nom')
                    ->required(),
                TextInput::make('unite')
                    ->required()
                    ->default('cm'),
                TextInput::make('categorie')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Toggle::make('est_actif')
                    ->required(),
            ]);
    }
}

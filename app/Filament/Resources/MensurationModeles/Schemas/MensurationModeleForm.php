<?php

namespace App\Filament\Resources\MensurationModeles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MensurationModeleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('modele_vetement_id')
                    ->required()
                    ->numeric(),
                TextInput::make('type_mensuration_id')
                    ->required()
                    ->numeric(),
                TextInput::make('valeur')
                    ->required()
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}

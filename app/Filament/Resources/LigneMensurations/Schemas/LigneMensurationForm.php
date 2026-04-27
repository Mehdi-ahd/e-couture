<?php

namespace App\Filament\Resources\LigneMensurations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LigneMensurationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('fiche_mesure_id')
                    ->required()
                    ->numeric(),
                TextInput::make('type_mensuration_id')
                    ->required()
                    ->numeric(),
                TextInput::make('valeur')
                    ->required()
                    ->numeric(),
                TextInput::make('source')
                    ->required(),
                TextInput::make('confiance')
                    ->numeric(),
                Textarea::make('commentaire')
                    ->columnSpanFull(),
            ]);
    }
}

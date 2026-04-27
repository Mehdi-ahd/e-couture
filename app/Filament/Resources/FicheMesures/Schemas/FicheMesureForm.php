<?php

namespace App\Filament\Resources\FicheMesures\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FicheMesureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('client_id')
                    ->required()
                    ->numeric(),
                DatePicker::make('date_prise')
                    ->required(),
                TextInput::make('methode')
                    ->required(),
                TextInput::make('statut_traitement')
                    ->required()
                    ->default('EN_ATTENTE'),
                TextInput::make('traitement_id'),
                TextInput::make('version_regles')
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('statut')
                    ->required()
                    ->default('BROUILLON'),
            ]);
    }
}

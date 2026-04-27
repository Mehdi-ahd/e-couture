<?php

namespace App\Filament\Resources\Patrons\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PatronForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('modele_vetement_id')
                    ->required()
                    ->numeric(),
                TextInput::make('methode')
                    ->required(),
                TextInput::make('version')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('fichier_url')
                    ->url(),
                Textarea::make('donnees_dessin')
                    ->columnSpanFull(),
                TextInput::make('statut')
                    ->required()
                    ->default('BROUILLON'),
            ]);
    }
}

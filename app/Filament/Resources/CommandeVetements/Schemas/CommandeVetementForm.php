<?php

namespace App\Filament\Resources\CommandeVetements\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CommandeVetementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('fiche_client_id')
                    ->required()
                    ->numeric(),
                TextInput::make('modele_vetement_id')
                    ->required()
                    ->numeric(),
                TextInput::make('fiche_mesure_id')
                    ->required()
                    ->numeric(),
                TextInput::make('statut')
                    ->required()
                    ->default('EN_ATTENTE'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                DateTimePicker::make('date_commande')
                    ->required(),
                DatePicker::make('date_livraison'),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Paiements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaiementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('commande_id')
                    ->required()
                    ->numeric(),
                TextInput::make('mode')
                    ->required(),
                TextInput::make('montant')
                    ->required()
                    ->numeric(),
                TextInput::make('devise')
                    ->required()
                    ->default('XOF'),
                TextInput::make('statut')
                    ->required()
                    ->default('INITIE'),
                DateTimePicker::make('date_initiation')
                    ->required(),
                DateTimePicker::make('date_confirmation'),
                TextInput::make('reference_externe'),
                Textarea::make('metadonnees_agregateur')
                    ->columnSpanFull(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\FicheClients\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FicheClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('couturier_id')
                    ->required()
                    ->numeric(),
                TextInput::make('client_id')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('date_creation')
                    ->required(),
                Toggle::make('est_actif')
                    ->required(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\NoteCouturiers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NoteCouturierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('client_id')
                    ->required()
                    ->numeric(),
                TextInput::make('couturier_id')
                    ->required()
                    ->numeric(),
                TextInput::make('commande_id')
                    ->numeric(),
                TextInput::make('note_service')
                    ->required()
                    ->numeric(),
                TextInput::make('note_conception')
                    ->required()
                    ->numeric(),
                TextInput::make('note_livraison')
                    ->required()
                    ->numeric(),
                TextInput::make('note_delai')
                    ->required()
                    ->numeric(),
                Textarea::make('commentaire')
                    ->columnSpanFull(),
                DateTimePicker::make('date_notation')
                    ->required(),
                Toggle::make('est_visible')
                    ->required(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\ModeleVetements\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ModeleVetementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('type_vetement_id')
                    ->required()
                    ->numeric(),
                TextInput::make('createur_id')
                    ->numeric(),
                TextInput::make('nom')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('portee')
                    ->required(),
                TextInput::make('statut')
                    ->required()
                    ->default('BROUILLON'),
            ]);
    }
}

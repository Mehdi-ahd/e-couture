<?php

namespace App\Filament\Resources\TypeVetements\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TypeVetementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('nom')
                    ->required(),
                TextInput::make('categorie')
                    ->required(),
                TextInput::make('mensuration_pivot_id')
                    ->required()
                    ->numeric(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Toggle::make('est_actif')
                    ->required(),
            ]);
    }
}

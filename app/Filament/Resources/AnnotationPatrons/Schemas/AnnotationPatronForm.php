<?php

namespace App\Filament\Resources\AnnotationPatrons\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AnnotationPatronForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('piece_patron_id')
                    ->required()
                    ->numeric(),
                TextInput::make('type_mensuration_id')
                    ->required()
                    ->numeric(),
                TextInput::make('label')
                    ->required(),
                TextInput::make('position_depart')
                    ->required(),
                TextInput::make('position_fin')
                    ->required(),
                TextInput::make('orientation')
                    ->required(),
            ]);
    }
}

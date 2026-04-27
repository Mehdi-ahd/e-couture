<?php

namespace App\Filament\Resources\NoteCouturiers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NoteCouturiersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('external_id')
                    ->searchable(),
                TextColumn::make('client_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('couturier_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('commande_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('note_service')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('note_conception')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('note_livraison')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('note_delai')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('date_notation')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('est_visible')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

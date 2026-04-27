<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options([
                        'COUTURIER' => 'Couturier',
                        'ADMINISTRATEUR' => 'Administrateur',
                        'CLIENT' => 'Client',
                    ])
                    ->required(),
                TextInput::make('nom')
                    ->required()
                    ->maxLength(80),
                TextInput::make('prenom')
                    ->required()
                    ->maxLength(80),
                TextInput::make('telephone')
                    ->tel()
                    ->maxLength(20)
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->maxLength(190)
                    ->required(),
                TextInput::make('specialite')
                    ->maxLength(150),
                Textarea::make('adresse_atelier'),
                Textarea::make('bio'),
                DatePicker::make('date_naissance'),
                Select::make('kyc_statut')
                    ->options([
                        'NON_SOUMIS' => 'Non soumis',
                        'EN_ATTENTE' => 'En attente',
                        'VALIDE' => 'Validé',
                        'REJETE' => 'Rejeté',
                    ])
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
            ]);
    }
}

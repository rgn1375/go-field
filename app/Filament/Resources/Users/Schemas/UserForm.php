<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(20)
                    ->placeholder('08123456789'),
                Textarea::make('address')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),
                DateTimePicker::make('email_verified_at')
                    ->label('Email Verified At')
                    ->nullable()
                    ->columnSpanFull(),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255)
                    ->helperText('Set initial password for new user')
                    ->columnSpanFull()
                    ->visible(fn (string $context): bool => $context === 'create'),
            ]);
    }
}

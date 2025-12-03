<?php

namespace App\Filament\Resources\SportTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SportTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Format: lowercase, no spaces (e.g., futsal, basketball)')
                    ->maxLength(50),
                
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(100),
                
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
                
                TextInput::make('icon')
                    ->label('Icon Class')
                    ->helperText('Contoh: heroicon-o-circle-stack')
                    ->maxLength(100),
                
                TextInput::make('sort_order')
                    ->label('Urutan Tampilan')
                    ->numeric()
                    ->default(0)
                    ->helperText('Angka lebih kecil akan tampil lebih dulu'),
                
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->helperText('Nonaktifkan untuk menyembunyikan dari pilihan'),
            ]);
    }
}

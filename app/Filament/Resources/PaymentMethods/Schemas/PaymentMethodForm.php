<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Format: lowercase, underscore (e.g., bank_transfer, e_wallet)')
                    ->maxLength(50),
                
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(100),
                
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
                
                TextInput::make('logo')
                    ->label('Logo Path')
                    ->helperText('Path ke file logo (opsional)'),
                
                TextInput::make('admin_fee')
                    ->label('Biaya Admin (Fixed)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->helperText('Biaya tetap dalam rupiah'),
                
                TextInput::make('admin_fee_percentage')
                    ->label('Biaya Admin (%)')
                    ->numeric()
                    ->suffix('%')
                    ->step(0.1)
                    ->default(0)
                    ->helperText('Persentase dari total transaksi'),
                
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

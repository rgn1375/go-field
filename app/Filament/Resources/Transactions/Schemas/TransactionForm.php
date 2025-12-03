<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Transaksi')
                    ->schema([
                        TextInput::make('transaction_code')
                            ->label('Kode Transaksi')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated'),
                        
                        Select::make('booking_id')
                            ->label('Booking')
                            ->relationship('booking', 'booking_code')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($record) => $record !== null),
                        
                        Select::make('payment_method_id')
                            ->label('Metode Pembayaran')
                            ->relationship('paymentMethod', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($record) => $record !== null),
                    ])
                    ->columns(2),
                
                Section::make('Detail Pembayaran')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->disabled(fn ($record) => $record !== null),
                        
                        TextInput::make('admin_fee')
                            ->label('Biaya Admin')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->disabled(fn ($record) => $record !== null),
                        
                        TextInput::make('total_amount')
                            ->label('Total Pembayaran')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->disabled(fn ($record) => $record !== null),
                        
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu Pembayaran',
                                'waiting_confirmation' => 'Menunggu Konfirmasi',
                                'paid' => 'Sudah Dibayar',
                                'failed' => 'Gagal',
                                'refunded' => 'Refund',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(2),
                
                Section::make('Bukti Pembayaran')
                    ->schema([
                        FileUpload::make('payment_proof')
                            ->label('Upload Bukti')
                            ->image()
                            ->disk('public')
                            ->directory('payment-proofs')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                        
                        Textarea::make('notes')
                            ->label('Catatan Customer')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(fn ($record) => $record !== null),
                    ]),
                
                Section::make('Konfirmasi & Refund')
                    ->schema([
                        DateTimePicker::make('paid_at')
                            ->label('Tanggal Dibayar')
                            ->disabled(),
                        
                        DateTimePicker::make('confirmed_at')
                            ->label('Tanggal Dikonfirmasi')
                            ->disabled(),
                        
                        Select::make('confirmed_by')
                            ->label('Dikonfirmasi Oleh')
                            ->relationship('confirmedBy', 'name')
                            ->disabled(),
                        
                        Textarea::make('admin_notes')
                            ->label('Catatan Admin')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Catatan internal untuk admin'),
                        
                        DateTimePicker::make('refunded_at')
                            ->label('Tanggal Refund')
                            ->disabled()
                            ->visible(fn ($record) => $record && $record->status === 'refunded'),
                        
                        TextInput::make('refund_amount')
                            ->label('Jumlah Refund')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->visible(fn ($record) => $record && $record->status === 'refunded'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}

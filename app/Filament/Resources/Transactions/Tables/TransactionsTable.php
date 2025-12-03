<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_code')
                    ->label('Kode Transaksi')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),
                
                TextColumn::make('booking.booking_code')
                    ->label('Kode Booking')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->booking ? route('filament.admin.resources.bookings.edit', $record->booking) : null)
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('booking.nama_pemesan')
                    ->label('Nama Pemesan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                
                TextColumn::make('paymentMethod.name')
                    ->label('Metode Pembayaran')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                
                TextColumn::make('admin_fee')
                    ->label('Biaya Admin')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'waiting_confirmation' => 'info',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'waiting_confirmation' => 'Konfirmasi',
                        'paid' => 'Lunas',
                        'failed' => 'Gagal',
                        'refunded' => 'Refund',
                        default => $state,
                    }),
                
                ImageColumn::make('payment_proof')
                    ->label('Bukti')
                    ->disk('public')
                    ->size(60)
                    ->toggleable(),
                
                TextColumn::make('paid_at')
                    ->label('Tgl Bayar')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('confirmed_at')
                    ->label('Tgl Konfirmasi')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('confirmedBy.name')
                    ->label('Dikonfirmasi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('refunded_at')
                    ->label('Tgl Refund')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('refund_amount')
                    ->label('Jumlah Refund')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Pembayaran',
                        'waiting_confirmation' => 'Menunggu Konfirmasi',
                        'paid' => 'Sudah Dibayar',
                        'failed' => 'Gagal',
                        'refunded' => 'Refund',
                    ])
                    ->multiple(),
                
                SelectFilter::make('payment_method_id')
                    ->label('Metode Pembayaran')
                    ->relationship('paymentMethod', 'name')
                    ->preload()
                    ->multiple(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

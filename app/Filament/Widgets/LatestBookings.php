<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestBookings extends TableWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): ?string
    {
        return 'Booking Terbaru';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(BookingResource::getEloquentQuery()->latest())
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('booking_code')
                    ->label('Kode Booking')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_pemesan')
                    ->label('Pemesan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lapangan.name')
                    ->label('Lapangan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jam_mulai')
                    ->label('Waktu')
                    ->formatStateUsing(fn ($record) => $record->jam_mulai . ' - ' . $record->jam_selesai),
                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->colors([
                        'warning' => 'unpaid',
                        'info' => 'waiting_confirmation',
                        'success' => 'paid',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'unpaid' => 'Belum Bayar',
                        'waiting_confirmation' => 'Menunggu Konfirmasi',
                        'paid' => 'Sudah Bayar',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status Booking')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'confirmed',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pending',
                        'confirmed' => 'Dikonfirmasi',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),
            ]);
    }
}

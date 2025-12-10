<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Bookings\BookingResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\ViewAction;

class BookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    protected static ?string $relatedResource = BookingResource::class;
    
    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->prefix('#'),
                
                TextColumn::make('lapangan.title')
                    ->label('Lapangan')
                    ->searchable()
                    ->icon('heroicon-o-map-pin')
                    ->iconColor('success'),
                
                TextColumn::make('lapangan.category')
                    ->label('Category')
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('tanggal')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                
                TextColumn::make('jam_mulai')
                    ->label('Time')
                    ->formatStateUsing(fn ($record) => 
                        substr($record->jam_mulai, 0, 5) . ' - ' . substr($record->jam_selesai, 0, 5)
                    )
                    ->icon('heroicon-o-clock'),
                
                TextColumn::make('lapangan.price')
                    ->label('Price')
                    ->money('IDR')
                    ->icon('heroicon-o-currency-dollar'),
                
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'primary' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'confirmed',
                        'heroicon-o-check-badge' => 'completed',
                        'heroicon-o-x-circle' => 'cancelled',
                    ]),
                
                TextColumn::make('created_at')
                    ->label('Booked At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => BookingResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}

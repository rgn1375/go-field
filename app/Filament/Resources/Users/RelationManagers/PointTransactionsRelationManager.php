<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class PointTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'pointTransactions';
    
    protected static ?string $title = 'Point History';
    
    protected static ?string $recordTitleAttribute = 'description';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'success' => 'earned',
                        'danger' => 'redeemed',
                        'warning' => 'adjusted',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                
                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->wrap()
                    ->icon('heroicon-o-document-text'),
                
                TextColumn::make('booking.id')
                    ->label('Booking')
                    ->formatStateUsing(fn ($state) => $state ? '#' . $state : '-')
                    ->url(fn ($record) => $record->booking_id ? 
                        route('filament.admin.resources.bookings.edit', ['record' => $record->booking_id]) : 
                        null
                    )
                    ->color('primary')
                    ->icon('heroicon-o-link'),
                
                BadgeColumn::make('points')
                    ->label('Points')
                    ->colors([
                        'success' => fn ($state) => $state > 0,
                        'danger' => fn ($state) => $state < 0,
                    ])
                    ->icons([
                        'heroicon-o-arrow-up' => fn ($state) => $state > 0,
                        'heroicon-o-arrow-down' => fn ($state) => $state < 0,
                    ])
                    ->formatStateUsing(fn ($state) => 
                        ($state > 0 ? '+' : '') . number_format($state)
                    ),
                
                TextColumn::make('balance_after')
                    ->label('Balance After')
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-star')
                    ->formatStateUsing(fn ($state) => number_format($state)),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'earned' => 'Earned',
                        'redeemed' => 'Redeemed',
                        'adjusted' => 'Adjusted',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}

<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Enums\PaginationMode;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\Textarea;
use App\Services\PointService;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->iconColor('primary'),
                
                TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->icon('heroicon-o-envelope'),
                
                TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->placeholder('Not set'),
                
                TextColumn::make('points_balance')
                    ->label('Points')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-star')
                    ->formatStateUsing(fn ($state) => number_format($state) . ' pts'),
                
                TextColumn::make('bookings_count')
                    ->label('Total Bookings')
                    ->counts('bookings')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-calendar'),
                
                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
                
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->nullable()
                    ->trueLabel('Verified')
                    ->falseLabel('Not Verified')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('email_verified_at'),
                        false: fn ($query) => $query->whereNull('email_verified_at'),
                    ),
                
                SelectFilter::make('has_bookings')
                    ->label('Booking Status')
                    ->options([
                        'has' => 'Has Bookings',
                        'none' => 'No Bookings',
                    ])
                    ->query(function ($query, $state) {
                        if ($state['value'] === 'has') {
                            return $query->has('bookings');
                        } elseif ($state['value'] === 'none') {
                            return $query->doesntHave('bookings');
                        }
                    }),
            ])
            ->recordActions([
                Action::make('adjust_points')
                    ->label('Adjust Points')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('warning')
                    ->form([
                        TextInput::make('points')
                            ->label('Points Amount')
                            ->numeric()
                            ->required()
                            ->helperText('Use positive numbers to add, negative to deduct'),
                        Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->placeholder('e.g., Manual adjustment for...'),
                    ])
                    ->action(function ($record, array $data) {
                        $pointService = app(PointService::class);
                        $pointService->adjustPoints($record, (int)$data['points'], $data['reason']);
                    })
                    ->successNotificationTitle('Points adjusted successfully'),
                
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->paginationMode(PaginationMode::Cursor);
    }
}

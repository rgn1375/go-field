<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Filters\SelectFilter;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Support\Icons\Heroicon;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Invoice number copied!')
                    ->badge()
                    ->color('primary')
                    ->icon(Heroicon::OutlinedDocumentText),
                    
                TextColumn::make('booking.booking_code')
                    ->label('Booking Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                    
                TextColumn::make('booking.nama_pemesan')
                    ->label('Customer Name')
                    ->searchable(),
                    
                TextColumn::make('booking.lapangan.title')
                    ->label('Lapangan')
                    ->searchable()
                    ->limit(25),
                    
                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                    ]),
                    
                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? ucfirst(str_replace('_', ' ', $state)) : 'N/A'),
                    
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                    
                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'paid' => 'Paid',
                        'pending' => 'Pending',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Download PDF')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('success')
                    ->action(function ($record) {
                        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $record->load('booking.lapangan')]);
                        $pdf->setPaper('a4', 'portrait');
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, $record->invoice_number . '.pdf');
                    }),
                    
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

<?php

namespace App\Filament\Resources\Bookings;

use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Models\Booking;
use App\Models\Lapangan;
use App\Models\PaymentMethod;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use App\Notifications\BookingCancelled;
use Illuminate\Support\Facades\Log;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static ?string $navigationLabel = 'Pemesanan';

    protected static ?string $modelLabel = 'Pemesanan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('booking_code')
                    ->label('Booking ID')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($record) => $record !== null)
                    ->placeholder('Auto-generated'),
                
                Select::make('lapangan_id')
                    ->label('Nama Lapangan')
                    ->options(Lapangan::where('status', true)->pluck('title', 'id'))
                    ->required(),

                DatePicker::make('tanggal')
                    ->required()
                    ->native(false)
                    ->minDate(now()),

                TimePicker::make('jam_mulai')
                    ->required()
                    ->seconds(false),

                TimePicker::make('jam_selesai')
                    ->required()
                    ->seconds(false),

                TextInput::make('nama_pemesan')
                    ->required(),
                
                TextInput::make('nomor_telepon')
                    ->label('No. Telepon')
                    ->tel()
                    ->required(),
                
                TextInput::make('email')
                    ->email(),
                
                TextInput::make('harga')
                    ->label('Harga')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                
                Select::make('payment_method_id')
                    ->label('Metode Pembayaran')
                    ->relationship('paymentMethod', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nama')
                            ->required(),
                        Textarea::make('description')
                            ->label('Deskripsi'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
                
                Select::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'unpaid' => 'Belum Bayar',
                        'waiting_confirmation' => 'Menunggu Konfirmasi',
                        'paid' => 'Sudah Bayar',
                        'refunded' => 'Refund',
                    ])
                    ->default('unpaid'),
                
                Select::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'confirmed' => 'Dikonfirmasi',
                        'cancelled' => 'Dibatalkan',
                        'completed' => 'Selesai',
                    ])
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_code')
                    ->label('Booking ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Booking ID disalin!')
                    ->copyMessageDuration(1500)
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-ticket')
                    ->weight('bold'),
                
                TextColumn::make('lapangan.title')
                    ->label('Lapangan')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-office-2')
                    ->iconColor('success'),
                
                TextColumn::make('lapangan.sportType.name')
                    ->label('Jenis Olahraga')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('jam_mulai')
                    ->label('Jam Mulai')
                    ->time('H:i')
                    ->icon('heroicon-o-clock'),

                TextColumn::make('jam_selesai')
                    ->label('Jam Selesai')
                    ->time('H:i')
                    ->icon('heroicon-o-clock'),

                TextColumn::make('nama_pemesan')
                    ->label('Nama Pemesan')
                    ->searchable()
                    ->icon('heroicon-o-user')
                    ->iconColor('primary'),
                
                TextColumn::make('nomor_telepon')
                    ->label('No. Telepon')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->copyMessage('Nomor disalin!')
                    ->copyMessageDuration(1500),
                
                TextColumn::make('harga')
                    ->label('Harga')
                    ->money('IDR')
                    ->icon('heroicon-o-currency-dollar')
                    ->sortable(),
                
                TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'waiting_confirmation' => 'warning',
                        'paid' => 'success',
                        'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'unpaid' => 'Belum Bayar',
                        'waiting_confirmation' => 'Menunggu Konfirmasi',
                        'paid' => 'Sudah Bayar',
                        'refunded' => 'Refund',
                        default => '-',
                    })
                    ->icon(fn (?string $state): string => match ($state) {
                        'unpaid' => 'heroicon-o-x-circle',
                        'waiting_confirmation' => 'heroicon-o-clock',
                        'paid' => 'heroicon-o-check-circle',
                        'refunded' => 'heroicon-o-arrow-path',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                
                TextColumn::make('paymentMethod.name')
                    ->label('Metode Pembayaran')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-credit-card')
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                        'pending_cancellation' => 'warning',
                        default => 'gray',
                    }),
                
                TextColumn::make('refund_amount')
                    ->label('Refund')
                    ->money('IDR')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->badge()
                    ->default('-')
                    ->formatStateUsing(function (?Booking $record): string {
                        if (!$record || $record->refund_amount <= 0) {
                            return '-';
                        }
                        $method = match($record->refund_method) {
                            'points' => '(Poin)',
                            'bank_transfer' => '(Transfer)',
                            default => '',
                        };
                        return 'Rp ' . number_format($record->refund_amount) . ' ' . $method;
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'confirmed' => 'Dikonfirmasi',
                        'cancelled' => 'Dibatalkan',
                        'completed' => 'Selesai',
                    ]),
                
                SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'unpaid' => 'Belum Bayar',
                        'waiting_confirmation' => 'Menunggu Konfirmasi',
                        'paid' => 'Sudah Bayar',
                        'refunded' => 'Refund',
                    ]),
                
                SelectFilter::make('lapangan_id')
                    ->label('Lapangan')
                    ->relationship('lapangan', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                // View Payment Proof
                Action::make('view_payment')
                    ->label('Lihat Bukti')
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->modalHeading('Bukti Pembayaran')
                    ->modalWidth('2xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function (?Booking $record) {
                        if (!$record) {
                            return null;
                        }
                        // Eager load relationship untuk modal
                        $record->load('paymentMethod');
                        return view('filament.modals.payment-proof', [
                            'record' => $record,
                        ]);
                    })
                    ->visible(function (?Booking $record): bool {
                        if (!$record || $record->payment_proof === null) {
                            return false;
                        }
                        
                        if ($record->payment_method_id === null) {
                            return false;
                        }
                        
                        // Load relationship jika belum
                        if (!$record->relationLoaded('paymentMethod')) {
                            $record->load('paymentMethod');
                        }
                        
                        // Hide untuk cash payment
                        if ($record->paymentMethod && $record->paymentMethod->code === 'cash') {
                            return false;
                        }
                        
                        return true;
                    }),
                
                // Approve Payment
                Action::make('approve_payment')
                    ->label('Terima')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Terima Pembayaran')
                    ->modalDescription('Apakah Anda yakin pembayaran ini valid?')
                    ->action(function (Booking $record): void {
                        $record->update([
                            'payment_status' => 'paid',
                            'paid_at' => now(),
                            'payment_confirmed_at' => now(),
                            'payment_confirmed_by' => auth()->id(),
                            'status' => 'confirmed',
                        ]);
                        
                        // Update or create transaction for payment history
                        $existingTransaction = $record->transactions()
                            ->whereIn('status', ['waiting_confirmation', 'pending'])
                            ->first();
                        
                        if ($existingTransaction) {
                            // Update existing transaction
                            $existingTransaction->update([
                                'status' => 'paid',
                                'confirmed_at' => now(),
                                'confirmed_by' => auth()->id(),
                            ]);
                        } else {
                            // Create new transaction if not exists (admin approval without user upload)
                            \App\Models\Transaction::create([
                                'booking_id' => $record->id,
                                'payment_method_id' => $record->payment_method_id ?? 1, // Default to first payment method
                                'amount' => $record->harga,
                                'total_amount' => $record->harga,
                                'status' => 'paid',
                                'paid_at' => now(),
                                'confirmed_at' => now(),
                                'confirmed_by' => auth()->id(),
                                'notes' => 'Admin approved payment directly',
                            ]);
                        }
                    })
                    ->successNotificationTitle('Pembayaran berhasil dikonfirmasi')
                    ->visible(fn (?Booking $record): bool =>
                        $record && in_array($record->payment_status, ['waiting_confirmation', 'unpaid'])
                    ),
                
                // Reject Payment
                Action::make('reject_payment')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pembayaran')
                    ->modalDescription('Pembayaran akan ditolak dan user perlu upload ulang bukti yang valid.')
                    ->form([
                        Textarea::make('reject_reason')
                            ->label('Alasan Penolakan')
                            ->placeholder('Contoh: Bukti pembayaran tidak jelas, jumlah tidak sesuai, dll.')
                            ->required()
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->action(function (Booking $record, array $data): void {
                        $record->update([
                            'payment_status' => 'unpaid',
                            'payment_notes' => 'DITOLAK: ' . $data['reject_reason'],
                        ]);
                        
                        // Update transaction status to failed
                        $record->transactions()
                            ->where('status', 'waiting_confirmation')
                            ->update([
                                'status' => 'failed',
                                'admin_notes' => 'DITOLAK: ' . $data['reject_reason'],
                            ]);
                    })
                    ->successNotificationTitle('Pembayaran ditolak')
                    ->visible(fn (?Booking $record): bool =>
                        $record && $record->payment_status === 'waiting_confirmation'
                    ),
                
                EditAction::make()
                    ->label('Edit'),
                
                Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Pemesanan')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan pemesanan ini? Notifikasi akan dikirim ke customer.')
                    ->form([
                        Textarea::make('reason')
                            ->label('Alasan Pembatalan')
                            ->placeholder('Masukkan alasan pembatalan (opsional)')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->action(function (Booking $record, array $data): void {
                        $record->update(['status' => 'cancelled']);
                        
                        try {
                            $record->notify(new BookingCancelled($record, $data['reason'] ?? null));
                        } catch (\Exception $e) {
                            Log::error('Failed to send cancellation notification', [
                                'booking_id' => $record->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    })
                    ->successNotificationTitle('Pemesanan berhasil dibatalkan dan notifikasi telah dikirim')
                    ->visible(fn (?Booking $record): bool => $record && in_array($record->status, ['pending', 'confirmed'])),
                
                Action::make('confirm')
                    ->label('Konfirmasi Booking')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Booking $record) => $record->update(['status' => 'confirmed']))
                    ->successNotificationTitle('Pemesanan berhasil dikonfirmasi')
                    ->visible(fn (?Booking $record): bool => $record && $record->status === 'pending'),
                
                // Approve Cancellation Request
                Action::make('approve_cancellation')
                    ->label('Approve Pembatalan')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Permintaan Pembatalan')
                    ->modalDescription(fn (Booking $record) =>
                        'Approve pembatalan dari ' . $record->nama_pemesan . ' dengan refund ' . 
                        $record->refund_percentage . '% (Rp ' . number_format($record->refund_amount) . ').'
                    )
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Catatan Admin')
                            ->placeholder('Catatan internal (optional)')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (Booking $record, array $data): void {
                        $record->update([
                            'status' => 'cancelled',
                            'refund_notes' => ($record->refund_notes ?? '') . "\n\nAdmin Notes: " . ($data['admin_notes'] ?? 'Approved'),
                        ]);
                        
                        // Send notification to user if exists
                        if ($record->user) {
                            $record->user->notify(new \App\Notifications\BookingCancelled($record));
                        }
                    })
                    ->successNotificationTitle('Pembatalan berhasil di-approve')
                    ->visible(fn (?Booking $record): bool => 
                        $record && $record->status === 'pending_cancellation'
                    ),
                
                // Reject Cancellation Request
                Action::make('reject_cancellation')
                    ->label('Tolak Pembatalan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Permintaan Pembatalan')
                    ->modalDescription('Tolak permintaan pembatalan dan kembalikan booking ke status sebelumnya.')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->placeholder('Jelaskan alasan penolakan kepada customer')
                            ->required()
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->action(function (Booking $record, array $data): void {
                        $previousStatus = $record->payment_status === 'paid' ? 'confirmed' : 'pending';
                        
                        $record->update([
                            'status' => $previousStatus,
                            'cancellation_reason' => null,
                            'cancelled_at' => null,
                            'cancelled_by' => null,
                            'refund_amount' => 0,
                            'refund_percentage' => 0,
                            'refund_method' => 'none',
                            'refund_notes' => 'Pembatalan ditolak: ' . $data['rejection_reason'],
                        ]);
                    })
                    ->successNotificationTitle('Pembatalan berhasil ditolak')
                    ->visible(fn (?Booking $record): bool => 
                        $record && $record->status === 'pending_cancellation'
                    ),
                
                // Process Manual Refund (Bank Transfer)
                Action::make('process_refund')
                    ->label('Proses Refund')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Refund Manual')
                    ->modalDescription(fn (Booking $record) =>
                        'Customer meminta refund via transfer bank sebesar Rp ' . number_format($record->refund_amount) .
                        '. Pastikan transfer sudah dilakukan sebelum konfirmasi.'
                    )
                    ->form([
                        Textarea::make('refund_notes')
                            ->label('Catatan Refund')
                            ->placeholder('Contoh: Sudah transfer ke rekening BCA 1234567890 a.n. John Doe pada 03 Des 2025')
                            ->required()
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->action(function (Booking $record, array $data): void {
                        $record->update([
                            'refund_method' => 'bank_transfer',
                            'refund_notes' => $data['refund_notes'],
                            'refund_processed_at' => now(),
                            'payment_status' => 'refunded',
                        ]);
                    })
                    ->successNotificationTitle('Refund berhasil diproses')
                    ->visible(function (?Booking $record): bool {
                        if (!$record) {
                            return false;
                        }
                        return $record->status === 'cancelled' &&
                               $record->refund_amount > 0 &&
                               $record->refund_method === 'manual' &&
                               $record->refund_processed_at === null;
                    }),
                
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->defaultSort('tanggal', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'create' => CreateBooking::route('/create'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }
}

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
                    }),
                
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
                    ->modalContent(function (Booking $record) {
                        // Eager load relationship untuk modal
                        $record->load('paymentMethod');
                        return view('filament.modals.payment-proof', [
                            'record' => $record,
                        ]);
                    })
                    ->visible(function (Booking $record): bool {
                        if ($record->payment_proof === null) {
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
                            'payment_confirmed_at' => now(),
                            'payment_confirmed_by' => auth()->id(),
                            'status' => 'confirmed', // Status jadi confirmed setelah payment approved
                        ]);
                        
                        if ($record->user_id && $record->points_earned > 0) {
                            $user = \App\Models\User::find($record->user_id);
                            if ($user) {
                                $user->points_balance += $record->points_earned;
                                $user->save();
                                
                                \App\Models\UserPoint::create([
                                    'user_id' => $user->id,
                                    'booking_id' => $record->id,
                                    'points' => $record->points_earned,
                                    'type' => 'earned',
                                    'description' => 'Points earned from booking #' . $record->id,
                                    'balance_after' => $user->points_balance,
                                ]);
                            }
                        }
                    })
                    ->successNotificationTitle('Pembayaran berhasil dikonfirmasi dan poin telah diberikan')
                    ->visible(fn (Booking $record): bool =>
                        $record->payment_status === 'waiting_confirmation'
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
                    })
                    ->successNotificationTitle('Pembayaran ditolak')
                    ->visible(fn (Booking $record): bool =>
                        $record->payment_status === 'waiting_confirmation'
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
                    ->visible(fn (Booking $record): bool => in_array($record->status, ['pending', 'confirmed'])),
                
                Action::make('confirm')
                    ->label('Konfirmasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Booking $record) => $record->update(['status' => 'confirmed']))
                    ->successNotificationTitle('Pemesanan berhasil dikonfirmasi')
                    ->visible(fn (Booking $record): bool => $record->status === 'pending'),
                
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

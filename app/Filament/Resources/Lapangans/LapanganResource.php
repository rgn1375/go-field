<?php

namespace App\Filament\Resources\Lapangans;

use App\Filament\Resources\Lapangans\Pages\CreateLapangan;
use App\Filament\Resources\Lapangans\Pages\EditLapangan;
use App\Filament\Resources\Lapangans\Pages\ListLapangans;
use App\Models\Lapangan;
use App\Models\SportType;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;

class LapanganResource extends Resource
{
    protected static ?string $model = Lapangan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $slug = 'lapangan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('title')
                    ->label('Nama Lapangan')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                    
                Select::make('sport_type_id')
                    ->label('Jenis Olahraga')
                    ->relationship('sportType', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Format: lowercase, no spaces (e.g., futsal, basketball)'),
                        TextInput::make('name')
                            ->label('Nama')
                            ->required(),
                        Textarea::make('description')
                            ->label('Deskripsi'),
                        TextInput::make('icon')
                            ->label('Icon Class')
                            ->helperText('Contoh: heroicon-o-circle-stack'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->required(),
                
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->columnSpanFull(),
                
                TextInput::make('price')
                    ->label('Harga per Sesi')
                    ->numeric()
                    ->prefix('Rp')
                    ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 2)
                    ->required(),
                
                Section::make('Dynamic Pricing')
                    ->description('Atur harga berbeda untuk weekday/weekend dan peak hours. Kosongkan untuk gunakan harga default.')
                    ->schema([
                        TextInput::make('weekday_price')
                            ->label('Harga Weekday (Senin-Jumat)')
                            ->numeric()
                            ->prefix('Rp')
                            ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 2)
                            ->helperText('Kosongkan untuk gunakan harga default'),
                        
                        TextInput::make('weekend_price')
                            ->label('Harga Weekend (Sabtu-Minggu)')
                            ->numeric()
                            ->prefix('Rp')
                            ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 2)
                            ->helperText('Kosongkan untuk gunakan harga default'),
                        
                        TimePicker::make('peak_hour_start')
                            ->label('Peak Hour Mulai')
                            ->seconds(false)
                            ->helperText('Jam mulai peak hour (misal: 17:00)'),
                        
                        TimePicker::make('peak_hour_end')
                            ->label('Peak Hour Selesai')
                            ->seconds(false)
                            ->helperText('Jam selesai peak hour (misal: 21:00)'),
                        
                        TextInput::make('peak_hour_multiplier')
                            ->label('Peak Hour Multiplier')
                            ->numeric()
                            ->step(0.1)
                            ->default(1.5)
                            ->minValue(1)
                            ->maxValue(3)
                            ->helperText('Pengali harga saat peak hour (misal: 1.5 = harga 1.5x lipat)'),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                FileUpload::make('image')
                    ->label('Gambar Lapangan')
                    ->image()
                    ->multiple()
                    ->maxFiles(3)
                    ->disk('public')
                    ->directory('lapangan-images')
                    ->visibility('public')
                    ->columnSpanFull()
                    ->helperText('Unggah hingga 3 gambar.'),
                
                Select::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                        2 => 'Under Maintenance',
                    ])
                    ->default(1)
                    ->required(),
                
                Section::make('Operational Hours')
                    ->description('Kosongkan untuk menggunakan jam operasional global')
                    ->schema([
                        TimePicker::make('jam_buka')
                            ->label('Jam Buka')
                            ->seconds(false)
                            ->helperText('Kosongkan untuk gunakan global setting'),
                        
                        TimePicker::make('jam_tutup')
                            ->label('Jam Tutup')
                            ->seconds(false)
                            ->helperText('Kosongkan untuk gunakan global setting'),
                        
                        CheckboxList::make('hari_operasional')
                            ->label('Hari Operasional')
                            ->options([
                                1 => 'Senin',
                                2 => 'Selasa',
                                3 => 'Rabu',
                                4 => 'Kamis',
                                5 => 'Jumat',
                                6 => 'Sabtu',
                                7 => 'Minggu',
                            ])
                            ->columns(4)
                            ->helperText('Kosongkan untuk beroperasi setiap hari')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Section::make('Maintenance Schedule')
                    ->description('Jadwal perawatan lapangan')
                    ->schema([
                        Toggle::make('is_maintenance')
                            ->label('Sedang Maintenance')
                            ->live()
                            ->columnSpanFull(),
                        
                        DatePicker::make('maintenance_start')
                            ->label('Tanggal Mulai')
                            ->native(false)
                            ->visible(fn ($get) => $get('is_maintenance')),
                        
                        DatePicker::make('maintenance_end')
                            ->label('Tanggal Selesai')
                            ->native(false)
                            ->visible(fn ($get) => $get('is_maintenance')),
                        
                        Textarea::make('maintenance_reason')
                            ->label('Alasan Maintenance')
                            ->rows(3)
                            ->visible(fn ($get) => $get('is_maintenance'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Gambar')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText(),
                    
                TextColumn::make('title')
                    ->label('Nama Lapangan')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-office-2')
                    ->weight('bold'),
                    
                TextColumn::make('sportType.name')
                    ->label('Jenis Olahraga')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-trophy'),
                    
                TextColumn::make('price')
                    ->label('Harga per Sesi')
                    ->money('IDR', true)
                    ->sortable()
                    ->icon('heroicon-o-currency-dollar'),
                
                TextColumn::make('weekday_price')
                    ->label('Pricing Info')
                    ->formatStateUsing(function (Lapangan $record) {
                        $info = [];
                        if ($record->weekday_price) {
                            $info[] = '📅 Weekday: Rp ' . number_format($record->weekday_price, 0, ',', '.');
                        }
                        if ($record->weekend_price) {
                            $info[] = '🌴 Weekend: Rp ' . number_format($record->weekend_price, 0, ',', '.');
                        }
                        if ($record->peak_hour_start && $record->peak_hour_end) {
                            $info[] = '⚡ Peak: ' . substr($record->peak_hour_start, 0, 5) . '-' . substr($record->peak_hour_end, 0, 5) . ' (' . $record->peak_hour_multiplier . 'x)';
                        }
                        return !empty($info) ? implode(' | ', $info) : 'Default';
                    })
                    ->badge()
                    ->color(fn (Lapangan $record) => ($record->weekday_price || $record->weekend_price || $record->peak_hour_start) ? 'warning' : 'gray')
                    ->icon('heroicon-o-banknotes')
                    ->wrap(),
                
                TextColumn::make('jam_buka')
                    ->label('Jam Operasional')
                    ->formatStateUsing(function (Lapangan $record) {
                        $hours = $record->getOperationalHours();
                        return substr($hours['jam_buka'], 0, 5) . ' - ' . substr($hours['jam_tutup'], 0, 5);
                    })
                    ->icon('heroicon-o-clock')
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('is_maintenance')
                    ->label('Maintenance')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'Ya' : 'Tidak')
                    ->color(fn (bool $state) => $state ? 'warning' : 'success')
                    ->icon(fn (bool $state) => $state ? 'heroicon-o-wrench-screwdriver' : 'heroicon-o-check-circle'),
                    
                IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'Futsal' => 'Futsal',
                        'Badminton' => 'Badminton',
                        'Tennis' => 'Tennis',
                        'Basket' => 'Basket',
                        'Volly' => 'Volly',
                    ]),
                    
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                        2 => 'Under Maintenance',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => ListLapangans::route('/'),
            'create' => CreateLapangan::route('/create'),
            'edit' => EditLapangan::route('/{record}/edit'),
        ];
    }
}

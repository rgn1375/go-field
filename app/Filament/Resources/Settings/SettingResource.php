<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages\CreateSetting;
use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Filament\Resources\Settings\Pages\ListSettings;
use Filament\Tables\Columns\TextColumn;
use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?string $modelLabel = 'Pengaturan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('key')
                    ->label('Kunci')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled(fn ($record) => $record && in_array($record->key, ['jam_buka', 'jam_tutup'])),

                TextInput::make('value')
                    ->label('Nilai')
                    ->required()
                    ->hidden(fn ($record) => $record && in_array($record->key, ['jam_buka', 'jam_tutup'])),

                TimePicker::make('value')
                    ->label('Waktu')
                    ->required()
                    ->seconds(false)
                    ->visible(fn ($record) => $record && in_array($record->key, ['jam_buka', 'jam_tutup'])),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Kunci')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'jam_buka' => 'Jam Buka',
                        'jam_tutup' => 'Jam Tutup',
                        default => $state,
                    }),

                TextColumn::make('value')
                    ->label('Nilai')
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        if (in_array($record->key, ['jam_buka', 'jam_tutup'])) {
                            return date('H:i', strtotime($state));
                        }
                        return $state;
                    }),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap()
                    ->tooltip(function (TextColumn $column): ?string{
                        $state = $column->getState();
                        if (is_string($state) && strlen($state) <= 50) {
                            return $state;
                        }

                        return null;
                    })
            ]);
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
            'index' => ListSettings::route('/'),
            'create' => CreateSetting::route('/create'),
            'edit' => EditSetting::route('/{record}/edit'),
        ];
    }
}

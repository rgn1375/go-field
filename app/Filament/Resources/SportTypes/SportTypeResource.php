<?php

namespace App\Filament\Resources\SportTypes;

use App\Filament\Resources\SportTypes\Pages\CreateSportType;
use App\Filament\Resources\SportTypes\Pages\EditSportType;
use App\Filament\Resources\SportTypes\Pages\ListSportTypes;
use App\Filament\Resources\SportTypes\Schemas\SportTypeForm;
use App\Filament\Resources\SportTypes\Tables\SportTypesTable;
use App\Models\SportType;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SportTypeResource extends Resource
{
    protected static ?string $model = SportType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;
    
    protected static ?string $navigationLabel = 'Jenis Olahraga';
    
    protected static ?string $modelLabel = 'Jenis Olahraga';
    
    protected static string|UnitEnum|null $navigationGroup = 'Master Data';
    
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SportTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SportTypesTable::configure($table);
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
            'index' => ListSportTypes::route('/'),
            'create' => CreateSportType::route('/create'),
            'edit' => EditSportType::route('/{record}/edit'),
        ];
    }
}

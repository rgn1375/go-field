<?php

namespace App\Filament\Resources\SportTypes\Pages;

use App\Filament\Resources\SportTypes\SportTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSportTypes extends ListRecords
{
    protected static string $resource = SportTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

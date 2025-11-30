<?php

namespace App\Filament\Resources\Lapangans\Pages;

use App\Filament\Resources\Lapangans\LapanganResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLapangans extends ListRecords
{
    protected static string $resource = LapanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

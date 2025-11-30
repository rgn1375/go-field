<?php

namespace App\Filament\Resources\Lapangans\Pages;

use App\Filament\Resources\Lapangans\LapanganResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLapangan extends EditRecord
{
    protected static string $resource = LapanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

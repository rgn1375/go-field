<?php

namespace App\Filament\Resources\SportTypes\Pages;

use App\Filament\Resources\SportTypes\SportTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSportType extends EditRecord
{
    protected static string $resource = SportTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

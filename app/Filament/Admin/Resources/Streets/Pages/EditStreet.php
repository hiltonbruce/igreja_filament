<?php

namespace App\Filament\Admin\Resources\Streets\Pages;

use App\Filament\Admin\Resources\Streets\StreetResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStreet extends EditRecord
{
    protected static string $resource = StreetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

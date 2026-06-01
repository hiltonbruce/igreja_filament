<?php

namespace App\Filament\Admin\Resources\Neighborhoods\Pages;

use App\Filament\Admin\Resources\Neighborhoods\NeighborhoodResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditNeighborhood extends EditRecord
{
    protected static string $resource = NeighborhoodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

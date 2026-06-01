<?php

namespace App\Filament\Admin\Resources\Neighborhoods\Pages;

use App\Filament\Admin\Resources\Neighborhoods\NeighborhoodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNeighborhoods extends ListRecords
{
    protected static string $resource = NeighborhoodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('custom.Register').' '.__('custom.Neighborhood')),
        ];
    }
}

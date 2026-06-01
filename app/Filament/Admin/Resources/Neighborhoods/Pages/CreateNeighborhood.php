<?php

namespace App\Filament\Admin\Resources\Neighborhoods\Pages;

use App\Filament\Admin\Resources\Neighborhoods\NeighborhoodResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateNeighborhood extends CreateRecord
{
    protected static string $resource = NeighborhoodResource::class;

    public function getTitle(): string
    {
        return __('custom.Register').' '.__('custom.Neighborhood');
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(__('custom.Register'));
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label(__('custom.Register').' & '.__('custom.Create').' '.__('custom.New'));
    }
}

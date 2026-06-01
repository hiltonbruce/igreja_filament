<?php

namespace App\Filament\Admin\Resources\Cities\Pages;

use App\Filament\Admin\Resources\Cities\CityResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateCity extends CreateRecord
{
    protected static string $resource = CityResource::class;

    public function getTitle(): string
    {
        return __('custom.Register').' '.__('custom.City');
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

<?php

namespace App\Filament\Admin\Resources\Streets\Pages;

use App\Filament\Admin\Resources\Streets\StreetResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateStreet extends CreateRecord
{
    protected static string $resource = StreetResource::class;

    public function getTitle(): string
    {
        return __('custom.Register').' '.__('custom.Street');
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

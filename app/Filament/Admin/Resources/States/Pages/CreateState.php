<?php

namespace App\Filament\Admin\Resources\States\Pages;

use App\Filament\Admin\Resources\States\StateResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateState extends CreateRecord
{
    protected static string $resource = StateResource::class;

    public function getTitle(): string
    {
        return __('custom.Register').' '.__('custom.State');
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

<?php

namespace App\Filament\Secretary\Resources\Members\Pages;

use App\Filament\Secretary\Resources\Members\MemberResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    public function getTitle(): string
    {
        return __('custom.Register').' '.__('custom.Member');
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

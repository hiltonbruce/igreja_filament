<?php

namespace App\Filament\Secretary\Resources\Members\Pages;

use App\Filament\Secretary\Resources\Members\MemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('custom.Register').' '.__('custom.Member'))
                ->modalWidth(Width::SixExtraLarge),
        ];
    }
}

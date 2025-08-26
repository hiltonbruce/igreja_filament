<?php

namespace App\Filament\Admin\Resources\Members\Pages;

use Filament\Actions\CreateAction;
use Filament\Support\Enums\Width;
use App\Filament\Admin\Resources\Members\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::SixExtraLarge),
        ];
    }
}

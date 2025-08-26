<?php

namespace App\Filament\Admin\Resources\Members\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Admin\Resources\Members\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

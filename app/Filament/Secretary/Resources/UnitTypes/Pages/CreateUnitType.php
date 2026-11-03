<?php

namespace App\Filament\Secretary\Resources\UnitTypes\Pages;

use App\Filament\Secretary\Resources\UnitTypes\UnitTypeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateUnitType extends CreateRecord
{
    protected static string $resource = UnitTypeResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}

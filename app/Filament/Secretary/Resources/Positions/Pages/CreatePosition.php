<?php

namespace App\Filament\Secretary\Resources\Positions\Pages;

use App\Filament\Secretary\Resources\Positions\PositionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePosition extends CreateRecord
{
    protected static string $resource = PositionResource::class;

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

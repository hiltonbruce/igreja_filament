<?php

namespace App\Filament\Secretary\Resources\Churches\Pages;

use App\Filament\Secretary\Resources\Churches\ChurchResource;
use App\Filament\Secretary\Resources\Churches\Pages\Concerns\PlacesFormActionsBelowContact;
use App\Support\ChurchHierarchy;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateChurch extends CreateRecord
{
    use PlacesFormActionsBelowContact;

    protected static string $resource = ChurchResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        try {
            return ChurchHierarchy::validate($data);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->errors(), 'data');

            throw $exception;
        }
    }
}

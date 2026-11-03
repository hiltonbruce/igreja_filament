<?php

namespace App\Filament\Secretary\Resources\Churches\Pages;

use App\Filament\Secretary\Resources\Churches\ChurchResource;
use App\Filament\Secretary\Resources\Churches\Pages\Concerns\PlacesFormActionsBelowContact;
use App\Models\Church;
use App\Support\ChurchHierarchy;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditChurch extends EditRecord
{
    use PlacesFormActionsBelowContact;

    protected static string $resource = ChurchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var Church $record */
        $record = $this->getRecord();
        $data['user_id'] = $record->user_id;

        try {
            return ChurchHierarchy::validate($data, $record);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->errors(), 'data');

            throw $exception;
        }
    }
}

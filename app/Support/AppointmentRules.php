<?php

namespace App\Support;

use App\Enums\PositionScope;
use App\Models\Appointment;
use App\Models\Position;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AppointmentRules
{
    /**
     * Normaliza os dados antes da validação: a unidade define a igreja.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function prepare(array $data): array
    {
        if (filled($data['unit_id'] ?? null)) {
            $churchId = Unit::query()->whereKey($data['unit_id'])->value('church_id');

            if ($churchId !== null) {
                $data['church_id'] = (int) $churchId;
            }
        }

        if (blank($data['ordinal'] ?? null)) {
            $data['ordinal'] = null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function validate(array $data, ?Appointment $current = null): array
    {
        $data = self::prepare($data);
        $position = filled($data['position_id'] ?? null)
            ? Position::query()->find($data['position_id'])
            : null;

        $validator = Validator::make($data, self::rules($data));

        $validator->after(function ($validator) use ($data, $position, $current): void {
            if ($position === null) {
                return;
            }

            $unitId = $data['unit_id'] ?? null;

            if ($position->scope === PositionScope::Unit && blank($unitId)) {
                $validator->errors()->add('unit_id', __('custom.This position must be assigned to a unit.'));
            }

            if ($position->scope === PositionScope::Church && filled($unitId)) {
                $validator->errors()->add('unit_id', __('custom.This position cannot be assigned to a unit.'));
            }

            self::assertUnitBelongsToChurch($validator, $data);
            self::assertOrdinal($validator, $data, $position, $current);
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return array_merge($data, $validator->validated());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, list<mixed>>
     */
    public static function rules(array $data = []): array
    {
        $position = filled($data['position_id'] ?? null)
            ? Position::query()->find($data['position_id'])
            : null;

        $requiresOrdinal = $position?->supports_ordinal === true;

        return [
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'church_id' => ['required', 'integer', 'exists:churches,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'ordinal' => array_values(array_filter([
                $requiresOrdinal ? 'required' : 'nullable',
                'integer',
                'min:1',
            ])),
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * @param  \Illuminate\Validation\Validator  $validator
     * @param  array<string, mixed>  $data
     */
    protected static function assertUnitBelongsToChurch($validator, array $data): void
    {
        $unitId = $data['unit_id'] ?? null;
        $churchId = $data['church_id'] ?? null;

        if (blank($unitId) || blank($churchId)) {
            return;
        }

        $unitChurchId = Unit::query()->whereKey($unitId)->value('church_id');

        if ($unitChurchId !== null && (int) $unitChurchId !== (int) $churchId) {
            $validator->errors()->add('unit_id', __('custom.The selected unit does not belong to the selected church.'));
        }
    }

    /**
     * @param  \Illuminate\Validation\Validator  $validator
     * @param  array<string, mixed>  $data
     */
    protected static function assertOrdinal($validator, array $data, Position $position, ?Appointment $current): void
    {
        $ordinal = $data['ordinal'] ?? null;

        if (! $position->supports_ordinal || blank($ordinal)) {
            return;
        }

        $isActive = (bool) ($data['is_active'] ?? true);

        if (! $isActive) {
            return;
        }

        $unitId = $data['unit_id'] ?? null;

        $exists = Appointment::query()
            ->where('position_id', $position->id)
            ->where('ordinal', (int) $ordinal)
            ->where('is_active', true)
            ->when(filled($unitId),
                fn (Builder $query): Builder => $query->where('unit_id', $unitId),
                fn (Builder $query): Builder => $query->whereNull('unit_id')->where('church_id', $data['church_id'] ?? null),
            )
            ->when($current, fn (Builder $query): Builder => $query->whereKeyNot($current->getKey()))
            ->exists();

        if ($exists) {
            $validator->errors()->add('ordinal', __('custom.This ordinal is already used for this position in the same unit or church.'));
        }
    }

    public static function unitChurchId(mixed $unitId): ?int
    {
        if (blank($unitId)) {
            return null;
        }

        $churchId = Unit::query()->whereKey($unitId)->value('church_id');

        return $churchId !== null ? (int) $churchId : null;
    }
}

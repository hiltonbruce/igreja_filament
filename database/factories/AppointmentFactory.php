<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Church;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'position_id' => Position::factory(),
            'church_id' => Church::factory(),
            'unit_id' => null,
            'ordinal' => null,
            'started_at' => now()->toDateString(),
            'ended_at' => null,
            'is_active' => true,
            'notes' => null,
            'user_id' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}

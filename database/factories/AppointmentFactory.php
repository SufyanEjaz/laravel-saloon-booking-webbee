<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition()
    {
        return [
            'service_id' => $this->faker->numberBetween(1, 10),
            'start_time' => $this->faker->dateTimeBetween('+1 day', '+1 week'),
            'duration' => $this->faker->randomElement([10, 60]),
            'num_clients' => $this->faker->numberBetween(1, 3),
        ];
    }
}

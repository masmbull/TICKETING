<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_number' => 'ITSUP-20000101-'.$this->faker->unique()->numerify('#####'),
            'description' => fake()->sentence(),
            'status' => 'Waiting Confirmation',
            'priority' => 'medium',
            'user_id' => User::factory(),
        ];
    }
}

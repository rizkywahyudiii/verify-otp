<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OTPCode>
 */
class OTPCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'otp_code' => str_pad($this->faker->numberBetween(0, 999999), 6, '0', STR_PAD_LEFT),
            'expires_at' => $this->faker->dateTimeBetween('now', '+10 minutes'),
        ];
    }

    /**
     * Indicate that the OTP code is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ]);
    }
}

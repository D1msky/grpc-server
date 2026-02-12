<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $packageTypes = ['STANDARD', 'EXPRESS', 'OVERNIGHT', 'FRAGILE', 'DOCUMENTS'];
        $statuses = ['PENDING', 'PICKED_UP', 'IN_TRANSIT', 'OUT_FOR_DELIVERY', 'DELIVERED', 'CANCELLED', 'FAILED'];

        return [
            'tracking_number' => Package::generateTrackingNumber(),
            'sender_name' => fake()->name(),
            'sender_address' => fake()->streetAddress() . ', ' . fake()->city() . ', ' . fake()->postcode(),
            'sender_phone' => fake()->phoneNumber(),
            'recipient_name' => fake()->name(),
            'recipient_address' => fake()->streetAddress() . ', ' . fake()->city() . ', ' . fake()->postcode(),
            'recipient_phone' => fake()->phoneNumber(),
            'weight' => fake()->randomFloat(2, 0.5, 50),
            'description' => fake()->optional(0.8)->sentence(6),
            'package_type' => fake()->randomElement($packageTypes),
            'status' => fake()->randomElement($statuses),
            'current_location' => fake()->optional(0.7)->city(),
        ];
    }

    /**
     * Package with PENDING status.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PENDING',
            'current_location' => null,
        ]);
    }

    /**
     * Package with IN_TRANSIT status.
     */
    public function inTransit(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'IN_TRANSIT',
            'current_location' => fake()->city(),
        ]);
    }

    /**
     * Package with DELIVERED status.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'DELIVERED',
            'current_location' => fake()->city(),
        ]);
    }
}

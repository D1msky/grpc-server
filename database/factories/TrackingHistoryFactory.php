<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\TrackingHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrackingHistory>
 */
class TrackingHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['PENDING', 'PICKED_UP', 'IN_TRANSIT', 'OUT_FOR_DELIVERY', 'DELIVERED', 'CANCELLED', 'FAILED'];

        return [
            'package_id' => Package::factory(),
            'location' => fake()->city(),
            'description' => fake()->sentence(8),
            'status' => fake()->randomElement($statuses),
        ];
    }
}

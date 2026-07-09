<?php

namespace Modules\Garage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Garage\Models\VehicleSize;

class VehicleSizeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = VehicleSize::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $name = $this->faker->randomElement(['Small', 'Sedan', 'SUV', 'Pickup', 'Truck']);

        return [
            'name' => $name,
            'code' => strtoupper(substr($name, 0, 3)),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'active' => true,
        ];
    }

    /**
     * Indicate that the size is inactive.
     */
    public function inactive(): Factory
    {
        return $this->state(fn (array $attributes) => ['active' => false]);
    }
}

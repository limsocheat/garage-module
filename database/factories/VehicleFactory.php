<?php

namespace Modules\Garage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Customer\Models\Customer;
use Modules\Garage\Models\Vehicle;

class VehicleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        // Link to an existing customer or create one, mirroring AddressFactory.
        $customer = Customer::inRandomOrder()->first() ?: Customer::factory()->create();

        return [
            'customer_id' => $customer->id,
            'vehicle_size_id' => null,
            'plate_number' => strtoupper($this->faker->bothify('#??-####')),
            'make' => $this->faker->randomElement(['Toyota', 'Honda', 'Lexus', 'Ford', 'Hyundai', 'Kia']),
            'model' => $this->faker->randomElement(['Camry', 'Highlander', 'CR-V', 'Ranger', 'Accent', 'RX 350']),
            'color' => $this->faker->safeColorName(),
            'year' => $this->faker->numberBetween(2005, 2025),
            'notes' => $this->faker->optional()->sentence(),
            'is_default' => false,
            'active' => true,
        ];
    }

    /**
     * A vehicle with no owner yet (plate-first / ANPR capture).
     */
    public function unowned(): Factory
    {
        return $this->state(fn (array $attributes) => ['customer_id' => null]);
    }

    /**
     * The customer's default vehicle.
     */
    public function default(): Factory
    {
        return $this->state(fn (array $attributes) => ['is_default' => true]);
    }
}

<?php

namespace Modules\Garage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Garage\Models\ServiceJob;
use Modules\Garage\Models\ServiceJobLine;

class ServiceJobLineFactory extends Factory
{
	protected $model = ServiceJobLine::class;

	public function definition(): array
	{
		return [
			'service_job_id' => ServiceJob::factory(),
			'client_line_id' => $this->faker->uuid(),
			'type' => $this->faker->randomElement(['service', 'part']),
			'product_uuid' => $this->faker->uuid(),
			'name' => $this->faker->words(2, true),
			'qty' => $this->faker->numberBetween(1, 4),
			'unit_price' => $this->faker->randomFloat(2, 1, 50),
			'membership_redemption' => false,
		];
	}

	public function service(): Factory
	{
		return $this->state(fn (array $attributes) => ['type' => 'service']);
	}

	public function part(): Factory
	{
		return $this->state(fn (array $attributes) => ['type' => 'part']);
	}
}

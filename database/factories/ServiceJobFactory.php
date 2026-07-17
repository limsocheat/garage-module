<?php

namespace Modules\Garage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Garage\Enums\ServiceJobStatusEnum;
use Modules\Garage\Models\ServiceJob;
use Modules\Garage\Models\Vehicle;

class ServiceJobFactory extends Factory
{
	protected $model = ServiceJob::class;

	public function definition(): array
	{
		return [
			'job_uuid' => $this->faker->uuid(),
			'vehicle_id' => Vehicle::factory(),
			'customer_id' => null,
			'location_id' => (string) $this->faker->numberBetween(1, 5),
			'technician' => $this->faker->name(),
			'odometer' => $this->faker->optional()->numberBetween(1000, 200000),
			'note' => $this->faker->optional()->sentence(),
			'status' => ServiceJobStatusEnum::PendingSettlement,
			'submitted_at' => now(),
		];
	}

	public function settled(): Factory
	{
		return $this->state(fn (array $attributes) => [
			'status' => ServiceJobStatusEnum::Settled,
			'settled_at' => now(),
		]);
	}
}
